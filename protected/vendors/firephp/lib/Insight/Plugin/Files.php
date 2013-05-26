<?php

class Insight_Plugin_Files extends Insight_Plugin_API {

    public function loaded($files) {
        $meta = array(
            "encoder" => "JSON",
            "target" => "files/loaded"
        );
        $this->message->meta($this->_addFileLineMeta($meta))->send($files);
    }

    protected function onShutdown() {
        // TODO: Allow client to switch this off
        
        $files = get_included_files();

        // exclude our own files
        $excluding = false;
        for ($i=0 ; $i<count($files) ; $i++) {
            
            // TODO: Make this more reliable

            if (preg_match("/\/FirePHP\//", $files[$i]) ||
                preg_match("/\/FirePHPCore\//", $files[$i]) ||
                preg_match("/\/Insight\//", $files[$i]) ||
                preg_match("/\/Wildfire\//", $files[$i]) ||
                preg_match("/\/Zend\//", $files[$i]))
            {
                // potentially exclude
                $exclude = false;

                // start excluding when
                if (preg_match("/\/FirePHP\/Init.php$/", $files[$i])) {
                    $excluding = true;
                } else
                // stop excluding after
                if (preg_match("/\/Wildfire\/Protocol\/Component.php$/", $files[$i]) ||
-                   preg_match("/\/Insight\/Encoder\/Default.php$/", $files[$i]) ||
                    preg_match("/\/Zend\/Reflection\/Class.php$/", $files[$i]) ||
                    preg_match("/\/Zend\/Reflection\/Property.php$/", $files[$i]) ||
                    preg_match("/\/Zend\/Reflection\/Method.php$/", $files[$i]) ||
                    preg_match("/\/Zend\/Reflection\/Docblock.php$/", $files[$i]) ||
                    preg_match("/\/Zend\/Reflection\/Docblock\/Tag.php$/", $files[$i]) ||
                    preg_match("/\/Zend\/Loader.php$/", $files[$i]) ||
                    preg_match("/\/Zend\/Reflection\/Parameter.php$/", $files[$i]))
                {
                    $excluding = false;
                    $exclude = true;
                } else
                // always exclude
                if (preg_match("/\/FirePHP\//", $files[$i]) ||
                    preg_match("/\/FirePHPCore\//", $files[$i]) ||
                    preg_match("/\/Insight\//", $files[$i]) ||
                    preg_match("/\/Wildfire\//", $files[$i]))
                {
                    $exclude = true;
                }
                if ($excluding || $exclude)
                {
                    array_splice($files, $i, 1);
                    $i--;
                }
            }
        }
        Insight_Helper::to('request')->files()->loaded($files);
    }
}
