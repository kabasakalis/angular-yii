<?php

abstract class Wildfire_Channel
{
    private static $HEADER_PREFIX = "x-wf-";
    
    private $requestId = null;
    
    private $receivers = array();
    protected $options = array();
    private $outgoingQueue = array();
    protected $_flushListeners = array();
    
    protected $transport = null;

    protected $autoflush = false;

    // protocol related
    private $_parser_protocolBuffers = array();
    // message related
    private $_parser_buffers = array();
    private $_parser_protocols = array();
    private $_parser_receivers = array();
    private $_parser_senders = array();
    private $_parser_messages = array();


    public function __construct()
    {
        $this->options['messagePartMaxLength'] = 5000;
    }

    public function enqueueOutgoing(Wildfire_Message $message, $bypassReceivers=false, $skipAutoflush=false)
    {
        // TODO: Implement passing messages to registered receivers
        
        $this->outgoingQueue[] = $this->encode($message);

        if($this->autoflush===true && $skipAutoflush===false) {
            $this->flush(false, true);
        }
        return true;
    }

    public function relayData($data, $receivers=array())
    {
        $memoryChannel = new Wildfire_Channel_Memory();
        $receiver = new Wildfire_Receiver_Relay();
        $receiver->setChannel($memoryChannel);
        foreach( $receivers as $id ) {
            $receiver->addId($id);
        }
        $receiver->setTargetChannel($this);
        $memoryChannel->parseReceived($data);
    }


    public function getOutgoing()
    {
        return $this->outgoingQueue;
    }

    public function clearOutgoing() {
        return $this->outgoingQueue = array();
    }

    public function setMessagePartMaxLength($length)
    {
        $this->options['messagePartMaxLength'] = $length;
    }

    public function addFlushListener(Wildfire_Channel_FlushListener $listener) {
        foreach( $this->_flushListeners as $obj ) {
            if($obj===$listener) {
                return;
            }
        }
        $this->_flushListeners[] = $listener;
    }

    public function flush($bypassTransport=false, $autoflushAfter=false)
    {
        foreach( $this->_flushListeners as $listener ) {
            if(method_exists($listener, 'channelFlushing')) {
                $listener->channelFlushing($this);
            }
        }

        if($this->requestId) {
            $this->setMessagePart('x-request-id', $this->requestId);
        }

        $messages = $this->getOutgoing();
        if(!$messages) {
            return 0;
        }
        
        $util = array(
            "applicator" => $this,
            "HEADER_PREFIX" => self::$HEADER_PREFIX
        );
        
        $applicator = $this;
        if($this->transport && !$bypassTransport) {
            $util['applicator'] = $this->transport;
        }
                
        // encode messages and write to headers
        foreach( $messages as $message ) {
            $headers = $message;
            foreach( $headers as $header ) {
                $util['applicator']->setMessagePart(
                    Wildfire_Protocol::factory($header[0])->encodeKey($util, $header[1], $header[2]),
                    $header[3]
                );
            }
        }

        $count = sizeof($messages);
        
        $this->clearOutgoing();
        
        if($this->transport && !$bypassTransport) {
            $this->transport->flush($this, $this->requestId);
        }

        foreach( $this->_flushListeners as $listener ) {
            if(method_exists($listener, 'channelFlushed')) {
                $listener->channelFlushed($this);
            }
        }
        
        $this->autoflush = $autoflushAfter;

        return $count;
    }

    private function encode(Wildfire_Message $message)
    {
        $protocol_id = $message->getProtocol();
        if(!$protocol_id) {
            throw new Exception("Protocol not set for message");
        }
        return Wildfire_Protocol::factory($protocol_id)->encodeMessage($this->options, $message);
    }

    public function addReceiver($receiver)
    {
        $this->receivers[] = $receiver;
    }

    public function parseReceived($rawHeaders)
    {
        if(is_string($rawHeaders)) {
            $data = explode("\n", $rawHeaders);
            $rawHeaders = array();
            foreach( $data as $header ) {
                $index = strpos($header, ":");
                if($index>5) {  // sanity check
                    $rawHeaders[substr($header, 0, $index)] = trim(substr($header, $index+1));
                }
            }
        }

        // parse the raw headers into messages
        foreach( $rawHeaders as $name => $value ) {
            $this->_parseHeader(strtolower($name), $value);
        }

        // empty any remaining buffers in case protocol header was last
        if($this->_parser_protocolBuffers) {
            foreach( $this->_parser_protocolBuffers as $id => $buffers ) {
                if($this->_parser_protocols[$id]) {
                    foreach( $buffers as $info) {
                        $this->_parser_protocols[$id]->parse($this->_parser_buffers, $this->_parser_receivers, $this->_parser_senders, $this->_parser_messages, $info[0], $info[1]);
                    }
                    unset($this->_parser_protocolBuffers[$id]);
                }
            }
        }

        // deliver the messages to the appropriate receivers
        foreach( $this->_parser_messages as $receiverKey => $receiverMessages ) {

            // sort messages by index
/*            
TODO: implement
            messages[receiverKey].sort(function(a, b) {
                if(parseInt(a[0])>parseInt(b[0])) return 1;
                if(parseInt(a[0])<parseInt(b[0])) return -1;
                return 0;
            });
*/    

            // determine receiver
            if($receiverKey=='*') {
                $receiverId = '*';
            } else {
                $receiverId = $this->_parser_receivers[$receiverKey];
            }

            // fetch receivers that support ID
            $targetReceivers = array();
            for( $i=0 ; $i<count($this->receivers) ; $i++ ) {
                if($receiverKey=='*' || $this->receivers[$i]->hasId($receiverId)) {

                    $obj = $this->receivers[$i];
                    if(method_exists($obj, "onMessageGroupStart")) {
                        $obj->onMessageGroupStart();
                    }
                    $targetReceivers[] = $this->receivers[$i];
                }
            }
            if(count($targetReceivers)>0) {
                for( $j=0 ; $j<count($receiverMessages) ; $j++ ) {

                    // re-write sender and receiver keys to IDs
                    if(isset($this->_parser_senders[$receiverMessages[$j][1]->getSender()])) {
                        $receiverMessages[$j][1]->setSender($this->_parser_senders[$receiverMessages[$j][1]->getSender()]);
                    }
                    $receiverMessages[$j][1]->setReceiver($receiverId);
                    for( $k=0 ; $k<count($targetReceivers) ; $k++ ) {
                        $targetReceivers[$k]->onMessageReceived($receiverMessages[$j][1]);
                    }
                }
                for( $k=0 ; $k<count($targetReceivers) ; $k++ ) {
                    $obj = $targetReceivers[$k];
                    if(method_exists($obj, "onMessageGroupEnd")) {
                        $obj->onMessageGroupEnd();
                    }
                }
            }
        }
    }

    private function _parseHeader($name, $value)
    {
        if (substr($name, 0, strlen(self::$HEADER_PREFIX)) == self::$HEADER_PREFIX) {
            if (substr($name, 0, strlen(self::$HEADER_PREFIX) + 9) == self::$HEADER_PREFIX . 'protocol-') {
                $id = "id:".substr($name, strlen(self::$HEADER_PREFIX) + 9);
                $this->_parser_protocols[$id] = Wildfire_Protocol::factory($value);
            } else {
                $index = strpos($name, '-', strlen(self::$HEADER_PREFIX));
                $id = "id:".substr($name, strlen(self::$HEADER_PREFIX), $index-strlen(self::$HEADER_PREFIX));
                if(isset($this->_parser_protocols[$id])) {
                    if(isset($this->_parser_protocolBuffers[$id])) {
                        foreach( $this->_parser_protocolBuffers[$id] as $info) {
                            $this->_parser_protocols[$id]->parse($this->_parser_buffers, $this->_parser_receivers, $this->_parser_senders, $this->_parser_messages, $info[0], $info[1]);
                        }
                        unset($this->_parser_protocolBuffers[$id]);
                    }
                    $this->_parser_protocols[$id]->parse($this->_parser_buffers, $this->_parser_receivers, $this->_parser_senders, $this->_parser_messages, substr($name, $index+1), $value);
                } else {
                    if(!isset($this->_parser_protocolBuffers[$id])) {
                        $this->_parser_protocolBuffers[$id] = array();
                    }
                    $this->_parser_protocolBuffers[$id][] = array(substr($name, $index+1), $value);
                }
            }
        } else
        if($name=='x-request-id') {
            $this->requestId = $value;
        }
    }
    
    public function setTransport($transport)
    {
        $this->transport = $transport;
    }

    public function getTransport()
    {
        return $this->transport;
    }
        
    abstract public function setMessagePart($key, $value);

    abstract public function getMessagePart($key);
    
}
