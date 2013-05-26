<?php

class Insight_Plugin_Page extends Insight_Plugin_API {

    public function console() {
        return $this->message->api('Insight_Plugin_Console')->meta(array(
            'context' => 'page',
            'target' => 'console'
        ));
    }

}
