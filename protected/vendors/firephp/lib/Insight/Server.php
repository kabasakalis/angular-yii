<?php

require_once('Insight/Util.php');
require_once('Insight/Plugin/Tester.php');
require_once('Insight/Plugin/FileViewer.php');
require_once('Insight/Request.php');

class Insight_Server
{
    private $helper = null;
    private $config = null;
    private $plugins = array();

    function __construct() {
        // TODO: Load plugins dynamically based on server request
//        $this->registerPlugin(new Insight_Plugin_Tester());
//        $this->registerPlugin(new Insight_Plugin_FileViewer());
    }

    public function setHelper($helper) {
        $this->helper = $helper;
    }

    public function setConfig($config) {
        $this->config = $config;
    }

    public function getConfig() {
        return $this->config;
    }


/*    
    public function registerPlugin($plugin) {
        $this->plugins[strtolower(get_class($plugin))] = $plugin;
    }
*/

    public function getUrl() {
        $info = $this->config->getServerInfo();
        $url = array();
        if(php_sapi_name()=='cli') {
            $url[] = 'file://';
        } else {
            $host = $_SERVER['HTTP_HOST'];
            if(isset($info['host'])) {
                $host = $info['host'];
            }
            $port = $_SERVER['SERVER_PORT'];
            $parts = explode(':', $host);
            if(count($parts)==2) {
                $host = $parts[0];
                $port = $parts[1];
            }
            $secure = false;
            if($port==443) {
                $secure = true;
                $port = false;
            }
            if(isset($info['port'])) {
                $port = $info['port'];
            }
            if($info['secure']===true || $secure) {
                $url[] = 'https';
            } else {
                $url[] = 'http';
            }
            $url[] = '://';
            $url[] = $host;
            if($port && $port>0 && $port!=80) {
                $url[] = ':' . $port;
            }
    
            $path = $info['path'];
            if(substr($path, 0, 2)=="./") {
                $pathInfo = parse_url("http://domain.com" . $_SERVER['REQUEST_URI']);
                $pathParts = explode("/", $pathInfo['path']);
                // trim filename if applicable
                if(substr($_SERVER['REQUEST_URI'], -1,1)!='/') {
                    array_pop($pathParts);
                }
                $path = implode("/", $pathParts) . '/' . substr($path, 2);
            }

            $url[] = preg_replace('/\/{2}/', '/', $path);
        }
        return implode('', $url);
    }
    
    public function getPath() {
        $urlInfo = parse_url($this->getUrl());
        return $urlInfo['path'];
    }

    public function listen() {

        if(Insight_Util::getRequestHeader('x-insight')=='serve' ||
          (isset($_GET['x-insight']) && $_GET['x-insight']=='serve')) {
            // we can respond
        } else {
            return false;
        }

//        try {
            $response = false;
            if(isset($_POST['payload'])) {
                $payload = $_POST['payload'];
                if(get_magic_quotes_gpc()) {
                    $payload = stripslashes($payload);
                }
                try {
                    $response = $this->respond(Insight_Util::json_decode($payload));
                } catch(Exception $e) {
                    $response = array(
                        'type' => 'error',
                        'status' => 500
                    );
                }
            } else
            if(sizeof($_GET)>0) {
                // TODO: Implement fetching via GET
            }

            if(!$response) {
                header("HTTP/1.0 204 No Content");
                header("Status: 204 No Content");
            } else
            if (is_string($response)) {
                echo $response;
            } else {
                switch($response['type']) {
                    case 'error':
                        header("HTTP/1.0 " . $response['status']);
                        header("Status: " . $response['status']);
                    	break;
                    case 'json':
                        header("Content-Type: application/json");
                        echo Insight_Util::json_encode($response['data']);
                        break;
                    default:
                        echo $response['data'];
                        break;
                }
            }
/*
        } catch(Exception $e) {
            throw $e;
            header("HTTP/1.0 500 Internal Server Error");
            header("Status: 500 Internal Server Error");

            echo($e->getMessage());

            // TODO: Log error to insight client
        }
*/
        return true;
    }

    protected function respond($payload) {
        if(!$payload['target']) {
            throw new Exception('$payload.target not set');
        }
        if(!$payload['action']) {
            throw new Exception('$payload.action not set');
        }

        $target = $payload['target'];
        if(!isset($this->plugins[$target])) {
            $file = str_replace('_', '/', $target) . '.php';
            require_once($file);
            if(!class_exists($target, false)) {
                throw new Exception('Class ' . $target . ' not defined in: ' . $file);
            }
            $this->plugins[$target] = new $target();
        }

        $plugin = $this->plugins[$target];
        
        $request = new Insight_Request();
        $request->setConfig($this->config);
        $clientInfo = $this->helper->getClientInfo();
        $request->setClientKey(implode(':', $clientInfo['authkeys']));
        $request->initServerRequest($payload);

        $plugin->setRequest($request);

        return $plugin->respond($this, $request);
    }

    public function canServeFile($file) {
        $file = realpath($file);
        if(!file_exists($file)) {
            return false;
        }
        $paths = $this->config->getPaths();
        // find longest path match and look at instruction
        foreach( $paths as $path => $instruction ) {
            if(substr($file, 0, strlen($path))==$path) {
                if($instruction=="deny") {
                    return false;
                } else
                if($instruction=="allow") {
                    return $file;
                }
            }
        }
        return false;
    }
}
