<?php

class Insight_Message {
    
    /**
     * @insight filter = on
     */
    protected $helper = null;
    
    protected $to = null;
    public $meta = array();    
    protected $api = null;
    protected $once = false;
    protected $apiOnce = null;

    // if blocks are present null messages are returned
    protected static $blocks = 0;


    public function setHelper($helper) {
        $this->helper = $helper;
    }
    
    
    public static function openBlock() {
        self::$blocks++;
    }

    public static function closeBlock() {
        self::$blocks--;
        if(self::$blocks<0) {
            throw new Exception('Incorrect open() close() nesting!');
        }
    }  


    public function __call($name, $arguments) {

        if(self::$blocks>0) {
            return Insight_Helper::getNullMessage();
        }

        if($this->apiOnce) {
            if(!method_exists($this->apiOnce, $name)) {
                throw new Exception('Method "' . $name . '" does not exist in class: ' . get_class($this->apiOnce));
            }
            $api = $this->apiOnce;
            $this->apiOnce = false;
            $oldmsg = $api->setMessage($this);
            $retval = call_user_func_array(array($api, $name), $arguments);
            $api->setMessage($oldmsg);
            return $retval;
        } else
        if($this->api && method_exists($this->api, $name)) {
            $oldmsg = $this->api->setMessage($this);
            $retval = call_user_func_array(array($this->api, $name), $arguments);
            $this->api->setMessage($oldmsg);
            return $retval;
        }
        if($name=='once') {
            $message = clone $this;
            $message->once = $arguments[0];
            return $message;
        } else
        if($name=='to') {
            $message = clone $this;
            $message->to = $arguments[0];
            return $message;
        } else
        if($name=='is') {
            if(is_bool($arguments[0])) {
                return $arguments[0];
            }
            throw new Exception('non-boolean is() comparison not supported');
        } else
        if($name=='api') {
            $message = clone $this;
            $api = $arguments[0];
            if(is_string($api)) {
                $api = $this->helper->getApi($api);
            }
            if(isset($arguments[1]) && $arguments[1]===true) {
                $message->apiOnce = $api;
            } else {
                $message->api = $api;
            }
            if(method_exists($api, 'setRequest')) {
                $api->setRequest($this->helper->getRequest());
            }
            return $message;
        } else
        if($name=='meta') {
            $message = clone $this;
            foreach( $arguments[0] as $name => $value ) {
                if($value===null) {
                    unset($message->meta[$name]);
                } else
                if(isset($message->meta[$name])) {
                    $message->meta[$name] = Insight_Util::array_merge($message->meta[$name], $value);
                } else {
                    $message->meta[$name] = $value;
                }
            }
            return $message;
        } else
        if($name=='open') {
            return $this;
        } else
        if($name=='close') {
            return $this;
        }
        throw new Exception("Unknown method: " . $name);
    }

    public function send($data) {

        $dispatcher = $this->helper->getDispatcher();

        $meta = $this->meta;

        if(isset($meta['renderer'])) {
            $parts = explode(":", $meta['renderer']);
            $info = $this->helper->getConfig()->getRendererInfo($parts[0]);
            $parts[0] = $info['uid'];
            $meta['renderer'] = implode(":", $parts);
        }

        $info = $this->helper->getConfig()->getTargetInfo($this->to);
        if($this->once) {
            $dispatcher->sendOnce($this->once, $data, $meta, $info['implements']);
        } else {
            $dispatcher->send($data, $meta, $info['implements']);
        }
    }
}