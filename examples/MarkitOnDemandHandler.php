<?php

use ForsakenThreads\Diplomatic\Support\BasicFilters;
use ForsakenThreads\Diplomatic\ResponseHandler;

class MarkitOnDemandHandler extends ResponseHandler {

    public function __construct()
    {
        $this->filter([BasicFilters::class, 'json'], true);
    }

    /**
     *
     * Check to see if the response was errored
     *
     * @return boolean
     */
    function wasErrored()
    {
        // if the response failed to get parsed as JSON, the filtered response is the same as the raw response
        // if JSON decoding failed, something is seriously wrong, and we'll call that an errored response
        return $this->filteredResponse == $this->rawResponse;
    }

    /**
     * Check to see if the response failed
     *
     * @return boolean
     */
    function wasFailed()
    {
        // if this was errored, it is not failed.
        // if it wasn't errored, but the Message key does not exist on the filtered response, it is also not failed
        if ($this->wasErrored() || !key_exists('Message', $this->filteredResponse)) {
            return false;
        }

        // not errored and there is a Message key. that's a failure
        return true;
    }

    /**
     *
     * Check to see if the response was successful (true) or failed/errored (false)
     *
     * @return boolean
     */
    function wasSuccessful()
    {
        return !$this->wasErrored() && !$this->wasFailed();
    }
}