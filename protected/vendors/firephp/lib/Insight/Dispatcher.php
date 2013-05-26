<?php

class Insight_Dispatcher implements Wildfire_Channel_FlushListener
{
    const PROTOCOL_ID = 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0';
    
    private $receiverID = null;
    
    private $channel = null;
    private $messageFactory = null;
    private $encoders = array();

    private $helper = null;
    
    private $onceMessages = array();


    public function setHelper($helper) {
        $this->helper = $helper;
    }

    public function setSenderID($id) {
        $this->senderID = $id;
    }

    protected function getSenderID() {
        return $this->senderID;
    }

    public function setReceiverID($id) {
        $this->receiverID = $id;
    }

    protected function getReceiverID() {
        if(!$this->receiverID) {
            throw new Exception('receiverID not set');
        }
        return $this->receiverID;
    }

    public function setChannel($channel)
    {
        if(is_string($channel)) {
            $class = 'Wildfire_Channel_' . $channel;
            $channel = new $class();
        }
        $channel->addFlushListener($this);
        return $this->channel = $channel;
    }

    public function setMessageFactory($messageFactory)
    {
        $this->messageFactory = $messageFactory;
        return true;
    }
    
    public function getChannel()
    {
        $this->channel->addFlushListener($this);
        return $this->channel;
    }

    /**
     * @interface Wildfire_Channel_FlushListener
     */
    public function channelFlushed(Wildfire_Channel $channel)
    {
    }
    public function channelFlushing(Wildfire_Channel $channel)
    {
        if($channel===$this->getChannel()) {
            if($this->onceMessages) {
                foreach( $this->onceMessages as $id => $message ) {
                    if($message[2]) {
                        $this->setReceiverID($message[2]);
                    }
                    $this->send($message[0], $message[1]);
                }
                $this->onceMessages = array();
            }
        }
    }


    private function getNewMessage($meta)
    {
        if(!$this->messageFactory) {
            return new Wildfire_Message();
        }
        return $this->messageFactory->newMessage($meta);
    }

    public function getEncoder($name = 'Default')
    {
        if(!isset($this->encoders[$name])) {
            $class = 'Insight_Encoder_' . $name;
            $this->encoders[$name] = $encoder = new $class();
            $encoder->setOptions($this->helper->getConfig()->getEncoderOptions());
        }
        return $this->encoders[$name];
    }


    public function sendOnce($id, $data, $meta=array(), $receiver=false)
    {
        $this->onceMessages[$id] = array($data, $meta, $receiver);
    }

    public function send($data, $meta=array(), $receiver=false)
    {
        if($receiver) {
            $this->setReceiverID($receiver);
        }
        list($data, $meta) = $this->getEncoder((isset($meta['encoder']))?$meta['encoder']:'Default')->encode($data, $meta);
        if($meta) {
            // remove helper options
            foreach( $meta as $name => $value ) {
                if(substr($name, 0,1 )==".") {
                    unset($meta[$name]);
                }
            }
        }
        return $this->sendRaw(
            $data,
            ($meta)?Insight_Util::json_encode($meta):''
        );
    }

    public function sendRaw($data, $meta='')
    {
        $message = $this->getNewMessage($meta);
        $message->setProtocol(self::PROTOCOL_ID);
        $message->setSender($this->getSenderID());
        $message->setReceiver($this->getReceiverID());
        if($meta) $message->setMeta($meta);
        $message->setData($data);
        $this->getChannel()->enqueueOutgoing($message);
        return true;
    }
}
