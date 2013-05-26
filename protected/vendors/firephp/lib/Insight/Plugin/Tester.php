<?php

class Insight_Plugin_Tester extends Insight_Plugin_API
{
    public function respond($server, $request) {

        if($request->getAction()=='TestClient') {

            FirePHP::to('controller')->triggerClientTest($request->getArguments());

            return array(
                'type' => 'text/plain',
                'data' => "OK"
            );
        }
        return false;
    }
}
