<?php namespace ForsakenThreads\Diplomatic;

use ForsakenThreads\Diplomatic\Support\CallableArgumentsPair;
use ForsakenThreads\Diplomatic\Support\CurlInfo;
use ForsakenThreads\Diplomatic\Support\Helpers;

abstract class ResponseHandler {

    // An equivalent of the request as a CLI curl call
    protected $cliCall;

    // Http status code for the response
    protected $code;

    // Result of curl_getinfo() for the curl request
    protected $curlInfo;

    // Http response headers
    protected $headers;

    // Http response version
    protected $htmlVersion;

    // The response after it has been filtered
    protected $filteredResponse;

    /** @var CallableArgumentsPair[] $filters */
    protected $filters;

    // Raw response
    protected $rawResponse;

    /**
     *
     * Check to see if the response was errored
     *
     * @return boolean
     */
    abstract function wasErrored();

    /**
     * Check to see if the response failed
     *
     * @return boolean
     */
    abstract function wasFailed();

    /**
     *
     * Check to see if the response was successful (true) or failed/errored (false)
     *
     * @return boolean
     */
    abstract function wasSuccessful();

    /**
     *
     * Returns a CLI curl version of the request
     *
     * @return string
     */
    public function getCliCall()
    {
        return $this->cliCall;
    }

    /**
     *
     * Http status code for the response
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     *
     * Get the response after it has been sent through any filters
     *
     * @return mixed
     */
    public function getFilteredResponse()
    {
        return $this->filteredResponse;
    }

    /**
     *
     * Get the Http response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *
     * Get the Http response version
     *
     * @return string
     */
    public function getHtmlVersion()
    {
        return $this->htmlVersion;
    }

    /**
     *
     * Get the raw response
     *
     * @return mixed
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     *
     * Provide access to the curlInfo property
     *
     * @return CurlInfo
     */
    public function info()
    {
        return $this->curlInfo;
    }

    /**
     *
     * Called by the Client to initialize the response handler
     *
     * @param string $rawResponse
     * @param string $htmlVersion
     * @param array $headers
     * @param integer $code
     * @param array $curlInfo
     * @param string $cliCall
     */
    public function initialize($rawResponse, $htmlVersion, array $headers, $code, $curlInfo, $cliCall)
    {
        $this->rawResponse = $rawResponse;
        $this->htmlVersion = $htmlVersion;
        $this->headers = $headers;
        $this->filteredResponse = $rawResponse;
        $this->code = $code;
        $this->curlInfo = new CurlInfo($curlInfo);
        $this->cliCall = $cliCall;
        $continue = 0;
        foreach ($this->filters as $filter) {
            if ($continue) {
                $continue--;
                continue;
            }
            try {
                $this->filteredResponse = call_user_func_array($filter->getCallable(), Helpers::array_enqueue($filter->getArguments(), $this->filteredResponse));
            } catch (InterruptContinue $interrupt) {
                // The InterruptContinue allows us to skip a number of filters
                $continue = (integer) $interrupt->getMessage();
            } catch (Interrupt $interrupt) {
                // The Interrupt is meant to halt any further application of the filters so we break out of the loop
                break;
            }
        }
    }

    /**
     *
     * Register a filter callback.  These are called after the response handler is initialized by the Client
     * The callback will receive the raw response as its first argument
     * Optional extra arguments may be passed here that will also be forwarded to the filter when invoked
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function filter(callable $callable)
    {
        $arguments = func_get_args();
        $callable = array_shift($arguments);
        $this->filters[] = new CallableArgumentsPair($callable, $arguments);
        return $this;
    }

}