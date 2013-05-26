<?php

abstract class Wildfire_Protocol
{
    private static $protocols = array();
    protected $uri = null;

    public static function factory($uri) {
        if(isset(self::$protocols[$uri])) {
            return self::$protocols[$uri];
        }
        $class = null;
        switch($uri) {
            case 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0':
            case '__TEST__':
            	$class = 'Wildfire_Protocol_Component';
                break;
            case 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/announce/0.1.0':
                $class = 'Wildfire_Protocol_Announce';
                break;
            default:
                throw new Exception('Unknown protocol: ' . $uri);
                break;
        }
        return (self::$protocols[$uri] = new $class($uri));
    }

    public function __construct($uri) {
        $this->uri = $uri;
    }
    
    abstract public function parse(&$buffers, &$receivers, &$senders, &$messages, $key, $value);
    abstract public function encodeMessage($options, $message);
    abstract public function encodeKey($util, $receiverId, $senderId);
    
}
