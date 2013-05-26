<?php

class Wildfire_Dispatcher
{
    
    private $channel = null;
    private $sender = null;
    private $receiver = null;
    private $protocol = null;
    
    
    public function setChannel($channel)
    {
        $this->channel = $channel;
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
        
    public function dispatch(Wildfire_Message $message)
    {
        if(!$message->getProtocol()) $message->setProtocol($this->protocol);
        if(!$message->getSender()) $message->setSender($this->sender);
        if(!$message->getReceiver()) $message->setReceiver($this->receiver);
        $this->channel->enqueueOutgoing($message);
    }
}
