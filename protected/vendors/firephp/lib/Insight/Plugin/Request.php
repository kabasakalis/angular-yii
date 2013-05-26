<?php

class Insight_Plugin_Request extends Insight_Plugin_API {

    public function console($name = 'Console') {
        return $this->message->api('Insight_Plugin_Console')->meta(array(
            'context' => 'request',
            'target' => 'console/' . $name
        ));
    }

    public function files() {
        return $this->message->api('Insight_Plugin_Files')->meta(array(
            'context' => 'request'
        ));
    }



/*
    public function timeline($name) {
        return $this->message->api(new Insight_Plugin_Timeline())->meta(array(
            'target' => 'timeline/' . $name
        ));
    }
*/
}