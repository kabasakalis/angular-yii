<?php

class Insight_Plugin_Group extends Insight_Plugin_Console {
    
    protected static $loggedTitles = array();

    public function logGroupTitle($title, $label=null) {
        if(!isset(self::$loggedTitles[$this->message->meta['group']])) {
            self::$loggedTitles[$this->message->meta['group']] = true;
        } else {
            // title is already logged
            return;
        }
        if($label!==null) {
            $this->label($label)->log($title);
        } else {
            $this->log($title);
        }
    }

    public function open() {
        $this->message->meta($this->_addFileLineMeta(array(
            'group.start' => true
        )))->send(true);
        return $this->message;
    }

    public function close() {
        $this->message->meta($this->_addFileLineMeta(array(
            'group.end' => true
        )))->send(true);
        return $this->message;
    }
}
