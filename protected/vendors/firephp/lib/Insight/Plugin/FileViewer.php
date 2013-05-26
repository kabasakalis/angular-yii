<?php

class Insight_Plugin_FileViewer extends Insight_Plugin_API
{
    public function respond($server, $request) {

        if($request->getAction()=='ListFiles') {
/*            
            $basePath = $server->getOption('applicationRootPath');
            
            // TODO: Simplify
            if(!$args['id']) {
                $data = array(
                    'id' => '___ROOT___',
                    'name' => 'Application',
                    'children' => array()
                );
            } else {
                $data = array(
                    'id' => $args['id'],
                    'name' => array_pop(explode("/", $args['id'])),
                    'children' => array()
                );
            }
            
            $path = $basePath;
            if($args['id']) {
                $path .= DIRECTORY_SEPARATOR . $args['id'];
            }

            foreach( new DirectoryIterator($path) as $item ) {
                if(!$item->isDot() &&
                   substr($item->getFilename(),0,5)!='.tmp_' &&
                   substr($item->getFilename(),0,9)!='.DS_Store') {
                    
                    $node = array(
                        'name' => $item->getFilename()
                    );
                    
                    $id = $item->getFilename();
                    if($args['id']) {
                        $id = $args['id'] . DIRECTORY_SEPARATOR . $id;
                    }

                    if($item->isDir()) {
                        $node['$ref'] = $id;
                        $node['children'] = true;
                    } else
                    if($item->isFile()) {
                        $node['id'] = $id;
                    }

                    $data['children'][] = $node;
                }
            }

            return array(
                'type' => 'json',
                'data' => $data
            );
*/            
        } else
        if($request->getAction()=='GetFile') {
            $args = $request->getArguments();

            $file = false;
            if(isset($args['path'])) {
                $file = $server->canServeFile($args['path']);
            } else {
//                $basePath = $server->getOption('applicationRootPath');
//                $path = realpath($basePath . DIRECTORY_SEPARATOR . $args['id']);
            }

            if(!$file) {
                return array(
                    'type' => 'error',
                    'status' => '403'
                );
            }

            return array(
                'type' => 'text/plain',
                'data' => file_get_contents($file)
            );
        }
        return false;
    }
}
