<?php

class Insight_Plugin_Plugin extends Insight_Plugin_API {

    private $plugins = array();

    public function removeAll() {
        $this->message->meta(array(
            'encoder' => 'JSON',
            'action' => 'removeAll'
        ))->send(true);
    }

    public function plugin($alias, $class=false) {
        if(!preg_match_all('/^[A-Za-z0-9_\\-\\.\\/]*$/', $alias, $m)) {
            throw new Exception("Invalid plugin alias '" . $alias . "'. May only contain [A-Za-z0-9_-./]");
        }        
        $meta = array(
            'alias' => $alias
        );
        if($class!==false) {
            $meta['.class'] = $class;
        } else
        if(isset($this->plugins[$alias])) {
            $meta['.class'] = get_class($this->plugins[$alias]);
        }
        return $this->message->meta($meta);
    }

    public function getInstance() {
        if(!isset($this->message->meta['alias'])) {
            throw new Exception('Plugin alias not set! Use ->plugin("<alias>")-> first');
        }
        if(!isset($this->message->meta['.class'])) {
            throw new Exception('Plugin class not set! Use ->plugin("<alias>", "<class>")-> or ->register() first');
        }
        $program = $this->loadProgram($this->message->meta['.class'], (isset($this->message->meta['.file']))?$this->message->meta['.file']:false);
        $program->setAlias($this->message->meta['alias']);
        return $this->plugins[$this->message->meta['alias']] = $program;
    }

    public function register($options) {
        static $registeredPrograms = array();

        if(!isset($this->message->meta['alias'])) {
            throw new Exception('Plugin alias not set! Use ->plugin("<alias>")-> first');
        }

        $class = false;
        if(isset($options['class'])) {
            if(isset($this->message->meta['.class']) && $this->message->meta['.class']!=$options['class']) {
                throw new Exception('Cannot register plugin with different class "' . $options['class'] . '" when plugin class "' . $this->message->meta['.class'] . '" already set via ->plugin()->');
            }
            $this->message->meta['.class'] = $class = $options['class'];
        } else
        if(isset($this->message->meta['.class'])) {
            $class = $this->message->meta['.class'];
        } else {
            throw new Exception('No "class" provided when registering plugin');
        }

        if(isset($options['file'])) {
            $this->message->meta['.file'] = $options['file'];
        }

        $program = $this->loadProgram($class, (isset($options['file']))?$options['file']:false);

        if(isset($registeredPrograms[$program->getId()])) {
            // only send program registration message once
            throw new Exception('Plugin "' . $class . '" has already been registered during this request! A plugin can only be registered once.');
        }
        $registeredPrograms[$program->getId()] = true;

        $program->setAlias($this->message->meta['alias']);

        if(isset($options['forceReload']) && $options['forceReload']) {
            $program->setForceReload(true);
        }

        $this->plugins[$this->message->meta['alias']] = $program;

        foreach( $program->getInsightRegistrationMessages() as $message ) {
            $this->message->meta(array(
                'encoder' => 'JSON',
                'action' => 'register'
            ))->send($message);
        }
    }

    public function sendSimpleMessage($message) {
        $this->message->meta(array(
            'encoder' => 'JSON',
            'action' => 'simpleMessage'
        ))->send($message);
    }

    public function show() {
        $this->message->meta(array(
            'encoder' => 'JSON',
            'action' => 'show'
        ))->send(true);
    }

    public function respond($server, $request) {

        if($request->getAction()=='GetFile') {
            $args = $request->getArguments();
            $program = $this->loadProgram($args['controllerClass'], $args['controllerFile']);
            $plugin = $program->getPlugin($args['container']);
            $file = $plugin->getResourcePath($args['path']);
            if(!$file) {
                return array(
                    'type' => 'error',
                    'status' => '404'
                );
            } else {
                $extensions = array(
                    'js' => 'application/javascript',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'css' => 'text/css'
                );
                $parts = explode('.', $file);
                $ext = array_pop($parts);
                if(!isset($extensions[$ext])) {
                    return array(
                        'type' => 'error',
                        'status' => '403'
                    );
                }
                return array(
                    'type' => $extensions[$ext],
                    'data' => base64_encode(file_get_contents($file))
                );
            }
        } else
        if($request->getAction()=='GetProgram') {

            $args = $request->getArguments();

            $program = $this->loadProgram($args['controllerClass'], $args['controllerFile']);

            $program->setAlias($args['alias']);

            if(isset($args['forceReload']) && $args['forceReload']) {
                $program->setForceReload(true);
            }

            return array(
                'type' => 'application/javascript',
                'data' => $program->getWrappedProgram($args['container'])
            );
        } else
        if($request->getAction()=='MessageProgram') {

            $args = $request->getArguments();

            $program = $this->loadProgram($args['controllerClass'], $args['controllerFile']);

            $program->setAlias($args['alias']);

            $message = new Insight_Plugin_Plugin_Message($args['message']);
            $response = $program->onMessage($message);

            if($message->getType()=='simple-response') {
                return array(
                    'type' => 'text/plain',
                    'data' => json_encode($response)
                );
            } else {
                return array(
                    'type' => 'text/plain',
                    'data' => 'OK'
                );
            }
        }
        return array(
            'type' => 'error',
            'status' => '403'
        );
    }

    protected function loadProgram($class, $file=null) {

        if(!class_exists($class, false)) {
            if(!$file) {
                // assuming $class is accessible via autoloader
                new $class();
            } else {
                if(!is_file($file) || !is_readable($file)) {
                    throw new Exception('File not accessible: ' . $file);
                }
                if(!require_once($file)) {
                    throw new Exception('Error while requiring file: ' . $file);
                }
                if(!class_exists($class, false)) {
                    throw new Exception('Class "' . $class . '" not declared in file: ' . $file);
                }
            }
        }

        $reflectionClass = new ReflectionClass($class);

        // Ensure class is declared in a directory we have access to
        if(!Insight_Helper::getInstance()->getServer()->canServeFile($reflectionClass->getFileName())) {
            throw new Exception('Class "' . $class . '" in file "' . $reflectionClass->getFileName() . ' cannot be access remotely. You need to configure acces with ["implements"]["cadorn.org/insight/@meta/config/0"]["paths"].');
        }

        $program = new $class();

        if(!is_a($program, 'Insight_Program_JavaScript')) {
            throw new Exception('Class "' . $class . '" in file "' . $reflectionClass->getFileName() . '" does not inherit from Insight_Program_JavaScript');
        }
        
        return $program;
    }
}
