<?php

abstract class Wildfire_Util
{
    public static function json_encode($var) {
        if(function_exists('json_encode')) {
            return json_encode($var);
        } else {
            return Zend_Json::encode($var, true, array('silenceCyclicalExceptions'=>true));
        }
    }

    public static function json_decode($str) {
        if(function_exists('json_decode')) {
            return json_decode($str, true);
        } else {
            return Zend_Json::decode($str);
        }
    }
}
