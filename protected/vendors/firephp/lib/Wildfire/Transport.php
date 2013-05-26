<?php

abstract class Wildfire_Transport
{
    const RECEIVER_ID = "http://registry.pinf.org/cadorn.org/wildfire/@meta/receiver/transport/0";
    
    protected $buffer = array();
    
    private $pointerMessages = array();


    public function getMessagePart($key)
    {
        if(!isset($this->buffer[$key])) {
            return null;
        }
        return $this->buffer[$key];
    }

    public function setMessagePart($key, $value)
    {
        $this->buffer[$key] = $value;
    }
    
    public function flush($channel, $requestId)
    {
        $data = array();
//        $seed = array();
    
        // combine all message parts into one text block
        foreach( $this->buffer as $key => $value ) {
            $data[] = $key . ": " . $value;
//            if(count($data) % 3 == 0 && count($seed) < 5) $seed[] = $value;
        }

        // generate a key for the text block
        $key = md5($requestId); //md5(uniqid() . ":" . implode("", $seed));
    
        // store the text block for future access
        // TODO: Do not flush all data all the time (during autoflush). Some data may already be written and does not need to be written
        //       to file over and over. Need an appendData() method?
        $this->setData($key, implode("\n", $data));
        
        $this->sendPointerMessage($channel, $key);
        
        return $channel->flush(true);
    }

    private function sendPointerMessage($channel, $key) {
        // pointer message should only be sent once
        if(isset($this->pointerMessages[$key])) {
            return;
        }

        // create a pointer message to be sent instead of the original messages
        $message = new Wildfire_Message();

        $message->setProtocol('http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0');
        $message->setSender('http://registry.pinf.org/cadorn.org/wildfire/packages/lib-php/lib/Wildfire/Transport.php');
        $message->setReceiver(self::RECEIVER_ID);
        $message->setData(Wildfire_Util::json_encode($this->getPointerData($key)));

        // send the pointer message through the channel bypassing all transports and local receivers
        $channel->enqueueOutgoing($message, false, true);
        
        $this->pointerMessages[$key] = $message;
    }

    protected function getPointerData($key) {
        return array("url" => $this->getUrl($key));
    }    

    abstract public function getUrl($key);
    abstract public function getData($key);
    abstract public function setData($key, $value);
}
