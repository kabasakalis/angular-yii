<?php

class Wildfire_Channel_HttpClient extends Wildfire_Channel implements Wildfire_Channel_FlushListener
{
    private $host = dalse;
    private $port = false;
    private $headers = array();

    public function __construct($host, $port) {
        $this->host = $host;
        $this->port = $port;
        $this->addFlushListener($this);
        parent::__construct();
    }

    public function setMessagePart($key, $value)
    {
        // replace headers with same name
        $this->headers[$key] = $value;
    }
    
    public function getMessagePart($key)
    {
        if(!isset($this->headers[$key])) return false;
        return $this->headers[$key];
    }

    public function channelFlushed(Wildfire_Channel $channel) {
        if(count($this->headers)==0) {
            return;
        }
        $data = array();
        // combine all message parts into one text block
        foreach( $this->headers as $key => $value ) {
            $data[] = $key . ": " . $value;
        }
        $data = implode("\n", $data);
        try {
            if($fp = @fsockopen($this->host, $this->port)) {
                fputs($fp, "POST /wildfire-server HTTP/1.1\r\n");
                fputs($fp, "Host: " . $this->host . "\r\n");
                fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fputs($fp, "Content-length: " . strlen($data) . "\r\n");
                fputs($fp, "Connection: close\r\n\r\n");
                fputs($fp, $data);
                $result = ''; 
                while(!feof($fp)) {
                    $result .= fgets($fp, 128);
                }
                fclose($fp);
                if($result) {
                    try {
                        $result = json_decode($result, true);
                        if(result && isset($result['success'])) {
                            if($result['success']===true) {
                                // all good
                            }
                        } else {
                            // invalid response
                            // TODO: throw exception
                        }
                    } catch(Exception $e) {
                        // error parsing response
                        // TODO: throw exception
                    }
                }
            }
        } catch(Exception $e) {
            // ignore errors - socket not responsing
        }
    }
}
