<?php

class Insight_Plugin_Plugin_Message {
    
    private $type = false;
    private $data = false;
    
    public function __construct($message) {
        $this->type = $message['type'];
        $this->data = $message['data'];
    }
    
    public function getType() {
        return $this->type;
    }

    public function getData() {
        return $this->data;
    }

}
