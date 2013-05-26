<?php

class Insight_Encoder_JSON {

    const UNDEFINED = '_U_N_D_E_F_I_N_E_D_';

    protected $options = array();

    /**
     * @insight filter = on
     */
    protected $_origin = self::UNDEFINED;
    
    /**
     * @insight filter = on
     */
    protected $_meta = null;

    /**
     * @insight filter = on
     */
    protected $_instances = array();
    

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }    

    public function setOptions($options)
    {
//        if(count($diff = array_diff(array_keys($options), array_keys($this->options)))>0) {
//            throw new Exception('Unknown options: ' . implode(',', $diff));
//        }
        $this->options = Insight_Util::array_merge($this->options, $options);
    }

    public function setOrigin($variable)
    {
        $this->_origin = $variable;

        // reset some variables
        $this->_instances = array();

        return true;
    }

    public function setMeta($meta)
    {
        $this->_meta = $meta;
    }

    public function getOption($name) {
        // check for option in meta first, then fall back to default options
        if(isset($this->_meta['encoder.' . $name])) {
            return $this->_meta['encoder.' . $name];
        } else
        if(isset($this->options[$name])) {
            return $this->options[$name];
        }
        return null;
    }

    public function encode($data=self::UNDEFINED, $meta=self::UNDEFINED)
    {
        if($data!==self::UNDEFINED) {
            $this->setOrigin($data);
        }
        
        if($meta!==self::UNDEFINED) {
            $this->setMeta($meta);
        }

        $graph = array();
        
        if($this->_origin!==self::UNDEFINED) {
            $graph = $this->_origin;
        }

        // remove encoder options
        foreach( $this->_meta as $name => $value ) {
            if($name=="encoder" || substr($name, 0, 8)=="encoder.") {
                unset($this->_meta[$name]);
            }
        }

        return array(Insight_Util::json_encode($graph), ($this->_meta)?$this->_meta:false);
    }

}
