<?php

namespace ForsakenThreads\Diplomatic;

class BasicHandler extends ResponseHandler {

    /**
     *
     * Check to see if the response was errored
     *
     * @return boolean
     */
    function wasErrored()
    {
        return
            // if the HTTP status code is null or 0, then the curl call totally failed. probably an error
            is_null($this->code) || $this->code == 0 || $this->code == '0'

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