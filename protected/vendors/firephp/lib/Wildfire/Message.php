<?php

class Wildfire_Message
{
    private $sender = null;
    private $receiver = null;
    private $data = null;
    private $meta = null;
    private $protocol = null;
    
    public function setData($data)
    {
        if(!is_string($data)) {
            throw new Exception('$data is not a string');
        }
        $this->data = $data;
        return true;
    }   
    
    public function getData()
    {
        return $this->data;
    }
    
    public function setMeta($meta)
    {
        if($meta!==false && $meta!==null) {
            if(!is_string($meta)) {
                throw new Exception('$meta is not a string');
            }
        }
        $this->meta = $meta;
    }   

    public function getMeta()
    {
        return $this->meta;
    }
    
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }
    
    public function getProtocol()
    {
        return $this->protocol;
    }
    
    public function setSender($sender)
    {
        $this->sender = $sender;
    }
    
    public function getSender()
    {
        return $this->sender;
    }
    
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }
    
    public function getReceiver()
    {
        return $this->receiver;
    }
    
}
