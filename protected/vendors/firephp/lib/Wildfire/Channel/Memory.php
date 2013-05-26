<?php

class Wildfire_Channel_Memory extends Wildfire_Channel
{
    private $headers = array();

    public function setMessagePart($key, $value)
    {
        // replace headers with same name
        $this->headers[$key] = $value;
    }
    
    public function getMessagePart($key)
    {
        if(!isset($this->headers[$key])) return false;
        return $this->headers[$key];
    }

}
