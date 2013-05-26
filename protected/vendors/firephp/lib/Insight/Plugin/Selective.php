<?php

class Insight_Plugin_Selective extends Insight_Plugin_Console {
    
    protected $defaultFilters = null;

    protected $filters = null;
    
    protected $usedFilters = null;
    
    protected $openStack = array();


    protected function _loadFilters() {
        if($this->filters===null) {
            if ($this->request->isClientPresent()) {
                $this->defaultFilters = $this->request->getFromClientCache('filters');
                $this->filters = $this->request->getFromClientUrlCache('filters');
            }
            if(!$this->defaultFilters) $this->defaultFilters = array();
            if(!$this->filters) $this->filters = array();
            $this->usedFilters = array();
        }
    }

    protected function _keyForName($name, $parents, $includeTarget = true) {
        if($includeTarget && isset($this->message->meta['target'])) {
            return $this->message->meta['target'] . ((sizeof($parents)>0)?("|".implode("|", $parents)):"") . '|' . $name;
        } else {
            return ((sizeof($parents)>0)?(implode("|", $parents)."|"):"") . $name;
        }
    }

    protected function _getFilter($name, $parents) {
        $this->_loadFilters();
        $key = $this->_keyForName($name, $parents);

        $enabled = false;
        if(isset($this->filters[$key])) {
            $enabled = $this->filters[$key]['enabled'];
        } else
        if(isset($this->defaultFilters[$key])) {
            $enabled = $this->defaultFilters[$key]['enabled'];
        }

        $this->usedFilters[$key] = array(
            'name' => $name,
            'enabled' => $enabled
        );
        if(isset($this->message->meta['target'])) {
            $this->usedFilters[$key]['target'] = $this->message->meta['target'];
        }
        return $this->usedFilters[$key];
    }

    protected function onShutdown() {
        if (!$this->usedFilters || !$this->request->isClientPresent())
            return;
        $this->request->storeInCache('filters', $this->usedFilters);
        Insight_Helper::to('selective')->getMessage()->meta(array(
            "encoder" => "JSON"
        ))->send(array(
            "filters" => $this->usedFilters
        ));
    }

    public function on($name) {
        if (strpos($name, '|') !== false)
            throw new Exception("on() labels may not contain the '|' (pipe) character");
        $parents = isset($this->message->meta['.selective.parents'])?$this->message->meta['.selective.parents']:array();
        if (sizeof($parents) === 0 && sizeof($this->openStack) > 0)
            $parents = explode("|", $this->openStack[sizeof($this->openStack)-1]);
        if (sizeof($parents)>0 && $parents[sizeof($parents)-1] == $name)
            $parents = array_slice($parents, 0, -1);
        $filter = $this->_getFilter($name, $parents);
        if($filter['enabled']) {
            array_push($parents, $name);
            return $this->message->meta(array(
                '.selective.parents' => $parents
            ));
        } else {
            return Insight_Helper::getNullMessage();
        }
    }

    public function open() {
        $parents = isset($this->message->meta['.selective.parents'])?$this->message->meta['.selective.parents']:array();
        $key = $this->_keyForName($parents[sizeof($parents)-1], array_slice($parents, 0, sizeof($parents)-1), false);
        array_push($this->openStack, $key);
    }

    public function close() {
        $parents = isset($this->message->meta['.selective.parents'])?$this->message->meta['.selective.parents']:array();
        $key = $this->_keyForName($parents[sizeof($parents)-1], array_slice($parents, 0, sizeof($parents)-1), false);
        $last = array_pop($this->openStack);
        if ($last != $key) {
            throw new Exception('on()->open/close() nesting is incorrect for open[' + $last + '] close[' + $key + ']');
        }
    }

    public function respond($server, $request) {

        if($request->getAction()=='ToggleFilter') {

            $this->_loadFilters();

            $key = $request->getArgument('key');

            if(isset($this->filters[$key])) {
                if ($request->hasArgument('enabled')) {
                    $this->filters[$key]['enabled'] = $request->getArgument('enabled');
                } else {
                    $this->filters[$key]['enabled'] = !$this->filters[$key]['enabled'];
                }
            }
            if (!$this->defaultFilters) {
                $this->defaultFilters[$key] = array();
            }
            $this->defaultFilters[$key]['enabled'] = $this->filters[$key]['enabled'];

            $request->storeInClientCache('filters', $this->defaultFilters);
            $request->storeInClientUrlCache('filters', $this->filters);

            return array(
                'type' => 'text/plain',
                'data' => Insight_Util::json_encode(array(
                    'filters' => $this->filters
                ))
            );
        }
        return false;
    }
}
