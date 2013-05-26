<?php

function Wildfire_Protocol_Component__parse__enqueueBuffer__sort($a, $b) {
    return $a[0] - $b[0];
}

class Wildfire_Protocol_Component extends Wildfire_Protocol
{
    const CHUMK_DELIM = '__<|CHUNK|>__';

    public function parse(&$buffers, &$receivers, &$senders, &$messages, $key, $value) {

        $parts = explode('-', $key);
        // parts[0] - receiver
        // parts[1] - sender
        // parts[2] - message id/index
        
        if($parts[0]=='index') {
            // ignore the index header
            return;
        } else
        if($parts[1]=='receiver') {
            $receivers[$parts[0]] = $value;
            return;
        } else
        if($parts[2]=='sender') {
            $senders[$parts[0] + ':' + $parts[1]] = $value;
            return;
        }

        // 62|...|\
        if(!preg_match_all('/^(\d*)?\\|(.*)\\|(\\\\)?$/', $value, $m)) {
            throw new Exception('Error parsing message: ' . $value);
        }
        // length present and message matches length - complete message
        if($m[1][0] && $m[1][0]==strlen($m[2][0]) && !$m[3][0]) {
            $this->parse__enqueueMessage($messages, $parts[2], $parts[0], $parts[1], $m[2][0]);
        } else
        // message continuation present - message part
        if( $m[3][0] ) {
            $this->parse__enqueueBuffer($buffers, $messages, $parts[2], $parts[0], $parts[1], $m[2][0], ($m[1][0])?'first':'part', $m[1][0]);
        } else
        // no length and no message continuation - last message part
        if( !$m[1][0] && !$m[3][0] ) {
            $this->parse__enqueueBuffer($buffers, $messages, $parts[2], $parts[0], $parts[1], $m[2][0], 'last');
        } else {
            throw new Exception('Error parsing message: ' . $value);
        }
    }

    // this supports message parts arriving in any order as fast as possible
    public function parse__enqueueBuffer(&$buffers, &$messages, $index, $receiver, $sender, $value, $position, $length) {

        if(!$buffers[$receiver]) {
            $buffers[$receiver] = array('firsts' => 0, 'lasts' => 0, 'messages' => array());
        }
        if($position=='first') $buffers[$receiver]['firsts'] += 1;
        else if($position=='last') $buffers[$receiver]['lasts'] += 1;
        $buffers[$receiver]['messages'][] = array($index, $value, $position, $length);
        
        // if we have a mathching number of first and last parts we assume we have
        // a complete message so we try and join it
        if($buffers[$receiver]['firsts']>0 && $buffers[$receiver]['firsts']==$buffers[$receiver]['lasts']) {
            // first we sort all messages
            usort($buffers[$receiver]['messages'], 'Wildfire_Protocol_Component__parse__enqueueBuffer__sort');
            // find the first "first" part and start collecting parts
            // until "last" is found
            $startIndex = null;
            $buffer = null;
            for( $i=0 ; $i<sizeof($buffers[$receiver]['messages']) ; $i++ ) {
                if($buffers[$receiver]['messages'][$i][2]=='first') {
                    $startIndex = $i;
                    $buffer = $buffers[$receiver]['messages'][$i][1];
                } else
                if($startIndex!==null) {
                    $buffer .= $buffers[$receiver]['messages'][$i][1];
                    if($buffers[$receiver]['messages'][$i][2]=='last') {
                        // if our buffer matches the message length
                        // we have a complete message
                        if(strlen($buffer)==$buffers[$receiver]['messages'][$startIndex][3]) {
                            // message is complete
                            $this->parse__enqueueMessage($messages, $buffers[$receiver]['messages'][$startIndex][0], $receiver, $sender, $buffer);
                            array_splice($buffers[$receiver]['messages'], $startIndex, $i-$startIndex);
                            $buffers[$receiver]['firsts'] -= 1;
                            $buffers[$receiver]['lasts'] -= 1;
                            if(sizeof($buffers[$receiver]['messages'])==0) unset($buffers[$receiver]);
                            $startIndex = null;
                            $buffer = null;
                        } else {
                            // message is not complete
                        }
                    }
                }
            }
        }
    }

    public function parse__enqueueMessage(&$messages, $index, $receiver, $sender, $value) {

        if(!$messages[$receiver]) {
            $messages[$receiver] = array();
        }

        preg_match_all('/^(.*?[^\\\])?\\|(.*)$/', $value, $m);

        $message = new Wildfire_Message();
        $message->setReceiver($receiver);
        $message->setSender($sender);
        $message->setMeta(($m[1][0])?str_replace('&#124;', '|', $m[1][0]):null);
        $message->setData(str_replace('&#124;', '|', $m[2][0]));
        $message->setProtocol('http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0');

        $messages[$receiver][] = array($index, $message);
    }

    public function encodeMessage($options, $message)
    {
        if($this->uri=="http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0" ||
           $this->uri=="__TEST__") {

            $protocol_id = $message->getProtocol();
            if(!$protocol_id) {
                throw new Exception("Protocol not set for message");
            }
            $receiver_id = $message->getReceiver();
            if(!$receiver_id) {
                throw new Exception("Receiver not set for message");
            }
            $sender_id = $message->getSender();
            if(!$sender_id) {
                throw new Exception("Sender not set for message");
            }
            
            $headers = array();
            
            $meta = $message->getMeta();
            if(!$meta) {
                $meta = '';
            }

            $data = str_replace('|', '\\|', $meta) . '|' . $message->getData();

            $parts = explode(self::CHUMK_DELIM, chunk_split($data, $options['messagePartMaxLength'], self::CHUMK_DELIM));

            for ($i=0 ; $i<count($parts) ; $i++) {
    
                $part = $parts[$i];
                if ($part) {
    
                    $msg = '';
    
                    if (count($parts)>2) {
                        $msg = (($i==0)?strlen($data):'')
                               . '|' . $part . '|'
                               . (($i<count($parts)-2)?'\\':'');
                    } else {
                        $msg = strlen($part) . '|' . $part . '|';
                    }

                    $headers[] = array(
                        $protocol_id,
                        $receiver_id,
                        $sender_id,
                        $msg);
                }
            }
    
            return $headers;
        }
    }
    
    public function encodeKey($util, $receiverId, $senderId)
    {
        if($this->uri=="http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0" ||
           $this->uri=="__TEST__") {
        
            if(!isset($util["protocols"])) $util["protocols"] = array();
            if(!isset($util["messageIndexes"])) $util["messageIndexes"] = array();
            if(!isset($util["receivers"])) $util["receivers"] = array();
            if(!isset($util["senders"])) $util["senders"] = array();
    
            $protocol = self::_getProtocolIndex($util, $this->uri);
            $messageIndex = self::_getMessageIndex($util, $protocol);
            $receiver = self::_getReceiverIndex($util, $protocol, $receiverId);
            $sender = self::_getSenderIndex($util, $protocol, $receiver, $senderId);
            
            return $util["HEADER_PREFIX"] . $protocol . "-" . $receiver . "-" . $sender . "-" . $messageIndex;
        }
    }

    
    private static function _getProtocolIndex($util, $protocolId)
    {
        if(isset($util["protocols"][$protocolId])) return $util["protocols"][$protocolId];
        for( $i=1 ; ; $i++ ) {
            $value = $util["applicator"]->getMessagePart($util['HEADER_PREFIX'] . "protocol-" . $i);
            if(!$value) {
                $util["protocols"][$protocolId] = $i;
                $util["applicator"]->setMessagePart($util['HEADER_PREFIX'] . "protocol-" . $i, $protocolId);
                return $i;
            } else
            if($value==$protocolId) {
                $util["protocols"][$protocolId] = $i;
                return $i;
            }
        }
    }
    
    private static function _getMessageIndex($util, $protocolIndex)
    {
        if(isset($util["messageIndexes"][$protocolIndex])) {
            $value = $util["messageIndexes"][$protocolIndex];
        } else {
            $value = $util["applicator"]->getMessagePart($util['HEADER_PREFIX'] . $protocolIndex . "-index");
        }
        if(!$value) {
            $value = 0;
        }
        $value++;
        $util["messageIndexes"][$protocolIndex] = $value;
        $util["applicator"]->setMessagePart($util['HEADER_PREFIX'] . $protocolIndex . "-index", $value);
        return $value;
    }
    
    private static function _getReceiverIndex($util, $protocolIndex, $receiverId)
    {
        if(isset($util["receivers"][$protocolIndex . ":" . $receiverId])) return $util["receivers"][$protocolIndex . ":" . $receiverId];
        for( $i=1 ; ; $i++ ) {
            $value = $util["applicator"]->getMessagePart($util['HEADER_PREFIX'] . $protocolIndex . "-" . $i . "-receiver");
            if(!$value) {
                $util["receivers"][$protocolIndex . ":" . $receiverId] = $i;
                $util["applicator"]->setMessagePart($util['HEADER_PREFIX'] . $protocolIndex . "-" . $i . "-receiver", $receiverId);
                return $i;
            } else
            if($value==$receiverId) {
                $util["receivers"][$protocolIndex . ":" . $receiverId] = $i;
                return $i;
            }
        }
    }
    
    private static function _getSenderIndex($util, $protocolIndex, $receiverIndex, $senderId)
    {
        if(isset($util["senders"][$protocolIndex . ":" . $receiverIndex . ":" . $senderId])) return $util["senders"][$protocolIndex . ":" . $receiverIndex . ":" . $senderId];
        for( $i=1 ; ; $i++ ) {
            $value = $util["applicator"]->getMessagePart($util['HEADER_PREFIX'] . $protocolIndex . "-" . $receiverIndex . "-" . $i . "-sender");
            if(!$value) {
                $util["senders"][$protocolIndex . ":" . $receiverIndex . ":" . $senderId] = $i;
                $util["applicator"]->setMessagePart($util['HEADER_PREFIX'] . $protocolIndex . "-" . $receiverIndex . "-" . $i . "-sender", $senderId);
                return $i;
            } else
            if($value==$senderId) {
                $util["senders"][$protocolIndex . ":" . $receiverIndex . ":" . $senderId] = $i;
                return $i;
            }
        }
    }

}
