<?php

namespace ForsakenThreads\Diplomatic;

class SimpleXmlHandler extends ResponseHandler {

    protected $filtersXml = true;

    /**
     *
     * Check to see if the response was errored
     *
     * @return boolean
     */
    function wasErrored()
    {
        return
            // if the response failed to get parsed as JSON, the filtered response is the same as the raw response
            // if JSON decoding failed, something is seriously wrong, and we'll call that an errored response
            $this->filteredResponse == $this->rawResponse

            // if the HTTP status code is null or 0, then the curl call totally failed. probably an error
            || is_null($this->code) || $this->code == 0 || $this->code == '0'

            // if the HTTP status code is 500 or more, then chances are that's an error.
            || $this->code >= 500;
    }

    /**
     * Check to see if the response failed
     *
     * @return boolean
     */
    function wasFailed()
    {
        // if this was errored, it is not failed.
        if ($this->wasErrored()) {
            return false;
        }

        // any HTTP status code that is not a 2xx will be considered a failure
        return $this->code >= 300;
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