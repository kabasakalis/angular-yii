<?php

class Insight_Plugin_Package extends Insight_Plugin_API {
    
    private $info = false;


    public function setInfo($info) {
        $this->info = $info;
    }
    
    public function addQuickLink($label, $info) {
        if(!$this->info) $this->info = array();
        if(!isset($this->info['links'])) $this->info['links'] = array();
        if(!isset($this->info['links']['quick'])) $this->info['links']['quick'] = array();
        if(isset($this->info['links']['quick'][$label])) {
            throw new Exception('Quick link with label "' . $label . '" alreadt exists!');
        }
        $this->info['links']['quick'][$label] = $info;
    }

    protected function onShutdown() {
        if (!$this->info) return;
        if (!$this->request->isClientPresent()) return;
        // only send info to client if it has changed
        $packageInfo = $this->request->getFromClientCache('package-info', false);
        if ($packageInfo == serialize($this->info))
            return;
        $this->request->storeInClientCache('package-info', serialize($this->info), false);
        Insight_Helper::to('package')->getMessage()->meta(array(
            "encoder" => "JSON",
            "target" => "info"
        ))->send($this->info);
    }

    public function respond($server, $request) {

        if($request->getAction()=='GetInfo') {            
            if($packageInfo = $server->getConfig()->getPackageInfo()) {
                return array(
                    'type' => 'json',
                    'data' => $packageInfo
                );
            } else {
                return "";
            }
        }
        return false;
    }
}
