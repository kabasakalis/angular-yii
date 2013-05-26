<?php

class Insight_Plugin_API {

    protected $temporaryTraceOffset = null;
    protected $traceOffset = 4;
    protected $message = null;
    protected $request = null;


    public function setRequest($request) {
        $this->request = $request;
    }

    public function setMessage($message) {
        $oldmsg = $this->message;
        $this->message = $message;
        return $oldmsg;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function setTemporaryTraceOffset($offset) {
        $this->temporaryTraceOffset = $offset;
    }

    // NOTE: This method will only work properly if called from a subclass
    //       and the result is passed to $this->message->meta($meta) 
    protected function _addFileLineMeta($meta=false, $data=false) {
        if(!$meta) {
            $meta = array();
        }
        // If file and line info is already present return right away
        if((isset($meta['file']) && isset($meta['line'])) ||
           (isset($this->message->meta['file']) && isset($this->message->meta['line']))) {
            return $meta;
        }
        if($data!==false && $data instanceof Exception && $this->temporaryTraceOffset==-1) {
            if(!isset($this->message->meta['file']) && !isset($meta['file'])) {
                $meta['file'] = $data->getFile();
            }
            if(!isset($this->message->meta['line']) && !isset($meta['line'])) {
                $meta['line'] = $data->getLine();
            }
        } else {
            $backtrace = debug_backtrace();
            $offset = $this->traceOffset;
            if(isset($this->message->meta['encoder.trace.offsetAdjustment'])) {
                $offset += $this->message->meta['encoder.trace.offsetAdjustment'];
            }
            if($this->temporaryTraceOffset!==null) {
                $offset = $this->temporaryTraceOffset;
                $this->temporaryTraceOffset = null;
            }
            if($offset>=0) {
                if(isset($backtrace[$offset]['file']) && !isset($this->message->meta['file']) && !isset($meta['file'])) {
                    $meta['file'] = $backtrace[$offset]['file'];
                }
                if(isset($backtrace[$offset]['line']) && !isset($this->message->meta['line']) && !isset($meta['line'])) {
                    $meta['line'] = $backtrace[$offset]['line'];
                }
            }
        }
        return $meta;
    }

    public function _shutdown() {
        $this->onShutdown();
    }

    protected function onShutdown() {
    }
}
