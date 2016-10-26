<?php

use ForsakenThreads\Diplomatic\ResponseHandler;
use ForsakenThreads\Diplomatic\SelfHandling;

class Handler extends ResponseHandler {

    function wasErrored()
    {
        return $this->filteredResponse == 'Error';
    }

    function wasFailed()
    {
        return $this->filteredResponse == 'Failed';
    }

    function wasSuccessful()
    {
        return !$this->wasErrored() && !$this->wasFailed();
    }
}

class SelfHandler extends Handler implements SelfHandling {

    function onError()
    {
       return 'WasErrored';
    }

    function onFailure()
    {
        return 'WasFailed';
    }

    function onSuccess()
    {
        return 'WasSuccessful';
    }
}

