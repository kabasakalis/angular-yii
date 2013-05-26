<?php

class Wildfire_Receiver_Relay extends Wildfire_Receiver
{
    private $targetChannel = null;

    public function getProtocol() {
        // TODO: return "*" so all protocols are captured?
        return 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0';
    }

    public function setTargetChannel($channel) {
        $this->targetChannel = $channel;
    }

    public function onMessageReceived(Wildfire_Message $message)
    {
        $this->targetChannel->enqueueOutgoing($message);
    }
}
