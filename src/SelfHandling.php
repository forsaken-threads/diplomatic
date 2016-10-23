<?php namespace ForsakenThreads\Diplomatic;

interface SelfHandling {

    /**
     *
     * Invoked on an errored response
     *
     * @return mixed
     */
    function onError();

    /**
     *
     * Invoked on a failed response
     *
     * @return mixed
     */
    function onFailure();

    /**
     *
     * Invoked on a successful response
     *
     * @return mixed
     */
    function onSuccess();
}