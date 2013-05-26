<?php

class Insight_Plugin_Assertion extends Insight_Plugin_API {
    
    protected $traceOffset = 6;

    protected $assertionErrorConsole = null;

    /**
     * Capture all assertion errors and send to provided console
     * 
     * @return mixed Returns the original setting or FALSE on error
     */
    public function onAssertionError($console) {
        $this->assertionErrorConsole = $console;
        return assert_options(ASSERT_CALLBACK, array($this, '_assertionErrorHandler'));
    }

    public function _assertionErrorHandler($file, $line, $code) {
        if(!$this->assertionErrorConsole) {
            return;
        }
        $this->assertionErrorConsole->setTemporaryTraceOffset($this->traceOffset);
        $this->assertionErrorConsole->meta(array(
            'encoder.rootDepth' => 5,
            'encoder.exception.traceOffset' => 1
        ))->error(new ErrorException('Assertion Failed - Code[ '.$code.' ]', 0, null, $file, $line));
    }
}
