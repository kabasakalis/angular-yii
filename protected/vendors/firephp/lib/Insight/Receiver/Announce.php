<?php

class Insight_Receiver_Announce extends Wildfire_Receiver
{
    private $data = array();

    public function getProtocol() {
        return 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/announce/0.1.0';
    }

    public function onMessageReceived(Wildfire_Message $message)
    {
        $data = Insight_Util::json_decode($message->getData());
        
        if(isset($data['authkey'])) {
            if(!isset($this->data['authkeys'])) {
                $this->data['authkeys'] = array();
            }
            $this->data['authkeys'][] = $data['authkey'];
        }
        
        if(isset($data['receivers'])) {
            if(!isset($this->data['receivers'])) {
                $this->data['receivers'] = array();
            }
            $this->data['receivers'] = array_merge($this->data['receivers'], $data['receivers']);
            array_unique($this->data['receivers']);
        }
    }

    public function getAuthkeys() {
        if(!$this->data || !$this->data["authkeys"]) return false;
        return $this->data["authkeys"];
    }
    
    public function getReceivers() {
        if(!$this->data || !$this->data["receivers"]) return false;
        return $this->data["receivers"];
    }
}
