<?php

class Wildfire_Protocol_Announce extends Wildfire_Protocol
{    

    public function parse(&$buffers, &$receivers, &$senders, &$messages, $key, $value) {
    
        $parts = explode("-", $key);
        // parts[0] - message id/index

        if($parts[0]=='index') {
            // ignore the index header
            return;
        }

        // 62|...|\
        if(!preg_match_all('/^(\d*)?\|(.*)\|(\\\)?$/si' ,$value, $m)) {
            throw new Exception("Error parsing message: " . $value);
        }

        // length present and message matches length - complete message
        if($m[1][0] && $m[1][0]==strlen($m[2][0]) && !$m[3][0]) {
            $this->_parse_enqueueMessage($messages, $key, $m[2][0]);
        } else
        // message continuation present - message part
        if( $m[3][0] ) {
            $this->_parse_enqueueBuffer($buffers, $receivers, $senders, $messages, $key, $m[2][0], ($m[1][0])?'first':'part', $m[1][0]);
        } else
        // no length and no message continuation - last message part
        if( !$m[1][0] && !$m[3][0] ) {
            $this->_parse_enqueueBuffer($buffers, $receivers, $senders, $messages, $key, $m[2][0], 'last');
        } else {
            throw new Exception('Error parsing message: ' + $value);
        }
    }

    // this supports message parts arriving in any order as fast as possible
    private function _parse_enqueueBuffer(&$buffers, &$receivers, &$senders, &$messages, $index, $value, $position, $length) {
        
        $receiver = "*";
        if(!$buffers[$receiver]) {
            $buffers[$receiver] = array("firsts"=> 0, "lasts"=> 0, "messages"=> array());
        }
        if($position=="first") $buffers[$receiver]["firsts"] += 1;
        else if($position=="last") $buffers[$receiver]["lasts"] += 1;
        $buffers[$receiver]["messages"][] = array($index, $value, $position, $length);
        
        // if we have a mathching number of first and last parts we assume we have
        // a complete message so we try and join it
        if($buffers[$receiver]["firsts"]>0 && $buffers[$receiver]["firsts"]==$buffers[$receiver]["lasts"]) {
            // first we sort all messages
/*            
TODO: implement
            $buffers[$receiver]["messages"].sort(
                function (a, b) {
                    return a[0] - b[0];
                }
            );
*/
            // find the first "first" part and start collecting parts
            // until "last" is found
            $startIndex = null;
            $buffer = null;
            for( $i=0 ; $i<count($buffers[$receiver]["messages"]) ; $i++ ) {
                if($buffers[$receiver]["messages"][$i][2]=="first") {
                    $startIndex = $i;
                    $buffer = $buffers[$receiver]["messages"][$i][1];
                } else
                if($startIndex!==null) {
                    $buffer += $buffers[$receiver]["messages"][$i][1];
                    if($buffers[$receiver]["messages"][$i][2]=="last") {
                        // if our buffer matches the message length
                        // we have a complete message
                        if(count($buffer)==$buffers[$receiver]["messages"][$startIndex][3]) {
                            // message is complete
                            $this->_parse_enqueueMessage($messages, $buffers[$receiver]["messages"][$startIndex][0], $buffer);
                            array_splice($buffers[$receiver]["messages"], $startIndex, $i-$startIndex);
                            $buffers[$receiver]["firsts"] -= 1;
                            $buffers[$receiver]["lasts"] -= 1;
                            if(count($buffers[$receiver]["messages"])==0) $buffers[$receiver] = null;
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

    private function _parse_enqueueMessage(&$messages, $index, $value) {
            
        $receiver = "*";
        
        if(!isset($messages[$receiver])) {
            $messages[$receiver] = array();
        }
        
        $message = new Wildfire_Message();
        $message->setReceiver($receiver);
        
        preg_match_all('/^(.*?[^\\\])?\|(.*)$/si', $value, $m);
        
        $message->setMeta($m[1][0] || null);
        $message->setData($m[2][0]);
        
        $messages[$receiver][] = array($index, $message);
    }


    public function encodeMessage($options, $message) {
        // TODO: implement
    }

    public function encodeKey($util, $receiverId, $senderId) {
        // TODO: implement
    }
    
}
