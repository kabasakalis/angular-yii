<?php

class Wildfire_Receiver
{
    
    private $channel = null;
    private $protocol = null;
    private $ids = array();
    private $sender = null;
    private $receiver = null;
    
    public function setChannel($channel)
    {
        $this->channel = $channel;
        $this->channel->addReceiver($this);
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }
    
    public function getProtocol()
    {
        return $this->protocol;
    }

    public function setId($id) {
        if(sizeof($this->ids) > 0) {
            throw new Exception('ID already set for receiver!');
        }
        $this->ids[] = $id;
    }

    public function addId($id) {
        $this->ids[] = $id;
    }

    /**
     * @deprecated
     */
    public function getId() {
        if(sizeof($this->ids) > 1) {
            throw new Exception('DEPRECATED: Multiple IDs for receiver. Cannot use getId(). Use getIds() instead!');
        }
        return $this->ids[0];
    }

    public function getIds() {
        return $this->ids;
    }

    public function hasId($id) {
        for( $i=0 ; $i<sizeof($this->ids) ; $i++ ) {
            if($this->ids[$i]==$id) {
                return true;
            }
        }
        return false;
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


    public function onMessageGroupStart() {
        // TO BE SUBCLASSED
    }
    
    public function onMessageGroupEnd() {
        // TO BE SUBCLASSED
    }
    
    public function onMessageReceived(Wildfire_Message $message)
    {
        // TO BE SUBCLASSED
    }
}
