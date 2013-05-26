<?php

class Insight_Util {

    /**
    * Description not available
    *
    * @param array $a1
    * @param array $a2
    * @param string $MergeTypes A concatenated string of characters indicating the merge type at each level.
    *                           "S" Means a soft merge (add/rechain overlapping array indexes for indexed arrays)
    *                           "H" Means a hard merge (overwrite same array indexes for indexed arrays)
    *                           "A" Means add element only if key does not already exist
    *                           "R" Means a replacement and will stop traversing deeper
    * The last character will be used to determine the merge type for any depper levels if existing.
    */
    public static function array_merge($a1, $a2, $MergeTypes=false, $_Level=0) {

        if(gettype($a1)!='array') return $a2;
        if(gettype($a2)!='array') return $a1;

        $r = array();
    
        if(gettype($a1)!='array') {
            return $a2;
        } else {
            if(gettype($a2)!='array') return $a1;
        }

        $merge_type = false;
        if($MergeTypes!==false) {
            $merge_type = substr($MergeTypes,$_Level,1);
            if(!$merge_type) $merge_type = substr($MergeTypes,-1,1);
        }

        if($merge_type=='S' && self::is_list($a1) && self::is_list($a2)) {
            return array_merge($a1, $a2);
        }

        foreach( $a1 as $k => $v ) {
            if(isset($a2[$k])) {
                if(gettype($v)=='array') {
                    if($merge_type!='A' || !array_key_exists($k,$a1)) {
                        if($merge_type=='R' || gettype($a2[$k])!='array') {
                            $r[$k] = $a2[$k];
                        } else {
                            $r[$k] = self::array_merge($v,$a2[$k],$MergeTypes,$_Level+1);
                        }
                    } else {
                        $r[$k] = $v;
                    }
                } else {
                    if($merge_type!='A' || !array_key_exists($k,$a1)) {
                        $r[$k] = $a2[$k];
                    } else {
                        $r[$k] = $v;
                    }
                }
                unset($a2[$k]);
            } else {
                $r[$k] = $v;
            }
        }
    
        foreach( $a2 as $k => $v ) {
            $r[$k] = $v;
        }
    
        return $r;
    }


    public static function is_list($array) {
        $i = 0;
        foreach( array_keys($array) as $k ) {
            if( $k !== $i++ ) {
                $i = -1;
                break;
            }
        }
        if($i==-1) {
            // Array is a map
            return false;
        } else {
            // Array is a list
            return true;
        }
    }

    /**
     * is_utf8 - Checks if a string complies with UTF-8 encoding
     * 
     * @see http://us2.php.net/mb_detect_encoding#85294
     */
    public static function is_utf8($str) {
        if(function_exists('mb_detect_encoding')) {
            return (
                mb_detect_encoding($str, 'UTF-8', true) == 'UTF-8' &&
                ($str === null || self::json_encode($str) !== 'null')
            );
        }
        $c=0; $b=0;
        $bits=0;
        $len=strlen($str);
        for($i=0; $i<$len; $i++){
            $c=ord($str[$i]);
            if($c > 128){
                if(($c >= 254)) return false;
                elseif($c >= 252) $bits=6;
                elseif($c >= 248) $bits=5;
                elseif($c >= 240) $bits=4;
                elseif($c >= 224) $bits=3;
                elseif($c >= 192) $bits=2;
                else return false;
                if(($i+$bits) > $len) return false;
                while($bits > 1){
                    $i++;
                    $b=ord($str[$i]);
                    if($b < 128 || $b > 191) return false;
                    $bits--;
                }
            }
        }
        return ($str === null || self::json_encode($str) !== 'null');
    }

    public static function getallheaders() {
        static $_cached_headers = false;
        if($_cached_headers!==false) {
            return $_cached_headers;
        }
        $headers = array();
        if(function_exists('getallheaders')) {
            foreach( getallheaders() as $name => $value ) {
                $headers[strtolower($name)] = $value;
            }
        } else {
            foreach($_SERVER as $name => $value) {
                if(substr($name, 0, 5) == 'HTTP_') {
                    $headers[strtolower(str_replace(' ', '-', str_replace('_', ' ', substr($name, 5))))] = $value;
                }
            }
        }
        return $_cached_headers = $headers;
    }

    public static function getRequestHeader($name) {
        static $_cached_headers = array();
        if(isset($_cached_headers[$name])) {
            return $_cached_headers[$name];
        }
        $name = strtolower($name);
        $headers = self::getallheaders();
        if(isset($headers[$name])) {
            return $_cached_headers[$name] = $headers[$name];
        }
        return $_cached_headers[$name] = false;
    }

    public static function getInstallationId() {
        $host = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];
        $parts = explode(':', $host);
        // port specified in $_SERVER['HTTP_HOST'] takes precedense
        if(count($parts)==2) {
            $host = $parts[0];
            $port = $parts[1];
        }
        $id = implode('.', array_reverse(explode('.', $host)));
        if($port && $port>0 && $port!='80') {
            $id .= ":" . $port;
        }
        return $id;
    }

    public static function getRequestIP() {
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if(isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return false;
    }

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
