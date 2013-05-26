<?php

class Insight_Encoder_Default {

    const UNDEFINED = '_U_N_D_E_F_I_N_E_D_';
    const SKIP = '_S_K_I_P_';

    protected $options = array('depthNoLimit' => false,
                               'lengthNoLimit' => false,
                               'maxDepth' => 5,
                               'maxArrayDepth' => 3,
                               'maxArrayLength' => 25,
                               'maxObjectDepth' => 3,
                               'maxObjectLength' => 25,
                               'maxStringLength' => 5000,
                               'rootDepth' => 0,
                               'exception.traceOffset' => 0,
                               'exception.traceMaxLength' => -1,
                               'trace.maxLength' => -1,
                               'includeLanguageMeta' => true,
                               'treatArrayMapAsDictionary' => false);

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
        if(count($diff = array_diff(array_keys($options), array_keys($this->options)))>0) {
            throw new Exception('Unknown options: ' . implode(',', $diff));
        }
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
            $graph['origin'] = $this->_encodeVariable($this->_origin);
        }
        
        if($this->_instances) {
            foreach( $this->_instances as $key => $value ) {
                $graph['instances'][$key] = $value[1];
            }
        }

        if($this->getOption('includeLanguageMeta')) {
            if(!$this->_meta) {
                $this->_meta = array();
            }
            if(!isset($this->_meta['lang.id'])) {
                $this->_meta['lang.id'] = 'registry.pinf.org/cadorn.org/github/renderers/packages/php/master';
            }
        }

        // remove encoder options
        $meta = array();
        foreach( $this->_meta as $name => $value ) {
            if(!($name=="encoder" || substr($name, 0, 8)=="encoder.")) {
                $meta[$name] = $value;
            }
        }

        return array(Insight_Util::json_encode($graph), ($meta)?$meta:false);
    }


    protected function _encodeVariable($Variable, $ObjectDepth = 1, $ArrayDepth = 1, $MaxDepth = 1)
    {
/*        
        if($Variable===self::UNDEFINED) {
            $var = array('type'=>'constant', 'constant'=>'undefined');
            if($this->options['includeLanguageMeta']) {
                $var['lang.type'] = 'undefined';
            }
            return $var;
        } else
*/
        if(is_null($Variable)) {
            $var = array('type'=>'constant', 'constant'=>'null');
            if($this->getOption('includeLanguageMeta')) {
                $var['lang.type'] = 'null';
            }
        } else
        if(is_bool($Variable)) {
            $var = array('type'=>'constant', 'constant'=>($Variable)?'true':'false');
            if($this->getOption('includeLanguageMeta')) {
                $var['lang.type'] = 'boolean';
            }
        } else
        if(is_int($Variable)) {
            $var = array('type'=>'text', 'text'=>(string)$Variable);
            if($this->getOption('includeLanguageMeta')) {
                $var['lang.type'] = 'integer';
            }
        } else
        if(is_float($Variable)) {
            $var = array('type'=>'text', 'text'=>(string)$Variable);
            if($this->getOption('includeLanguageMeta')) {
                $var['lang.type'] = 'float';
            }
        } else
        if(is_object($Variable)) {
            $sub = $this->_encodeInstance($Variable, $ObjectDepth, $ArrayDepth, $MaxDepth);
            $var = array('type'=>'reference', 'reference'=> $sub['value']);
            if(isset($sub['meta'])) {
                $var = Insight_Util::array_merge($var, $sub['meta']);
            }
        } else
        if(is_array($Variable)) {
            if(isset($Variable[self::SKIP])) {
                unset($Variable[self::SKIP]);
                return $Variable;
            }
            $sub = null;
            // Check if we have an indexed array (list) or an associative array (map)
            if(Insight_Util::is_list($Variable)) {
                $sub = $this->_encodeArray($Variable, $ObjectDepth, $ArrayDepth, $MaxDepth);
                $var = array('type'=>'array', 'array'=> $sub['value']);
            } else
            if($this->getOption('treatArrayMapAsDictionary')) {
                $sub = $this->_encodeAssociativeArray($Variable, $ObjectDepth, $ArrayDepth, $MaxDepth);
                $var = array('type'=>'dictionary', 'dictionary'=> isset($sub['value'])?$sub['value']:false);
            } else {
                $sub = $this->_encodeAssociativeArray($Variable, $ObjectDepth, $ArrayDepth, $MaxDepth);
                $var = array('type'=>'map', 'map'=> isset($sub['value'])?$sub['value']:false);
            }
            if(isset($sub['meta'])) {
                $var = Insight_Util::array_merge($var, $sub['meta']);
            }
            if($this->getOption('includeLanguageMeta')) {
                $var['lang.type'] = 'array';
            }
        } else
        if(is_resource($Variable)) {
            // TODO: Try and get more info about resource
            $var = array('type'=>'text', 'text'=>(string)$Variable);
            if($this->getOption('includeLanguageMeta')) {
                $var['lang.type'] = 'resource';
            }
        } else
        if(is_string($Variable)) {
            $var = array('type'=>'text');
            // TODO: Add info about encoding
            if(Insight_Util::is_utf8($Variable)) {
                $var['text'] = $Variable;
            } else {
                $var['text'] = utf8_encode($Variable);
            }
            $maxLength = $this->getOption('maxStringLength');
            $lengthNoLimit = $this->getOption('lengthNoLimit');
            if($maxLength>=0 && strlen($var['text'])>=$maxLength && $lengthNoLimit!==true) {
                $var['encoder.trimmed'] = true;
                $var['encoder.trimmed.partial'] = true;
                $var['encoder.notice'] = 'Max String Length ('.$maxLength.') ' . (strlen($var['text'])-$maxLength) . ' more';
                $var['text'] = substr($var['text'], 0, $maxLength);
            }
            if($this->getOption('includeLanguageMeta')) {
                $var['lang.type'] = 'string';
            }
        } else {
            $var = array('type'=>'text', 'text'=>(string)$Variable);
            if($this->getOption('includeLanguageMeta')) {
                $var['lang.type'] = 'unknown';
            }
        }        
        return $var;
    }
    
    protected function _isObjectMemberFiltered($ClassName, $MemberName) {
        $filter = $this->getOption('filter');
        if(!isset($filter['classes']) || !is_array($filter['classes'])) {
            return false;
        }
        if(!isset($filter['classes'][$ClassName]) || !is_array($filter['classes'][$ClassName])) {
            return false;
        }
        return in_array($MemberName, $filter['classes'][$ClassName]);
    }
    
    protected function _getInstanceID($Object)
    {
        foreach( $this->_instances as $key => $instance ) {
            if($instance[0]===$Object) {
                return $key;
            }
        }
        return null;
    }
    
    protected function _encodeInstance($Object, $ObjectDepth = 1, $ArrayDepth = 1, $MaxDepth = 1)
    {
        if(($ret=$this->_checkDepth('Object', $ObjectDepth, $MaxDepth))!==false) {
            return $ret;
        }

        $id = $this->_getInstanceID($Object);
        if($id!==null) {
            return array('value'=>$id);
        }

        $id = sizeof($this->_instances);
        $this->_instances[$id] = array($Object);
        $this->_instances[$id][1] = $this->_encodeObject($Object, $ObjectDepth, $ArrayDepth, $MaxDepth);
        
        return array('value'=>$id);
    }    
    
    protected function _checkDepth($Type, $TypeDepth, $MaxDepth) {

        $depthNoLimit = $this->getOption('depthNoLimit');
        if($depthNoLimit===true) {
            return false;
        }

        $rootDepth = $this->getOption('rootDepth');

        // If we are traversing to $rootDepth we ignore or adjust max and type depth
        if($rootDepth>0) {
            if($MaxDepth == $TypeDepth) {
                if($MaxDepth <= $rootDepth) {
                    return false;
                }
                $TypeDepth -= $rootDepth;
            }
            $MaxDepth -= $rootDepth;
        }

        $maxDepthOption = $this->getOption('maxDepth');
        // NOTE: Not sure why >= is needed here (shout just be > as below but then it does not match up)
        if ($maxDepthOption>=0 && $MaxDepth >= $maxDepthOption) {
            return array(
                'value' => null,
                'meta' => array(
                    'encoder.trimmed' => true,
                    'encoder.notice' => 'Max Depth ('.$this->getOption('maxDepth').')'
                )
            );
        }

        $maxDepthOption = $this->getOption('max' . $Type . 'Depth');
        if ($maxDepthOption>=0 && $TypeDepth > $maxDepthOption) {
            return array(
                'value' => null,
                'meta' => array(
                    'encoder.trimmed' => true,
                    'encoder.notice' => 'Max ' . $Type . ' Depth ('.$this->getOption('max' . $Type . 'Depth').')'
                )
            );
        }
        return false;
    }
    
    protected function _encodeAssociativeArray($Variable, $ObjectDepth = 1, $ArrayDepth = 1, $MaxDepth = 1)
    {
        if(($ret=$this->_checkDepth('Array', $ArrayDepth, $MaxDepth))!==false) {
            return $ret;
        }

        $index = 0;
        $maxLength = $this->getOption('maxArrayLength');
        $lengthNoLimit = $this->getOption('lengthNoLimit');
        $isGlobals = false;

        foreach ($Variable as $key => $val) {
          // Encoding the $GLOBALS PHP array causes an infinite loop
          // if the recursion is not reset here as it contains
          // a reference to itself. This is the only way I have come up
          // with to stop infinite recursion in this case.
          if($key=='GLOBALS'
             && is_array($val)
             && array_key_exists('GLOBALS',$val)) {
            $isGlobals = true;
          }

          if($isGlobals) {
            switch($key) {
                case 'GLOBALS':
                    $val = array(
                        self::SKIP => true,
                        'encoder.trimmed' => true,
                        'encoder.notice' => 'Recursion (GLOBALS)'
                    );
                    break;
                case '_ENV':
                case 'HTTP_ENV_VARS':
                case '_POST':
                case 'HTTP_POST_VARS':
                case '_GET':
                case 'HTTP_GET_VARS':
                case '_COOKIE':
                case 'HTTP_COOKIE_VARS':
                case '_SERVER':
                case 'HTTP_SERVER_VARS':
                case '_FILES':
                case 'HTTP_POST_FILES':
                case '_REQUEST':
                    $val = array(
                        self::SKIP => true,
                        'encoder.trimmed' => true,
                        'encoder.notice' => 'Automatically Excluded (GLOBALS)'
                    );
                    break;
            }
          }

          if($this->getOption('treatArrayMapAsDictionary')) {
              if(!Insight_Util::is_utf8($key)) {
                  $key = utf8_encode($key);
              }
              $return[$key] = $this->_encodeVariable($val, 1, $ArrayDepth + 1);
          } else {
              $return[] = array($this->_encodeVariable($key), $this->_encodeVariable($val, 1, $ArrayDepth + 1, $MaxDepth + 1));
          }

          $index++;
          if($maxLength>=0 && $index>=$maxLength && $lengthNoLimit!==true) {
              if($this->getOption('treatArrayMapAsDictionary')) {
                  $return['...'] = array(
                    'encoder.trimmed' => true,
                    'encoder.notice' => 'Max Array Length ('.$maxLength.') ' . (count($Variable)-$maxLength) . ' more'
                  );
              } else {
                  $return[] = array(array(
                    'encoder.trimmed' => true,
                    'encoder.notice' => 'Max Array Length ('.$maxLength.') ' . (count($Variable)-$maxLength) . ' more'
                  ), array(
                    'encoder.trimmed' => true,
                    'encoder.notice' => 'Max Array Length ('.$maxLength.') ' . (count($Variable)-$maxLength) . ' more'
                  ));
              }
              break;
          }
        }
        return array('value'=>$return);
    }

    protected function _encodeArray($Variable, $ObjectDepth = 1, $ArrayDepth = 1, $MaxDepth = 1)
    {
        if(($ret=$this->_checkDepth('Array', $ArrayDepth, $MaxDepth))!==false) {
            return $ret;
        }

        $items = array();
        $index = 0;
        $maxLength = $this->getOption('maxArrayLength');
        $lengthNoLimit = $this->getOption('lengthNoLimit');
        foreach ($Variable as $val) {
          $items[] = $this->_encodeVariable($val, 1, $ArrayDepth + 1, $MaxDepth + 1);
          $index++;
          if($maxLength>=0 && $index>=$maxLength && $lengthNoLimit!==true) {
              $items[] = array(
                'encoder.trimmed' => true,
                'encoder.notice' => 'Max Array Length ('.$maxLength.') ' . (count($Variable)-$maxLength) . ' more'
              );
              break;
          }
        }
        return array('value'=>$items);
    }
    
    
    protected function _encodeObject($Object, $ObjectDepth = 1, $ArrayDepth = 1, $MaxDepth = 1)
    {
        $return = array('type'=>'dictionary', 'dictionary'=>array());

        $class = get_class($Object);
        if($this->getOption('includeLanguageMeta')) {
            if($Object instanceof Exception) {
                $return['lang.type'] = 'exception';
            } else {
                $return['lang.type'] = 'object';
            }
            $return['lang.class'] = $class;
        }

        $classAnnotations = $this->_getClassAnnotations($class);

        $properties = $this->_getClassProperties($class);
        $reflectionClass = new ReflectionClass($class);  
        
        if($this->getOption('includeLanguageMeta')) {
            $return['lang.file'] = $reflectionClass->getFileName();
        }

        $maxLength = $this->getOption('maxObjectLength');
        $lengthNoLimit = $this->getOption('lengthNoLimit');
        $maxLengthReached = false;
        
        $members = (array)$Object;
        foreach( $properties as $name => $property ) {
          
          if($name=='__insight_tpl_id') {
              $return['tpl.id'] = $property->getValue($Object);
              continue;
          }
          
          if($maxLength>=0 && count($return['dictionary'])>$maxLength && $lengthNoLimit!==true) {
              $maxLengthReached = true;
              break;
          }
          
          $info = array();
          $info['name'] = $name;
          
          $raw_name = $name;
          if($property->isStatic()) {
            $info['static'] = 1;
          }
          if($property->isPublic()) {
            $info['visibility'] = 'public';
          } else
          if($property->isPrivate()) {
            $info['visibility'] = 'private';
            $raw_name = "\0".$class."\0".$raw_name;
          } else
          if($property->isProtected()) {
            $info['visibility'] = 'protected';
            $raw_name = "\0".'*'."\0".$raw_name;
          }

          if(isset($classAnnotations['$'.$name])
             && isset($classAnnotations['$'.$name]['filter'])
             && $classAnnotations['$'.$name]['filter']=='on') {
                   
              $info['notice'] = 'Trimmed by annotation filter';
          } else
          if($this->_isObjectMemberFiltered($class, $name)) {
                   
              $info['notice'] = 'Trimmed by registered filters';
          }

          if(method_exists($property, 'setAccessible')) {
              $property->setAccessible(true);
          }

          if(isset($info['notice'])) {

              $info['trimmed'] = true;

              try {
                      
                  $info['value'] = $this->_trimVariable($property->getValue($Object));
                  
              } catch(ReflectionException $e) {
                  $info['value'] =  $this->_trimVariable(self::UNDEFINED);
                  $info['notice'] .= ', Need PHP 5.3 to get value';
              }

          } else {
              
            $value = self::UNDEFINED;

            if(array_key_exists($raw_name,$members)) {
//            if(array_key_exists($raw_name,$members)
 //              && !$property->isStatic()) {

                $value = $members[$raw_name];

            } else {
              try {
                  $value = $property->getValue($Object);
              } catch(ReflectionException $e) {
                  $info['value'] =  $this->_trimVariable(self::UNDEFINED);
                  $info['notice'] = 'Need PHP 5.3 to get value';
              }
            }

            if($value!==self::UNDEFINED) {
                // NOTE: This is a bit of a hack but it works for now
                if($Object instanceof Exception && $name=='trace' && $this->getOption('exception.traceOffset')!==null) {
                    $offset = $this->getOption('exception.traceOffset');
                    if($offset==-1) {
                        array_unshift($value, array(
                            'file' => $Object->getFile(),
                            'line' =>  $Object->getLine(),
                            'type' => 'throw',
                            'class' => $class,
                            'args' => array(
                                $Object->getMessage()
                            )
                        ));
                    } else
                    if($offset>0) {
                        array_splice($value, 0, $offset);
                    }
                }
                if($Object instanceof Exception && $name=='trace') {
                  $length = $this->getOption('exception.traceMaxLength');
                  if($length>0) {
                      $value = array_slice($value, 0, $length);
                  }
                }
            }
            
            if($value!==self::UNDEFINED) {
                $info['value'] = $this->_encodeVariable($value, $ObjectDepth + 1, 1, $MaxDepth + 1);
            }
          }
          
          $return['dictionary'][$info['name']] = $info['value'];
          if(isset($info['notice'])) {
              $return['dictionary'][$info['name']]['encoder.notice'] = $info['notice'];
          }
          if(isset($info['trimmed'])) {
              $return['dictionary'][$info['name']]['encoder.trimmed'] = $info['trimmed'];
          }
          if($this->getOption('includeLanguageMeta')) {
              if(isset($info['visibility'])) {
                  $return['dictionary'][$info['name']]['lang.visibility'] = $info['visibility'];
              }
              if(isset($info['static'])) {
                  $return['dictionary'][$info['name']]['lang.static'] = $info['static'];
              }
          }
//          $return['members'][] = $info;
        }
        
        if(!$maxLengthReached) {
            // Include all members that are not defined in the class
            // but exist in the object
            foreach( $members as $name => $value ) {
              
              if ($name{0} == "\0") {
                $parts = explode("\0", $name);
                $name = $parts[2];
              }

              if($maxLength>=0 && count($return['dictionary'])>$maxLength && $lengthNoLimit!==true) {
                  $maxLengthReached = true;
                  break;
              }
              
              if(!isset($properties[$name])) {
                
                $info = array();
                $info['undeclared'] = 1;
                $info['name'] = $name;
    
                if(isset($classAnnotations['$'.$name])
                   && isset($classAnnotations['$'.$name]['filter'])
                   && $classAnnotations['$'.$name]['filter']=='on') {
                           
                    $info['notice'] = 'Trimmed by annotation filter';
                } else
                if($this->_isObjectMemberFiltered($class, $name)) {
                           
                    $info['notice'] = 'Trimmed by registered filters';
                }
    
                if(isset($info['notice'])) {
                    $info['trimmed'] = true;
                    $info['value'] = $this->_trimVariable($value);
                } else {
                    $info['value'] = $this->_encodeVariable($value, $ObjectDepth + 1, 1, $MaxDepth + 1);
                }
    
                $return['dictionary'][$info['name']] = $info['value'];
                if($this->getOption('includeLanguageMeta')) {
                    $return['dictionary'][$info['name']]['lang.undeclared'] = 1;
                }
                if(isset($info['notice'])) {
                    $return['dictionary'][$info['name']]['encoder.notice'] = $info['notice'];
                }
                if(isset($info['trimmed'])) {
                    $return['dictionary'][$info['name']]['encoder.trimmed'] = $info['trimmed'];
                }
    
    //            $return['members'][] = $info;    
              }
            }
        }

        if($maxLengthReached) {
            $keys = array_keys($return['dictionary']);
            unset($return['dictionary'][array_pop($keys)]);
            $return['dictionary']['...'] = array(
              'encoder.trimmed' => true,
              'encoder.notice' => 'Max Object Length ('.$maxLength.') ' . (count($members)-$maxLength) . ' more'
            );
        }

        return $return;
    }

    protected function _trimVariable($var, $length=20)
    {
        if(is_null($var)) {
            $text = 'NULL';
        } else
        if(is_bool($var)) {
            $text = ($var)?'TRUE':'FALSE';
        } else
        if(is_int($var) || is_float($var) || is_double($var)) {
            $text = $this->_trimString((string)$var, $length);
        } else
        if(is_object($var)) {
            $text = $this->_trimString(get_class($var), $length);
        } else
        if(is_array($var)) {
            $text = $this->_trimString(serialize($var), $length);
        } else
        if(is_resource($var)) {
            $text = $this->_trimString('' . $var);
        } else
        if(is_string($var)) {
            $text = $this->_trimString($var, $length);
        } else {
            $text = $this->_trimString($var, $length);
        }
        return array(
            'type' => 'text',
            'text' => $text
        );
    }
    
    protected function _trimString($string, $length=20)
    {
        if(strlen($string)<=$length+3) {
            return $string;
        }
        return substr($string, 0, $length) . '...';
    }    
    
    protected function _getClassProperties($class)
    {
        $reflectionClass = new ReflectionClass($class);  
                
        $properties = array();

        // Get parent properties first
        if($parent = $reflectionClass->getParentClass()) {
            $properties = $this->_getClassProperties($parent->getName());
        }
        
        foreach( $reflectionClass->getProperties() as $property) {
          $properties[$property->getName()] = $property;
        }
        
        return $properties;
    }
    
    protected function _getClassAnnotations($class)
    {
        $annotations = array();
        
        // TODO: Go up to parent classes (let subclasses override tags from parent classes)
        
        try {
            $reflectionClass = new Zend_Reflection_Class($class);
            
            foreach( $reflectionClass->getProperties() as $property ) {
                
                $docblock = $property->getDocComment();
                if($docblock) {
                    
                    $tags = $docblock->getTags('insight');
                    if($tags) {
                        foreach($tags as $tag) {
                           
                           list($name, $value) = $this->_parseAnnotationTag($tag);
                           
                           $annotations['$'.$property->getName()][$name] = $value;
                        }
                    }
                }
            }
        } catch(Exception $e) {
            // silence errors (Zend_Reflection_Docblock_Tag throws if '@name(..)' tag found)
            // TODO: Optionally show these errors
        }

        return $annotations;
    }
    
    protected function _parseAnnotationTag($tag) {
        
        if(!preg_match_all('/^([^)\s]*?)\s*=\s*(.*?)$/si', $tag->getDescription(), $m)) {
            Insight_Annotator::setVariables(array('tag'=>$tag));
            throw new Exception('Tag format not valid!');
        }
        
        return array($m[1][0], $m[2][0]);
    }   
}
