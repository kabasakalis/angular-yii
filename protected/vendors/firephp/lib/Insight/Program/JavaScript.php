<?php

abstract class Insight_Program_JavaScript {

    protected $alias = null;
    protected $forceReload = false;

    public function setAlias($alias) {
        $this->alias = $alias;
    }

    public function getAlias() {
        return $this->alias;
    }

    public function setForceReload($forceReload) {
        $this->forceReload = $forceReload;
    }

    public function getForceReload() {
        return $this->forceReload;
    }

    public function getId() {
        return Insight_Util::getInstallationId() . '/' . get_class($this);
    }

    public function getInsightRegistrationMessages() {
        $reflectionClass = new ReflectionClass(get_class($this));
        $plugins = $this->getPluginPaths();
        foreach( $plugins as $containerName => $path ) {
            // NOTE: Ignoring $containerName for now - plugin will get name from basename($path)
            $plugin = new Insight_Program_JavaScript_Plugin($this, $path);
            $info = $plugin->getInsightRegistrationMessage();
            // augment plugin info with info about controlling class
            $info['controllerClass'] = get_class($this);
            $info['controllerFile'] = $reflectionClass->getFileName();
            $info['forceReload'] = $this->getForceReload();
            $info['alias'] = $this->getAlias();
            $info['options'] = $this->getOptions();
            $plugins[$containerName] = $info;
        }
        return array_values($plugins);
    }

    public function getPluginPaths() {
        $reflectionClass = new ReflectionClass(get_class($this));
        $file = dirname($reflectionClass->getFileName()) . DIRECTORY_SEPARATOR . 'packages';
        if(!is_dir($file)) {
            $file = dirname(dirname($reflectionClass->getFileName())) . DIRECTORY_SEPARATOR . 'packages';
        }
        if(!is_dir($file)) {
            throw new Exception("No plugins found in 'packages/' relative to: " . $reflectionClass->getFileName());
        }
        $plugins = array();
        foreach( new DirectoryIterator($file) as $dir ) {
            if(!$dir->isDot() && $dir->isDir())
            $plugins[$dir->getBasename()] = $dir->getPathname();
        }
        return $plugins;
    }

    public function getPlugin($containerName) {
        $plugins = $this->getPluginPaths();
        if(!isset($plugins[$containerName])) {
            throw new Exception('Plugin for $containerName "' . $containerName . '" not found.');
        }
        return new Insight_Program_JavaScript_Plugin($this, $plugins[$containerName]);
    }

    public function getWrappedProgram($containerName) {
        $plugins = $this->getPluginPaths();
        if(!isset($plugins[$containerName])) {
            throw new Exception('$containerName "' . $containerName . '" not found.');
        }
        $plugin = new Insight_Program_JavaScript_Plugin($this, $plugins[$containerName]);
        return $plugin->getWrappedProgram();
    }

    public function sendSimpleMessage($message) {
        FirePHP::to('plugin')->plugin($this->alias)->sendSimpleMessage($message);
    }

    public function onMessage($message) {
    }

    public function getOptions() {
        return array();
    }
}
