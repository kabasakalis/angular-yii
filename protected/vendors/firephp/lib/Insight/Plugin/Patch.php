<?php

class Insight_Plugin_Patch extends Insight_Plugin_API {

    protected $patchDirectories = array();

    public function addPatchDirectory($path)
    {
        $this->patchDirectories[$path] = true;
    }


    public function getClassFilePath($class, $path)
    {
        $info = $this->_getPatchInfoForClass($class);
        if ($info['mtime'] === 0) return $path;
        
        $info['mtime'] = max($info['mtime'], filemtime($path));
        
        $file = $this->request->cachePathForName('patched/classes/' . $this->_filenameForClass($class), 'client');
        
        if (!file_exists($file) || filemtime($file) < $info['mtime']) {
            $this->_applyPatch($path, $info, $file);
        }

        return $file;
    }


    protected function _getPatchInfoForClass($class)
    {
        $filename = $this->_filenameForClass($class);
        $info = array(
            'patches' => array(),
            'mtime' => 0
        );
        foreach (array_keys($this->patchDirectories) as $basePath) {
            if (file_exists($basePath . '/' . $filename)) {
                $patch = array(
                    'path' => $basePath . '/' . $filename,
                    'mtime' => filemtime($basePath . '/' . $filename),
                    'sections' => $this->_parsePatch($basePath . '/' . $filename)
                );
                $info['mtime'] = max($info['mtime'], $patch['mtime']);
                $info['patches'][] = $patch;
            }
        }
        return $info;
    }
    
    protected function _filenameForClass($class)
    {
        return str_replace('\\', '_', $class) . '.patch';
    }
    
    protected function _parsePatch($path)
    {
        $sections = array();
        $patchSections = explode("***************", file_get_contents($path));
        for ($i=0,$ic=sizeof($patchSections) ; $i<$ic ; $i++) {
            if ($patchSections[$i] && preg_match_all('/^\n?\*{3}\s+(\d*),(\d*)\s+\*{4}\n((.|\n)*?)-{3}\s+(\d*),(\d*)\s+\-{4}((.|\n)*)$/', $patchSections[$i], $m)) {
                $sections[] = array(
                    'match' => array(
                        'from' => $m[1][0],
                        'to' => $m[2][0],
                        'str' => $m[3][0]
                    ),
                    'replace' => array(
                        'from' => $m[5][0],
                        'to' => $m[6][0],
                        'str' => $m[7][0]
                    )
                );
            }
        }
        return $sections;
    }

    protected function _sourceTrim($in)
    {
        if (is_string($in)) {
            return trim(substr($in, 1));
        } else
        if (is_array($in)) {
            $out = array();
            for ($i=0,$ic=sizeof($in) ; $i<$ic ; $i++) {
                $out[] = trim(substr($in[$i], 1));
            }
            return $out;
        }
    }

    protected function _applyPatch($sourcePath, $patchInfo, $targetPath)
    {
        $source = explode("\n", file_get_contents($sourcePath));
        $patches = array();
        // assemble patches from different files
        for ($i=sizeof($patchInfo['patches'])-1 ; $i>=0 ; $i--) {
            for ($j=sizeof($patchInfo['patches'][$i]['sections'])-1 ; $j>=0 ; $j--) {
                $patches[$patchInfo['patches'][$i]['sections'][$j]['match']['from']] = $patchInfo['patches'][$i]['sections'][$j];
            }
        }
        // sort and process patches from last to first
        ksort($patches, SORT_NUMERIC);
        $patches = array_values($patches);
        for ($i=sizeof($patches)-1 ; $i>=0 ; $i--) {

            $patchSource = explode("\n", $patches[$i]['match']['str']);

            // find starting line by matching exact first, then going 5 up or 5 down if not found
            $found = false;
            if (trim($source[$patches[$i]['match']['from']-1]) == $this->_sourceTrim($patchSource[0])) {
                $found = $patches[$i]['match']['from'];
            }
            if (!$found) {
                for ($i=1 ; $i<=5 ; $i++) {
                    if (trim($source[$patches[$i]['match']['from']-1-$i]) == $this->_sourceTrim($patchSource[0])) {
                        $found = $patches[$i]['match']['from'] - $i;
                        break;
                    } else
                    if (trim($source[$patches[$i]['match']['from']-1+$i]) == $this->_sourceTrim($patchSource[0])) {
                        $found = $patches[$i]['match']['from'] + $i;
                        break;
                    }
                }
            }
            if ($found !== false) {
                // ensure all lines from before match
                for ($j=0,$jc=($patches[$i]['match']['to']-$patches[$i]['match']['from']) ; $j < $jc ; $j++) {
                    if (trim($source[$found-1+$j]) != $this->_sourceTrim($patchSource[$j])) {
                        $found = false;
                        break;
                    }
                }
                // if all lines match continue with applying patch
                if ($found) {
                    array_splice($source, $found-1, $patches[$i]['match']['to']-$patches[$i]['match']['from']+1, $this->_sourceTrim(explode("\n", $patches[$i]['replace']['str'])));
                }
            }
        }
        if (!file_exists(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0775, true);
        }
        file_put_contents($targetPath, implode("\n", $source));
    }
}
