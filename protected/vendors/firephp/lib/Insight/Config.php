<?php

class Insight_Config
{
    const PACKAGE_META_URI = 'http://registry.pinf.org/cadorn.org/insight/@meta/package/0';
    const CONFIG_META_URI = 'http://registry.pinf.org/cadorn.org/insight/@meta/config/0';
    
    protected $defaultConfig;
    
    protected $file = null;

    /**
     * @insight filter = on
     */
    protected $config = null;


    function __construct() {
        $this->defaultConfig = array(
            "implements" => array(
                self::CONFIG_META_URI => array(
                    "server" => array(
                        "secure" => false,
                        "path" => "/"
                    ),
                    "paths" => array(
                        "./" => "allow",
                        "./credentials.json" => "deny"
                    ),
                    "targets" => array(
                        "controller" => array(
                            "implements" => "http://registry.pinf.org/cadorn.org/insight/@meta/receiver/insight/controller/0",
                            "api" => "Insight/Plugin/Controller"
                        ),
                        "plugin" => array(
                            "implements" => "http://registry.pinf.org/cadorn.org/insight/@meta/receiver/insight/plugin/0",
                            "api" => "Insight/Plugin/Plugin"
                        ),
                        "package" => array(
                            "implements" => "http://registry.pinf.org/cadorn.org/insight/@meta/receiver/insight/package/0",
                            "api" => "Insight/Plugin/Package"
                        ),
                        "selective" => array(
                            "implements" => "http://registry.pinf.org/cadorn.org/insight/@meta/receiver/insight/selective/0",
                            "api" => "Insight/Plugin/Selective"
                        ),
                        "page" => array(
                            "implements" => "http://registry.pinf.org/cadorn.org/insight/@meta/receiver/console/page/0",
                            "api" => "Insight/Plugin/Page"
                        ),
                        "request" => array(
                            "implements" => "http://registry.pinf.org/cadorn.org/insight/@meta/receiver/console/request/0",
                            "api" => "Insight/Plugin/Request"
                        ),
                        "process" => array(
                            "implements" => "http://registry.pinf.org/cadorn.org/insight/@meta/receiver/console/process/0",
                            "api" => "Insight/Plugin/Process"
                        )
                    ),
                    'plugins' => array(
                        'assertion' => array(
                            'api' => 'Insight/Plugin/Assertion'
                        ),
                        'error' => array(
                            'api' => 'Insight/Plugin/Error'
                        ),
                        'patch' => array(
                            'api' => 'Insight/Plugin/Patch'
                        )
                    ),
                    "renderers" => array(
                        "insight" => array(
                            "uid" => "http://registry.pinf.org/cadorn.org/renderers/packages/insight/0"
                        ),
                        "php" => array(
                            "uid" => "http://registry.pinf.org/cadorn.org/renderers/packages/php/0"
                        )
                    )
                )
            )
        );
    }

    public function loadFromArray($config, $additionalConfig) {
        $this->config = $this->normalizeConfig($this->defaultConfig);
        $this->config = Insight_Util::array_merge($this->config, $this->normalizeConfig($config['package.json']));
        if($additionalConfig && is_array($additionalConfig)) {
            $this->config = Insight_Util::array_merge($this->config, $this->normalizeConfig($additionalConfig));
        }
        $credentials = $this->normalizeCredentials($config['credentials.json']);
        if(isset($credentials[self::CONFIG_META_URI])) {
            $this->config['implements'][self::CONFIG_META_URI] = Insight_Util::array_merge($this->config['implements'][self::CONFIG_META_URI], $credentials[self::CONFIG_META_URI]);
        }
        $this->validate();
    }

    public function loadFromFile($file, $additionalConfig) {
        if(!file_exists($file)) {
            throw new Exception('Config file not found at: ' . $file);
        }
        if(!is_readable($file)) {
            throw new Exception('Config file not readable at: ' . $file);
        }
        $this->file = $file;
        $this->config = $this->normalizeConfig($this->defaultConfig);
        if($additionalConfig && is_array($additionalConfig)) {
            $this->config = Insight_Util::array_merge($this->config, $this->normalizeConfig($additionalConfig));
        }
        $this->loadConfig($this->file);
        $this->loadConfig(str_replace(".json", ".local.json", $this->file));
        if(isset($this->config['implements']) &&
           isset($this->config['implements'][self::CONFIG_META_URI]) &&
           isset($this->config['implements'][self::CONFIG_META_URI]['credentialsPath'])) {
            $this->loadCredentials($this->config['implements'][self::CONFIG_META_URI]['credentialsPath']);
        } else {
            $this->loadCredentials(dirname($this->file) . DIRECTORY_SEPARATOR . 'credentials.json');
        }
        $this->loadCredentials(dirname($this->file) . DIRECTORY_SEPARATOR . 'credentials.local.json');
        $this->validate();
    }

    protected function loadConfig($file) {
        if(!file_exists($file)) {
            return false;
        }
        try {
            $json = Insight_Util::json_decode(file_get_contents($file));
            if(!$json) {
                throw new Exception();
            }
        } catch(Exception $e) {
            throw new Exception('Error (' . $this->getJsonError($file) . ') parsing JSON file "' . $file . '". You can validate this file at http://www.jsonlint.com/ to find any errors.');
        }
        $json = $this->normalizeConfig($json);
        $this->config = Insight_Util::array_merge($this->config, $json);
        return true;
    }

    protected function loadCredentials($file) {
        if(!file_exists($file)) {
            return false;
        }
        try {
            $credentials = Insight_Util::json_decode(file_get_contents($file));
            if(!$credentials) {
                throw new Exception();
            }
        } catch(Exception $e) {
            throw new Exception('Error (' . $this->getJsonError($file) . ') parsing JSON file "' . $file . '". You can validate this file at http://www.jsonlint.com/ to find any errors.');
        }
        $credentials = $this->normalizeCredentials($credentials);
        if(isset($credentials[self::CONFIG_META_URI])) {
            $this->config['implements'][self::CONFIG_META_URI] = Insight_Util::array_merge($this->config['implements'][self::CONFIG_META_URI], $credentials[self::CONFIG_META_URI], 'S');
        }
        return true;
    }

    protected function getJsonError($file) {
        $json = trim(file_get_contents($file));
        if(!$json) {
            return 'File is empty';
        }
        // check for comments and present customized error message if applicable
        $json = explode("\n", $json);
        for( $i=0 ; $i<sizeof($json) ; $i++ ) {
            if(substr(trim($json[$i]), 0, 2)=='//') {
                return 'Comments not allowed! Line: ' . ($i+1);
            }
        }
        if(!function_exists('json_last_error')) {
            return 'Cannot get detailed error message. json_last_error() not available.';
        }
        switch(json_last_error()) {
            case 0: // JSON_ERROR_NONE
                return 'No error has occurred';
            case 1: // JSON_ERROR_DEPTH
                return 'The maximum stack depth has been exceeded';
            case 2: // JSON_ERROR_STATE_MISMATCH
                return 'Invalid or malformed JSON';
            case 3: // JSON_ERROR_CTRL_CHAR
                return 'Control character error, possibly incorrectly encoded';
            case 4: // JSON_ERROR_SYNTAX
                return 'Syntax error';
            case 5: // JSON_ERROR_UTF8
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
        }
    }

    private function normalizeConfig($config) {
        
        if(isset($config['implements'])) {
            foreach( $config['implements'] as $key => $info ) {
                if(substr($key,0,5)!='http:') {
                    $config['implements']['http://registry.pinf.org/' . $key] = $info;
                    unset($config['implements'][$key]);
                }
            }
        }

        foreach(array('targets', 'renderers') as $prop1) {
            if(isset($config['implements'][self::CONFIG_META_URI][$prop1])) {
                foreach( $config['implements'][self::CONFIG_META_URI][$prop1] as $key => $info ) {
                    foreach(array('implements', 'uid') as $prop2) {
                        if(isset($info[$prop2]) && substr($info[$prop2],0,5)!='http:') {
                            $config[$prop2][self::CONFIG_META_URI][$prop1][$key][$prop2] = 
                                'http://registry.pinf.org/' . $config['implements'][self::CONFIG_META_URI][$prop1][$key][$prop2];
                        }
                    }
                }
            }
        }

        if(isset($config['implements'][self::CONFIG_META_URI]['credentialsPath'])) {
            $path = $config['implements'][self::CONFIG_META_URI]['credentialsPath'];
            if(substr($path, 0, 2)=="./") {
                if($this->file) {
                    $path = realpath( dirname($this->file) . DIRECTORY_SEPARATOR . substr($path,2) );
                }
            } else {
                $path = realpath($path);
            }
            $config['implements'][self::CONFIG_META_URI]['credentialsPath'] = $path;
        }

        if(isset($config['implements'][self::CONFIG_META_URI]['paths'])) {
            $paths = array();
            foreach( $config['implements'][self::CONFIG_META_URI]['paths'] as $path => $instruction ) {
                $key = false;
                if(substr($path, 0, 2)=="./") {
                    if($this->file) {
                        $key = realpath( dirname($this->file) . DIRECTORY_SEPARATOR . substr($path,2) );
                    }
                } else {
                    $key = realpath($path);
                }
                if (!$key) {
                    // TODO: Optionally log warning?
                } else {
                    $paths[$key] = $instruction;
                }
            }
            // sort alphabetically from longest to shortest
            krsort($paths);
            $config['implements'][self::CONFIG_META_URI]['paths'] = $paths;
        }

        if(isset($config['implements'][self::CONFIG_META_URI]['cache']) &&
           isset($config['implements'][self::CONFIG_META_URI]['cache']['path'])) {
            $path = $config['implements'][self::CONFIG_META_URI]['cache']['path'];
            if(substr($path, 0, 2)=="./") {
                if($this->file) {
                    $normalizedPath = realpath( dirname($this->file) . DIRECTORY_SEPARATOR . substr($path,2) );
                    if(!$normalizedPath) {
                        $normalizedPath = realpath(dirname($this->file)) . DIRECTORY_SEPARATOR . substr($path,2);
                    }
                    $config['implements'][self::CONFIG_META_URI]['cache']['path'] = $normalizedPath;
                }
            }
        }

        return $config;
    }

    private function normalizeCredentials($config) {
        foreach( $config as $key => $info ) {
            if(substr($key,0,5)!='http:') {
                $config['http://registry.pinf.org/' . $key] = $info;
                unset($config[$key]);
            }
        }
        // remove comments in authkeys and IPs
        if(isset($config[self::CONFIG_META_URI]) && isset($config[self::CONFIG_META_URI]['allow'])) {
            foreach( array('ips', 'authkeys') as $section ) {
                if(isset($config[self::CONFIG_META_URI]['allow'][$section])) {
                    for( $i=0,$c=sizeof($config[self::CONFIG_META_URI]['allow'][$section]) ; $i<$c ; $i++ ) {
                        $index = strpos($config[self::CONFIG_META_URI]['allow'][$section][$i], '//');
                        if($index>-1) {
                            $config[self::CONFIG_META_URI]['allow'][$section][$i] = trim(substr($config[self::CONFIG_META_URI]['allow'][$section][$i], 0, $index));
                        }
                    }
                }
            }
        }        
        return $config;
    }
    
    private function validate() {

        if(!isset($this->config['uid'])) {
            throw new Exception('"uid" config property not set in ' . $this->file);
        }

        if(isset($this->config['implements'][self::PACKAGE_META_URI])) {
            $config = $this->config['implements'][self::PACKAGE_META_URI];
            // TODO: validate
        }
        
        $config = $this->config['implements'][self::CONFIG_META_URI];
        
        $CONFIG_META_URI = str_replace("http://registry.pinf.org/", "", self::CONFIG_META_URI);

        if(isset($config['credentialsPath'])) {
            if(!file_exists($config['credentialsPath'])) {
                throw new Exception('"credentialsPath" config property does not refer to an existing file set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
            }
        }
        if(!isset($config['allow'])) {
            throw new Exception('"allow" config property not set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }
        if(!isset($config['allow']['ips'])) {
            throw new Exception('"allow.ips" config property not set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        } else
        if(!is_array($config['allow']['ips'])) {
            throw new Exception('"allow.ips" config property not an array set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        } else
        if(count($config['allow']['ips'])==0) {
            throw new Exception('"allow.ips" config property does not include at least one IP set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        } else
        if(count($config['allow']['ips'])>1 && in_array("*", $config['allow']['ips'])) {
            throw new Exception('"allow.ips" config property includes "*" with other IPs. If "*" is used it must be the only element. Set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }

        if(!isset($config['allow']['authkeys'])) {
            throw new Exception('"allow.authkeys" config property not set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        } else
        if(!is_array($config['allow']['authkeys'])) {
            throw new Exception('"allow.authkeys" config property not an array set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        } else
        if(count($config['allow']['authkeys'])>1 && in_array("*", $config['allow']['authkeys'])) {
            throw new Exception('"allow.authkeys" config property includes "*" with other authkeys. If "*" is used it must be the only element. Set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }

        if(!isset($config['server'])) {
            throw new Exception('"server" config property not set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }
        if(!isset($config['server']['path'])) {
            throw new Exception('"server.path" config property not set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        } else   
        if(substr($config['server']['path'], 0, 1)!="/" && substr($config['server']['path'], 0, 2)!="./") {
            throw new Exception('"server.path" config property must begin with a forward slash for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }
        if(isset($config['server']['port']) && (!is_numeric($config['server']['port']) || $config['server']['port']<=0)) {
            throw new Exception('"server.port" config property is not a valid port set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }
        if(isset($config['server']['secure']) && !is_bool($config['server']['secure'])) {
            throw new Exception('"server.secure" config property is not a boolean set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }
        if(!isset($config['targets'])) {
            throw new Exception('"targets" config property not set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }
        if(!isset($config['renderers'])) {
            throw new Exception('"renderers" config property not set for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }
        if(isset($config['cache']) && isset($config['cache']['path']) && !file_exists($config['cache']['path'])) {
            throw new Exception('"cache.path" [' . $config['cache']['path'] . '] does not exist for ' . $CONFIG_META_URI . ' in ' . $this->file . ' or other ([package|credentials][.local].json) config files');
        }
    }
    
    public function getPackageId() {
        return $this->config['uid'];
    }

    public function getPackageInfo() {        
        $info = array('links'=>array('quick'=>array()));
        if(isset($this->config['name'])) {
            $info['name'] = $this->config['name'];
        }
        if(isset($this->config['description'])) {
            $info['description'] = $this->config['description'];
        }
        if(isset($this->config['homepage'])) {
            $info['links']['quick']['Homepage'] = $this->config['homepage'];
        }
        if(isset($this->config['bugs'])) {
            $info['links']['quick']['Bugs'] = $this->config['bugs'];
        }
        if(isset($this->config['implements'][self::PACKAGE_META_URI])) {
            $info = Insight_Util::array_merge($info, $this->config['implements'][self::PACKAGE_META_URI]);
        }
        return $info;
    }
    
    public function getServerInfo() {
        if(!isset($this->config['implements'][self::CONFIG_META_URI]['server'])) {
            return false;
        }
        return $this->config['implements'][self::CONFIG_META_URI]['server'];
    }

    public function getPaths() {
        if(!isset($this->config['implements'][self::CONFIG_META_URI]['paths'])) {
            return false;
        }
        return $this->config['implements'][self::CONFIG_META_URI]['paths'];
    }

    public function getPlugins() {
        if(!isset($this->config['implements'][self::CONFIG_META_URI]['plugins'])) {
            return false;
        }
        return $this->config['implements'][self::CONFIG_META_URI]['plugins'];
    }

    public function getTargets() {
        if(!isset($this->config['implements'][self::CONFIG_META_URI]['targets'])) {
            return false;
        }
        return $this->config['implements'][self::CONFIG_META_URI]['targets'];
    }
    
    public function getRenderers() {
        if(!isset($this->config['implements'][self::CONFIG_META_URI]['renderers'])) {
            return false;
        }
        return $this->config['implements'][self::CONFIG_META_URI]['renderers'];
    }

    public function getPluginInfo($name) {
        $plugins = $this->getPlugins();
        if(!isset($plugins[$name])) {
            throw new Exception('"plugins.'.$name.'" config property not set');
        }
        return $plugins[$name];
    }

    public function getTargetInfo($name) {
        $targets = $this->getTargets();
        if(!isset($targets[$name])) {
            throw new Exception('"targets.'.$name.'" config property not set');
        }
        return $targets[$name];
    }

    public function getRendererInfo($name) {
        $renderers = $this->getRenderers();
        if(!isset($renderers[$name])) {
            throw new Exception('"renderers.'.$name.'" config property not set');
        }
        return $renderers[$name];
    }

    public function getAuthkeys() {
        return $this->config['implements'][self::CONFIG_META_URI]['allow']['authkeys'];
    }

    public function getIPs() {
        return $this->config['implements'][self::CONFIG_META_URI]['allow']['ips'];
    }
    
    public function getEncoderOptions() {
        if(!isset($this->config['implements'][self::CONFIG_META_URI]['encoder'])) {
            return array();
        }
        return $this->config['implements'][self::CONFIG_META_URI]['encoder'];
    }

    public function getCachePath($basePathOnly=false) {
        $nsPath = 'cadorn.org' . DIRECTORY_SEPARATOR . 'insight';
        if(!isset($this->config['implements'][self::CONFIG_META_URI]['cache']) ||
           !isset($this->config['implements'][self::CONFIG_META_URI]['cache']['path'])) {
            // check if we have a central PINF cache path
            // NOTE: Assumes we are running on a UNIX filesystem
            // TODO: Look for PINF_HOME or PINF_CACHE_DIR env vars instead of assuming
            $path = '/pinf/cache';
            if(is_dir($path)) {
                return $path . ( ($basePathOnly) ? '' : (DIRECTORY_SEPARATOR . $nsPath) );
            }
            $tmpDir = false;
            if(function_exists('sys_get_temp_dir')) {
                $tmpDir = sys_get_temp_dir();
            } else {
                $tmpDir = (($this->file)?dirname($this->file):dirname($_SERVER['SCRIPT_FILENAME'])) . DIRECTORY_SEPARATOR . '.cache';
                if(!file_exists($tmpDir)) {
                    if(!mkdir($tmpDir, 0775)) {
                        throw new Exception('Unable to create directory at: ' . $tmpDir);
                    }
                }
            }
            return $tmpDir . ( ($basePathOnly) ? '' : (DIRECTORY_SEPARATOR . $nsPath) );
        }
        return $this->config['implements'][self::CONFIG_META_URI]['cache']['path'] .
               ( ($basePathOnly) ? '' : (DIRECTORY_SEPARATOR . $nsPath) );
    }
}
