<?php

class Wildfire_Channel_HttpHeader extends Wildfire_Channel
{

    public function setMessagePart($key, $value)
    {
        // replace headers with same name
        header($key . ': ' . $value, true);
    }
    
    public function getMessagePart($key)
    {
        $key = strtolower($key);
        $headers = headers_list();

        if(!$headers) return false;
        foreach( $headers as $header ) {
            if(($pos = strpos($header, ":"))!==false) {
                if(strtolower(substr($header, 0, $pos))==$key) {
                    return trim(substr($header, $pos+1));
                }
            }
        }
        return false;
    }

}
