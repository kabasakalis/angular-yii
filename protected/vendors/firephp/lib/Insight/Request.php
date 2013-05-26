<?php

class Insight_Request
{
    protected $config = null;
    protected $clientKey = false;
    protected $url = false;
    protected $action = false;
    protected $arguments = array();
    
    
    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setClientKey($key) {
        $this->clientKey = $key;
    }
    
    public function isClientPresent() {
        if($this->clientKey) {
            return true;
        }
        return false;
    }

    public function getClientKey() {
        if(!$this->clientKey) {
            throw new Exception('Client key not set');
        }
        return $this->clientKey;
    }

    public function initServerRequest($payload)
    {
        $this->url = (isset($payload['url']))?$payload['url']:false;
        if($this->url) {
            // strip protocol (we assume the same code is run for same URL no matter what the protocol is)
            $urlInfo = parse_url($this->url);
            $this->url = substr($this->url, strlen($urlInfo['scheme']) + 3);
        }
        $this->action = $payload['action'];
        $this->arguments = (isset($payload['args']))?$payload['args']:array();
    }

    public function initAppRequest($server)
    {
        // strip protocol (we assume the same code is run for same URL no matter what the protocol is)
        $this->url = $server['HTTP_HOST'] . $server['REQUEST_URI'];
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function hasArgument($name)
    {
        if(!isset($this->arguments[$name])) {
            return false;
        }
        return true;
    }

    public function getArgument($name)
    {
        if(!isset($this->arguments[$name])) {
            throw new Exception('Argument not set: ' . $name);
        }
        return $this->arguments[$name];
    }

    /**
     * Cache for client key
     */
    public function getFromClientCache($name, $decode=true)
    {
        $file = $this->cachePathForName($name, 'client');
        if(!file_exists($file)) {
            return false;
        }
        if (!$decode)
            return file_get_contents($file);
        return Insight_Util::json_decode(file_get_contents($file));
    }

    /**
     * Cache for client key + url
     */
    public function getFromClientUrlCache($name, $decode=true)
    {
        $file = $this->cachePathForName($name, 'clienturl');
        if(!file_exists($file)) {
            return false;
        }
        if (!$decode)
            return file_get_contents($file);
        return Insight_Util::json_decode(file_get_contents($file));
    }
    /**
     * @deprected
     */
    public function getFromCache($name, $decode=true)
    {
        return $this->getFromClientUrlCache($name, $decode);
    }

    /**
     *Store in cache for client key
     */
    public function storeInClientCache($name, $object, $encode=true)
    {
        file_put_contents($this->cachePathForName($name, 'client'), ($encode)?Insight_Util::json_encode($object):$object);
    }

    /**
     * Store in cache for client key + url
     */
    public function storeInClientUrlCache($name, $object, $encode=true)
    {
        file_put_contents($this->cachePathForName($name, 'clienturl'), ($encode)?Insight_Util::json_encode($object):$object);
    }

    /**
     * @deprected
     */
    public function storeInCache($name, $object, $encode=true)
    {
        return $this->storeInClientUrlCache($name, $object, $encode);
    }

    public function cachePathForName($name, $type)
    {
        $url = $this->getUrl();
        if(!$url) {
            throw new Exception('URL must be set for request in order to use cache!');
        }
        // TODO: This cache path should be unique to the request ID (NOT the client key + url)
        // TODO: Refactor depending logic to use Insight_Page instead of Insight_Request
        if ($type=='clienturl') {
            $file = $this->config->getCachePath() . DIRECTORY_SEPARATOR .
                    '_request' . DIRECTORY_SEPARATOR .
                    md5('lkA022HSye2' . $this->getClientKey()) . '-' . md5($url);
        } else
        if ($type=='client') {
            $file = $this->config->getCachePath() . DIRECTORY_SEPARATOR .
                    '_client' . DIRECTORY_SEPARATOR .
                    md5('lkhs73HSye2' . $this->getClientKey());
        }
        if(!file_exists($file)) {
            if(!mkdir($file, 0775, true)) {
                throw new Exception('Error creating cache path at: ' . $file);
            }
        }
        $nameParts = explode('/', $name);
        if (strpos(array_pop($nameParts), '.') > 1) {
            return $file . DIRECTORY_SEPARATOR . $name;
        } else {
            return $file . DIRECTORY_SEPARATOR . $name . '.json';        
        }
    }
    
}
