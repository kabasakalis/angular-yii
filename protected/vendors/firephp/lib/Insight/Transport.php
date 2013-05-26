<?php

class Insight_Transport extends Wildfire_Transport {

    const TTL = 600;  // 10 minutes
    
    private $config;
    private $server;
    
    private $lastKey = false;
    
    
    public function setConfig($config) {
        $this->config = $config;
    }
    
    public function setServer($server) {
        $this->server = $server;
    }

    public function listen() {
        if(Insight_Util::getRequestHeader('x-insight')!='transport') {
            return false;
        }

        $payload = $_POST['payload'];
        if(get_magic_quotes_gpc()) {
            $payload = stripslashes($payload);
        }
        $payload = Insight_Util::json_decode($payload);
        $file = $this->getPath($payload['key']);
        if(file_exists($file)) {
            readfile($file);
            
            // delete old files
            // TODO: Only do this periodically

            $time = time();
            foreach (new DirectoryIterator($this->getBasePath()) as $fileInfo) {
                if($fileInfo->isDot()) continue;
                if($fileInfo->getMTime() < $time-self::TTL) {
                    unlink($fileInfo->getPathname());
                }
            }
        }
        return true;
    }

    public function getBasePath() {
        $path = $this->config->getCachePath() . DIRECTORY_SEPARATOR . '_transport';
        if(!file_exists($path)) {
            if(!mkdir($path, 0775)) {
                throw new Exception('Unable to create directory at: ' . $path);
            }
        }
        return $path;
    }

    public function getPath($key) {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . $key;
    }

    protected function getPointerData($key) {
        return array(
            "url" => $this->getUrl($key),
            "headers" => array(
                "x-insight" => "transport"
            ),
            "payload" => array(
                "key" => $key
            )
        );
    }    
    
    public function getUrl($key) {
        return $this->server->getUrl();
    }

    public function getData($key) {
        $file = $this->getPath($key);
        if(!file_exists($file)) {
            return false;
        }
        return file_get_contents($file);
    }

    public function setData($key, $value) {
        $this->lastKey = $key;
        $file = $this->getPath($key);
        file_put_contents($file, $value);
        if(!file_exists($file)) {
            throw new Exception("Unable to write data to: " . $file);
        }
    }

    public function getLastKey() {
        return $this->lastKey;
    }
}