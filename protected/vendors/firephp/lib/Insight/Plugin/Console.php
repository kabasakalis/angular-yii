<?php

if(!defined('E_USER_DEPRECATED ')) {
    define('E_USER_DEPRECATED ', 16384);
}

class Insight_Plugin_Console extends Insight_Plugin_API {
    
    protected static $groupIndex = 0;


    public function label($label) {
        return $this->message->meta(array(
            'label' => $label
        ));
    }

    public function renderer($renderer) {
        return $this->message->meta(array(
            'renderer' => $renderer
        ));
    }

    public function filter($filter) {
        return $this->message->meta(array(
            'encoder.filter' => $filter
        ));
    }

    /**
     * @deprecated
     */
    public function dump($key, $value) {
        trigger_error('Use of Insight_Plugin_Console::dump() is DEPRECATED!', E_USER_DEPRECATED);
        $this->label('Dump')->log(array($key => $value));
    }

    public function log($data) {
        $meta = array();
        if(isset($this->message->meta['.expand'])) {
            $meta['expand'] = true;
        }
        $this->message->meta($this->_addFileLineMeta($meta, $data))->send($data);
    }

    public function info($data) {
        $meta = array(
            'priority' => 'info'
        );
        if(isset($this->message->meta['.expand'])) {
            $meta['expand'] = true;
        }
        $this->message->meta($this->_addFileLineMeta($meta, $data))->send($data);
    }

    public function warn($data) {
        $meta = array(
            'priority' => 'warn'
        );
        if(isset($this->message->meta['.expand'])) {
            $meta['expand'] = true;
        }
        $this->message->meta($this->_addFileLineMeta($meta, $data))->send($data);
    }

    public function error($data) {
        $meta = array(
            'priority' => 'error'
        );
        if(isset($this->message->meta['.expand'])) {
            $meta['expand'] = true;
        }
        $this->message->meta($this->_addFileLineMeta($meta, $data))->send($data);
    }

    public function expand() {
        return $this->message->meta(array(
            '.expand' => true
        ));
    }

    public function group($name=null, $title=null) {
        if(!preg_match_all('/^[A-Za-z0-9_\\-\\.\\/]*$/', $name, $m)) {
            throw new Exception("Invalid group name '" . $name . "'. May only contain [A-Za-z0-9_-./]");
        }
        if(!$name) {
            $name = md5(uniqid() . microtime(true) . (self::$groupIndex++));
        }
        $meta = array(
            'group' => $name
        );
        if(isset($this->message->meta['.expand'])) {
            // NOTE: The group name is used instead of a flag as it may be logged multiple times and should only
            //       apply to this group. This is kind of a hack but no way around it for now.
            $meta['group.expand'] = $name;
            unset($this->message->meta['.expand']);
        }
        if(isset($this->message->meta['group'])) {
            $meta['group.parent'] = $this->message->meta['group'];
        }
        // detach label
        $label = null;
        if(isset($this->message->meta['label'])) {
            $label = $this->message->meta['label'];
            unset($this->message->meta['label']);
        }
        $group = $this->message->api('Insight_Plugin_Group')->meta($meta);
        if($title!==null) {
            $group->logGroupTitle($title, $label);
//  NOTE: Keep this until the protocol is documented as it is another way to set the group title            
//            if(!is_string($title)) {
//                throw new Exception('Only string titles are supported for groups');
//            }
//            $meta['group.title'] = $title;
        }
        return $group;
    }

    public function trace($title, $trace=null) {
        if(is_null($trace)) {
            $trace = debug_backtrace();
        }
        if(!$trace) return false;
        $offset = $this->traceOffset;
        if(isset($this->message->meta['encoder.trace.offsetAdjustment'])) {
            $offset += $this->message->meta['encoder.trace.offsetAdjustment'];
        }
        array_splice($trace, 0, $offset-1);
        if(isset($this->message->meta['encoder.trace.maxLength'])) {
            $trace = array_splice($trace, 0, $this->message->meta['encoder.trace.maxLength']);
        }
        $meta = array(
            'file' => $trace[0]['file'],
            'line' => $trace[0]['line'],
            'renderer' => 'insight:structures/trace',
            'encoder.rootDepth' => 5
        );
        if(isset($this->message->meta['.expand'])) {
            $meta['expand'] = true;
        }
        $this->message->meta($this->_addFileLineMeta($meta))->send(array(
            'title' => $title,
            'trace' => $trace
        ));
    }

    public function table($title, $data, $header=false) {
        
        if(is_object($data)) {
            // convert object to name/value table
            $originalData = $data;
            $data = array();
            foreach( (array)$originalData as $name => $value ) {
                $data[] = array($name, $value);
            }
        } else
        if(is_array($data)) {
            if(Insight_Util::is_list($data)) {
                // we have a simple list
                // this means we have a set of rows with cells or just a list where each element should be it's own row
                $originalData = $data;
                $isSimple = false;
                foreach( $originalData as $value ) {
                    // if value is not an array we assume we have a list where the value should be it's own row
                    // i.e. we will wrap each value in an array to simulate a row with one cell
                    if(!is_array($value) || !Insight_Util::is_list($value)) {
                        $isSimple = true;
                        break;
                    }
                }
                if($isSimple) {
                    // wrap each element in an array to simulate a row with one cell
                    $data = array();
                    foreach( $originalData as $name => $value ) {
                        $data[] = array($value);
                    }
                }
            } else {
                // convert associative array to name/value table
                $originalData = $data;
                $data = array();
                foreach( $originalData as $name => $value ) {
                    $data[] = array($name, $value);
                }
            }
        }
        $meta = array(
            'renderer' => 'insight:structures/table',
            'encoder.rootDepth' => 4
        );
        if(isset($this->message->meta['.expand'])) {
            $meta['expand'] = true;
        }        
        $this->message->meta($this->_addFileLineMeta($meta))->send(array(
            'title' => $title,
            'data' => $data,
            'header' => $header
        ));
    }

    public function on($path) {
        return $this->message->api('Insight_Plugin_Selective')->on($path);
    }

    public function nolimit($nolimit=true) {
        return $this->message->meta(array(
            'encoder.depthNoLimit' => ($nolimit===true),
            'encoder.lengthNoLimit' => ($nolimit===true)
        ));
    }

    public function notrim($notrim=true) {
        // NOTE: If trimming the option is removed to have client use defaults for pure and nested strings
        //       To explicitly enable trimming of pure strings use option('trim.enabled', true)
        $message = $this->message->meta(array(
            'string.trim.enabled' => false
        ));
        if($notrim===false) {
            unset($message->meta['string.trim.enabled']);
        }
        return $message;
    }

    public function option($name, $value=null) {
        if($value===null) {
            if(isset($this->message->meta[$name])) {
                return $this->message->meta[$name];
            } else {
                return null;
            }
        } else {
            return $this->message->meta(array($name => $value));
        }
    }

    public function options($options=null) {
        if($options===null) {
            return $this->message->meta;
        } else {
            return $this->message->meta($options);
        }
    }
    
    public function show() {
        FirePHP::to('controller')->triggerInspect(array(
            'target' => $this->message->meta['target']
        ));
    }
}
