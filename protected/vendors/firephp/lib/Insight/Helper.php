<?php

class Insight_Helper
{
    private static $instance = null;
    
    private static $senderLibrary = null;
    
    private $listeners = array();

    private $request = null;
    private $config = null;
    private $server = null;
    private $channel = null;
    private $dispatcher = null;
    private $announceReceiver = null;
        
    private $authorized = false;
    private $enabled = false;
    private $forceEnabled = false;

    private $apis = array();
    private $plugins = array();
    
    private static $swallowDebugMessages = false;

    public static function autoload($class)
    {
        if (strpos($class, 'Insight') !== 0 &&
            strpos($class, 'Zend') !== 0 &&
            strpos($class, 'Wildfire') !== 0
        ) {
            return;
        }

        // find relative
        if (file_exists($file = dirname(dirname(__FILE__)) . '/' . str_replace('_', '/', $class) . '.php')) {
            require_once($file);
        } else
        // find in include path
        {
            foreach (explode(PATH_SEPARATOR, get_include_path()) as $basePath) {
                if (file_exists($file = $basePath . '/' . str_replace('_', '/', $class) . '.php')) {
                    require_once($file);
                    return;
                }
            }
        }
    }

    public static function isInitialized() {
        return !!(self::$instance);
    }

    public static function setSenderLibrary($stringPointer) {
        self::$senderLibrary = $stringPointer;
    }

    public static function init($configPath, $additionalConfig, $options=array()) {
        if(self::$instance) {
            throw new Exception("Insight_Helper already initialized!");
        }

        try {

            // ensure min php version
            if(version_compare(phpversion(), '5.1') == -1) {
                throw new Exception('PHP version 5.1+ required. Your version: ' . phpversion());
            }

            // environment cleanup
            unset($GLOBALS['INSIGHT_AUTOLOAD']);
            unset($GLOBALS['INSIGHT_ADDITIONAL_CONFIG']);
            unset($GLOBALS['INSIGHT_FORCE_ENABLE']);

            $config = new Insight_Config();
            if(is_array($configPath)) {
                $config->loadFromArray($configPath, $additionalConfig);
            } else {
                $config->loadFromFile($configPath, $additionalConfig);
            }

            self::$instance = new self();
            self::$instance->setConfig($config);

            self::$instance->authorized = self::$instance->isClientAuthorized();
            self::$instance->forceEnabled = (isset($options['forceEnable']) && $options['forceEnable']===true)?true:false;

            if(self::$instance->authorized || self::$instance->forceEnabled) {

                // set a dummy channel if not authorized
                // this will prevent all data from being sent while keeping all channel logic and listeners working
                if(self::$instance->authorized!==true) {
                    self::$instance->channel = new Wildfire_Channel_Memory();
                }

                // ensure cache path works
                $cachePath = $config->getCachePath();
                if(!file_exists($cachePath)) {
                    $baseCachePath = $config->getCachePath(true);
                    if(!is_writable($baseCachePath)) {
                        throw new Exception('Error creating cache path. Insufficient permissions. Directory not writable: ' . $baseCachePath);
                    }
                    if(!mkdir($cachePath, 0775, true)) {
                        throw new Exception('Error creating cache path at: ' . $cachePath);
                    }
                }
                if(!is_dir($cachePath)) {
                    throw new Exception('Cache path not a directory: ' . $cachePath);
                }
                if(!is_writable($cachePath)) {
                    throw new Exception('Cache path not writable: ' . $cachePath);
                }

                // enable output buffering to disable flush() calls in code
                if(php_sapi_name()!='cli') {
                    ob_start();
                }

                // always enable insight for now
                self::$instance->setEnabled(true);
    
                // flush on shutdown
                register_shutdown_function('Insight_Helper__shutdown');

                // set transport
                // NOTE: If running as CLI we don't need to keep data in file
                $transport = false;
                if(php_sapi_name()!='cli') {
                    $transport = new Insight_Transport();
                    $transport->setConfig($config);
                    self::$instance->getChannel()->setTransport($transport);
                }

                // initialize server
                self::$instance->server = new Insight_Server();
                self::$instance->server->setHelper(self::$instance);
                self::$instance->server->setConfig($config);
    
                // NOTE: This may stop script execution if a transport data request is detected
                if($transport) {
                    $transport->setServer(self::$instance->server);
                    if($transport->listen()===true) {
                        self::$swallowDebugMessages = true;
                        exit;
                    }
                }

                // NOTE: This may stop script execution if a server request is detected
                if(self::$instance->server->listen()===true) {
                    self::$swallowDebugMessages = true;
                    exit;
                }
                
                // initialize request object
                self::$instance->request = new Insight_Request();
                self::$instance->request->setConfig($config);
                if($clientInfo = self::$instance->getClientInfo()) {
                    self::$instance->request->setClientKey(implode(':', $clientInfo['authkeys']));
                }
                self::$instance->request->initAppRequest($_SERVER);

                // send package info
                // TODO: Figure out a way to not send this all the time
                //       Could be done via static data structures with checksums where the client announces which
                //       data structures it has by sending the checksum in the request headers
                if($packageInfo = $config->getPackageInfo()) {
                    self::to('package')->setInfo($packageInfo);
                }
//                self::to('controller')->setServerUrl(self::$instance->server->getUrl());

                // init some plugins so their shutdown callback will be called
                self::to('request')->files();

                // setup error and assertion tracking
                self::plugin('assertion')->onAssertionError(
                    FirePHP::to('page')->console('Assertions')
                );
                self::plugin('error')->onError(
                    FirePHP::to('page')->console('Errors')
                );
                self::plugin('error')->onException(
                    FirePHP::to('page')->console('Errors')
                );

                // Look for x-insight trigger
                $insight = false;
                if(isset($_GET['x-insight'])) {
                    $insight = $_GET['x-insight'];
                }
                if(isset($_POST['x-insight'])) {
                    $insight = $_POST['x-insight'];
                }
                if($insight=='inspect' || Insight_Util::getRequestHeader('x-insight')=='inspect') {
                    Insight_Helper::to('controller')->triggerInspect();
                }
            }
        } catch(Exception $e) {

            // disable sending of data
            if(isset(self::$instance)) {
                self::$instance->setEnabled(false);
            }

            header("HTTP/1.0 500 Internal Server Error");
            header("Status: 500 Internal Server Error");
            if(isset(self::$instance->authorized) && self::$instance->authorized) {
                header('x-insight-status: ERROR');
                header('x-insight-status-msg: ' . $e->getMessage());
            }
            if(!Insight_Helper::debug('Initialization Error: ' . $e->getMessage())) {
                throw $e;
            }
        }
        return self::$instance;
    }

    public function registerListener($name, $listener) {
        if(!isset($this->listeners[$name])) {
            $this->listeners[$name] = array();
        }
        if(in_array($listener, $this->listeners[$name])) {
            return;
        }
        $this->listeners[$name][] = $listener;
    }

    public function hasListenersFor($name) {
        if(!isset($this->listeners[$name])) {
            return false;
        }
        return true;
    }

    public function getListenersFor($name) {
        if(!isset($this->listeners[$name])) {
            return false;
        }
        return $this->listeners[$name];
    }

    public function getChannel() {
        if(!$this->channel) {
            if(php_sapi_name()=='cli') {
                $this->channel = new Wildfire_Channel_HttpClient('localhost', 8099);
            } else {
                $this->channel = new Wildfire_Channel_HttpHeader();
            }
        }
        return $this->channel;
    }

    private function newInstance() {
        return new self();
    }

    public static function getInstance() {
        if(!self::$instance) {
            // initialize in disabled mode
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function setConfig($config) {
        $this->config = $config;
    }

    public function getConfig() {
        return $this->config;
    }
    
    public function getRequest() {
        return $this->request;
    }

    public function getServer() {
        return $this->server;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

    public function getEnabled() {
        return $this->enabled;
    }

    public function getAuthorized() {
        return $this->authorized;
    }

    public function getDispatcher() {
        if(!$this->getEnabled()) {
            throw new Exception("Insight is not enabled!");
        }
        if(!$this->dispatcher) {
            $this->dispatcher = new Insight_Dispatcher();
            $this->dispatcher->setHelper($this);
            $this->dispatcher->setSenderID($this->config->getPackageId() . ((self::$senderLibrary)?'?lib='.self::$senderLibrary:''));
            $this->dispatcher->setChannel($this->getChannel());
        }
        return $this->dispatcher;
    }

    private function getAnnounceReceiver() {
        if(!$this->announceReceiver) {
            $this->announceReceiver = new Insight_Receiver_Announce();
            $this->announceReceiver->setChannel($this->getChannel());
            // parse received headers
            // NOTE: This needs to be moved if we want to support multiple receivers
            $this->getChannel()->parseReceived(Insight_Util::getallheaders());
        }
        return $this->announceReceiver;
    }

    public static function to($name) {

        $instance = self::getInstance();

        if(!$instance->getEnabled()) {
            return Insight_Helper::getNullMessage();
        }

        $info = $instance->config->getTargetInfo($name);

        // if forceEnabled we collect everything
        // TODO: decide on what to collect based on config
        if($instance->forceEnabled===false) {
            if(!in_array($info['implements'], $instance->getAnnounceReceiver()->getReceivers())) {
                // if target was not announced we do not allow it and swallow the messages
                return Insight_Helper::getNullMessage();
            }
        }

        $message = new Insight_Message();
        $message->setHelper($instance);

        $message = $message->to($name);

        $api = $instance->getApi($info['api']);

        $message = $message->api($api);
        if(method_exists($api, 'getDefaultMeta')) {
            $message = $message->meta($api->getDefaultMeta());
        }

        return $message;
    }

    public function getApis() {
        return $this->apis;
    }

    public function getApi($class) {

        $class = str_replace("/", "_", $class);

        if(!isset($this->apis[$class])) {
            $api = $this->apis[$class] = new $class();
            if(method_exists($api, 'setRequest')) {
                $api->setRequest($this->request);
            }
        }
        return $this->apis[$class];
    }

    public static function plugin($name) {

        $instance = self::getInstance();

        if(!$instance->getEnabled()) {
            return Insight_Helper::getNullMessage();
        }

        if(!isset($instance->plugins[$name])) {

            $info = $instance->config->getPluginInfo($name);

            $instance->plugins[$name] = $instance->getApi($info['api']);
        }

        return $instance->plugins[$name];
    }

    protected function isClientAuthorized() {

        if(php_sapi_name()=='cli') {
            return true;
        }

        // verify IP
        $authorized = false;
        $ips = $this->config->getIPs();
        if(count($ips)==1 && $ips[0]=='*') {
            $authorized = true;
        } else {
            $requestIP = Insight_Util::getRequestIP();
            foreach( $ips as $ip ) {
                if(substr($requestIP, 0, strlen($ip))==$ip) {
                    $authorized = true;
                    break;
                }
            }
        }
        if(!$authorized) {
            Insight_Helper::debug('IP "' . Insight_Util::getRequestIP() . '" not authorized in credentials.json file or INSIGHT_IPS constant');
            return false;
        }

        $clientInfo = self::$instance->getClientInfo();
        if(!$clientInfo || $clientInfo['client']!='insight') {
            // announce installation
            // NOTE: Only an IP match is required for this. If client is announcing itself ($clientInfo) we do NOT send this header!
            // TODO: Use wildfire for this?
            header('x-insight-installation-id: ' . Insight_Util::getInstallationId());
            return false;
        }

        // verify client key
        $authorized = false;

        if($clientInfo['client']=='insight') {

            $authkeys = $this->config->getAuthkeys();

            if(count($authkeys)==1 && $authkeys[0]=='*') {
                $authorized = true;
            } else {
                foreach( $authkeys as $authkey ) {
                    if(in_array($authkey, $clientInfo['authkeys'])) {
                        $authorized = true;
                        break;
                    }
                }
            }
        }

        if(!$authorized) {
            // IP matched and client announced itself but authkey does not match
            header('x-insight-status: AUTHKEY_NOT_FOUND');
        }
        return $authorized;
    }

    public function getClientInfo() {

        if(php_sapi_name()=='cli') {
            return false;
        }

        static $_cached_info = false;
        if($_cached_info!==false) {
            return $_cached_info;
        }
        // Check if insight client is installed
        if(@preg_match_all('/^http:\/\/registry.pinf.org\/cadorn.org\/wildfire\/@meta\/protocol\/announce\/([\.\d]*)$/si',Insight_Util::getRequestHeader("x-wf-protocol-1"),$m) &&
            version_compare($m[1][0],'0.1.0','>=')) {
            return $_cached_info = array(
                "client" => "insight",
                "authkeys" => $this->getAnnounceReceiver()->getAuthkeys(),
                "receivers" => $this->getAnnounceReceiver()->getReceivers()
            );
        } else
        // Check if FirePHP is installed on client via User-Agent header
        if(@preg_match_all('/\sFirePHP\/([\.\d]*)\s?/si',$this->getUserAgent(),$m) &&
            version_compare($m[1][0],'0.0.6','>=')) {
            return $_cached_info = array("client" => "firephp");
        } else
        // Check if FirePHP is installed on client via X-FirePHP-Version header
        if(@preg_match_all('/^([\.\d]*)$/si',Insight_Util::getRequestHeader("X-FirePHP-Version"),$m) &&
            version_compare($m[1][0],'0.0.6','>=')) {
            return $_cached_info = array("client" => "firephp");
        }
        return $_cached_info = false;
    }

    protected function getUserAgent() {
        if(!isset($_SERVER['HTTP_USER_AGENT'])) return false;
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public static function debug($message, $type=null) {
        if(!defined('INSIGHT_DEBUG') || constant('INSIGHT_DEBUG')!==true || self::$swallowDebugMessages===true) {
            return false;
        }
        echo '<div style="border: 2px solid black; background-color: red;"> <span style="font-weight: bold;">[INSIGHT]</span> ' . $message . '</div>';
        return true;
    }

    public static function getNullMessage() {
        return new Insight_Helper__NullMessage();
    }
    
    public function relayPayload($payload) {
        if(!$this->getEnabled()) {
            return;
        }
        $receivers = array();
        $info = $this->config->getTargetInfo('page');
        $receivers[] = $info['implements'];
        $info = $this->config->getTargetInfo('request');
        $receivers[] = $info['implements'];
        $this->getChannel()->relayData($payload, $receivers);
    }
}

class Insight_Helper__NullMessage {
    public function __call($name, $arguments) {
        if($name=='open') {
            Insight_Message::openBlock();
        } else
        if($name=='close') {
            Insight_Message::closeBlock();
        } else
        if($name=='is') {
            if(is_bool($arguments[0])) {
                return !$arguments[0];
            }
            throw new Exception('non-boolean is() comparison not supported');
        }
        return $this;
    }
}

function Insight_Helper__shutdown() {
    $insight = Insight_Helper::getInstance();

    // only send headers if this was not a transport request
    if(class_exists('Insight_Server', false) && Insight_Util::getRequestHeader('x-insight')=="transport") {
        return;
    }

    // if disabled do not flush headers
    if(!$insight->getEnabled()) {
        return;
    }
    
    // call shutdown for all APIs
    $apis = $insight->getApis();
    if($apis) {
        foreach( $apis as $name => $obj ) {
            if (method_exists($obj, '_shutdown')) {
                $obj->_shutdown();
            }
        }
    }

    Insight_Helper::debug('Flushing headers');

    $insight->getDispatcher()->getChannel()->flush(false, true);

    if($insight->hasListenersFor('payload')) {
        $transport = $insight->getChannel()->getTransport();
        $contents = $transport->getData($transport->getLastKey());
        foreach( $insight->getListenersFor('payload') as $listener ) {
            $listener->onPayload($insight->getRequest, $contents);
        }
    }

    // if not authorized we now destroy the cached data as it is no longer needed
    // for later catching
    if($insight->getAuthorized()!==true) {
        $transport = $insight->getChannel()->getTransport();
        $file = $transport->getPath($transport->getLastKey());
        unlink($file);
    }
}


// auto-initialize based on environment if applicable
function Insight_Helper__main() {

    spl_autoload_register('Insight_Helper::autoload');

    $additionalConfig = isset($GLOBALS['INSIGHT_ADDITIONAL_CONFIG'])?$GLOBALS['INSIGHT_ADDITIONAL_CONFIG']:false;
    $insightConfigPath = getenv('INSIGHT_CONFIG_PATH');
    if(defined('INSIGHT_CONFIG_PATH')) {
        $insightConfigPath = constant('INSIGHT_CONFIG_PATH');
    }
    $options = array();
    if(isset($GLOBALS['INSIGHT_FORCE_ENABLE'])) {
        $options['forceEnable'] = ($GLOBALS['INSIGHT_FORCE_ENABLE']===true)?true:false;
    }
    if($insightConfigPath) {
        $insightConfigPath = explode(',', $insightConfigPath);
        if(sizeof($insightConfigPath)==2) {
            $additionalConfig = Insight_Util::array_merge(
                ($additionalConfig)?$additionalConfig:array(),
                array(
                    'implements' => array(
                        'cadorn.org/insight/@meta/config/0' => array(
                            'credentialsPath' => $insightConfigPath[1]
                        )
                    )
                )
            );
        }
        $insightConfigPath = $insightConfigPath[0];

        if(defined('INSIGHT_IPS')) {
            Insight_Helper::debug('INSIGHT_IPS constant ignored as INSIGHT_CONFIG_PATH is defined');
            trigger_error('INSIGHT_IPS constant ignored as INSIGHT_CONFIG_PATH is defined', E_USER_WARNING);
        }
        if(defined('INSIGHT_AUTHKEYS')) {
            Insight_Helper::debug('INSIGHT_AUTHKEYS constant ignored as INSIGHT_CONFIG_PATH is defined');
            trigger_error('INSIGHT_AUTHKEYS constant ignored as INSIGHT_CONFIG_PATH is defined', E_USER_WARNING);
        }
        if(defined('INSIGHT_PATHS')) {
            Insight_Helper::debug('INSIGHT_PATHS constant ignored as INSIGHT_CONFIG_PATH is defined');
            trigger_error('INSIGHT_PATHS constant ignored as INSIGHT_CONFIG_PATH is defined', E_USER_WARNING);
        }
        if(defined('INSIGHT_SERVER_PATH')) {
            Insight_Helper::debug('INSIGHT_SERVER_PATH constant ignored as INSIGHT_CONFIG_PATH is defined');
            trigger_error('INSIGHT_SERVER_PATH constant ignored as INSIGHT_CONFIG_PATH is defined', E_USER_WARNING);
        }
        Insight_Helper::init($insightConfigPath, $additionalConfig, $options);
    } else
    if(!$insightConfigPath && php_sapi_name()=='cli') {
        $paths = array();
        if(defined('INSIGHT_PATHS')) {
            foreach(explode(',', constant('INSIGHT_PATHS')) as $path) {
                $paths[$path] = 'allow';
            }
        }
        $config = array(
            'package.json' => array(
                'uid' => 'localhost',
                'implements' => array(
                    'cadorn.org/insight/@meta/config/0' => array(
                        'paths' => $paths
                    )
                )
            ),
            'credentials.json' => array(
                'cadorn.org/insight/@meta/config/0' => array(
                    'allow' => array(
                        'ips' => array('*'),
                        'authkeys' => array('*')
                    )
                )
            )
        );
        Insight_Helper::init($config, $additionalConfig, $options);
    } else
    if(defined('INSIGHT_IPS') || defined('INSIGHT_AUTHKEYS') || defined('INSIGHT_PATHS') || defined('INSIGHT_SERVER_PATH')) {
        if(!defined('INSIGHT_IPS') || !defined('INSIGHT_AUTHKEYS') || !defined('INSIGHT_PATHS') || !defined('INSIGHT_SERVER_PATH')) {
           Insight_Helper::debug('INSIGHT_IPS, INSIGHT_AUTHKEYS, INSIGHT_PATHS and INSIGHT_SERVER_PATH constants must be defined if not using INSIGHT_CONFIG_PATH');
           throw new Exception('INSIGHT_IPS, INSIGHT_AUTHKEYS, INSIGHT_PATHS and INSIGHT_SERVER_PATH constants must be defined if not using INSIGHT_CONFIG_PATH');
        }
        $paths = array();
        foreach(explode(',', constant('INSIGHT_PATHS')) as $path) {
            $paths[$path] = 'allow';
        }
        $config = array(
            'package.json' => array(
                'uid' => $_SERVER['HTTP_HOST'],
                'implements' => array(
                    'cadorn.org/insight/@meta/config/0' => array(
                        'server' => array(
                            'path' => constant('INSIGHT_SERVER_PATH')
                        ),
                        'paths' => $paths
                    )
                )
            ),
            'credentials.json' => array(
                'cadorn.org/insight/@meta/config/0' => array(
                    'allow' => array(
                        'ips' => explode(',', constant('INSIGHT_IPS')),
                        'authkeys' => explode(',', constant('INSIGHT_AUTHKEYS'))
                    )
                )
            )
        );
        Insight_Helper::init($config, $additionalConfig, $options);
    } else {
        Insight_Helper::debug('INSIGHT_CONFIG_PATH constant or environment variable or INSIGHT_IPS, INSIGHT_AUTHKEYS, INSIGHT_PATHS and INSIGHT_SERVER_PATH constants not set!');
        return false;
    }

}

if(isset($GLOBALS['INSIGHT_AUTOLOAD'])?$GLOBALS['INSIGHT_AUTOLOAD']:true) {
    Insight_Helper__main();
}
