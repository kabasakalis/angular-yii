<?php

class Insight_Plugin_Process extends Insight_Plugin_API {

    public function console($name = 'Console') {
        return $this->message->api('Insight_Plugin_Console')->meta(array(
            'context' => 'process',
            'target' => 'console/' . $name
        ));
    }

}
