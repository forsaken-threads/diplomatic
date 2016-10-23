<?php namespace ForsakenThreads\Diplomatic;

use ForsakenThreads\Diplomatic\Support\DiplomaticException;
use ForsakenThreads\Diplomatic\Support\Helpers;

class Client {

    // An equivalent of the request as a CLI curl call
    protected $cliCall;

    // Http response code of the response
    protected $code;

    // The schema, host, port, and/or path for the request
    protected $destination;

    /**
     *
     * @var ResponseHandler
     *
     * The response handler
     */
    protected $responseHandler;

    // Callback handlers and extra arguments for the various response results
    protected $onAnyHandler;
    protected $onAnyHandlerExtraArgs;

    protected $onErrorHandler;
    protected $onErrorHandlerExtraArgs;

    protected $onFailureHandler;
    protected $onFailureHandlerExtraArgs;

    protected $onSuccessHandlerExtraArgs;
    protected $onSuccessHandler;

    // Boolean that determines whether to reset all handlers after a request
    protected $resetHandlers = true;

    // Http response version
    protected $responseHttpVersion;

    // Http response headers
    protected $responseHeaders;

    // Http headers for the request
    protected $headers = [];

    /**
     *
     * Client constructor
     * The scheme for the destination defaults to https:// if it is not provided
     *
     * @param $destination
     * @param ResponseHandler $responseHandler
     *
     * @throws DiplomaticException
     */
    public function __construct($destination, ResponseHandler $responseHandler)
    {
        $this->setDestination($destination)
            ->setResponseHandler($responseHandler);
    }

    /**
     *
     * Add Http headers to the request
     *
     * @param array $headers
     *
     * @return $this
     */
    public function addHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     *
     * A CONNECT request
     *
     * @param string $page
     *
     * @return $this
     */
    public function connect($page)
    {
        return $this->send($page, [], '-X CONNECT');
    }

    /**
     *
     * A DELETE request
     *
     * @param string $page
     * @param array $data
     *
     * @return $this|Client|mixed
     */
    public function delete($page, $data = [])
    {
        return $this->send($page, $data, '-X DELETE');
    }

    /**
     *
     * A GET request
     *
     * @param string $page
     * @param array $data
     *
     * @return $this|Client|mixed
     */
    public function get($page, $data = [])
    {
        return $this->send($page, $data);
    }

    /**
     *
     * A HEAD request
     *
     * @param string $page
     *
     * @return $this|Client|mixed
     */
    public function head($page)
    {
        return $this->send($page, [], '-I');
    }

    /**
     *
     * Register a callback (or anything but `null`) for any request result - supersedes all other handlers
     * The callback will receive the response handler as the first argument
     * Optional extra arguments may be passed here that will also be provided to the callback when invoked
     *
     * @param mixed $callback
     *
     * @return $this
     */
    public function onAny($callback)
    {
        $args = func_get_args();
        $callback = array_shift($args);
        $this->onAnyHandler = $callback;
        $this->onAnyHandlerExtraArgs = $args;
        return $this;
    }

    /**
     *
     * Register a callback (or anything but `null`) for an errored response
     * The callback will receive the response handler as the first argument
     * Optional extra arguments may be passed here that will also be provided to the callback when invoked
     *
     * @param mixed $callback
     *
     * @return $this
     */
    public function onError($callback)
    {
        $args = func_get_args();
        $callback = array_shift($args);
        $this->onErrorHandler = $callback;
        $this->onErrorHandlerExtraArgs = $args;
        return $this;
    }

    /**
     *
     * Register a callback (or anything but `null`) for a failed response
     * The callback will receive the response handler as the first argument
     * Optional extra arguments may be passed here that will also be provided to the callback when invoked
     *
     * @param mixed $callback
     *
     * @return $this
     */
    public function onFailure($callback)
    {
        $args = func_get_args();
        $callback = array_shift($args);
        $this->onFailureHandler = $callback;
        $this->onFailureHandlerExtraArgs = $args;
        return $this;
    }

    /**
     *
     * Register a callback (or anything but `null`) for a successful response
     * The callback will receive the response handler as the first argument
     * Optional extra arguments may be passed here that will also be provided to the callback when invoked
     *
     * @param mixed $callback
     *
     * @return $this
     */
    public function onSuccess($callback)
    {
        $args = func_get_args();
        $callback = array_shift($args);
        $this->onSuccessHandler = $callback;
        $this->onSuccessHandlerExtraArgs = $args;
        return $this;
    }

    /**
     *
     * Register an onSuccess handler that simply returns the response handler
     *
     * @return $this
     */
    public function onSuccessReturnResponseHandler()
    {
        return $this->onSuccess([$this, 'returnResponseHandler']);
    }


    /**
     *
     * An OPTIONS request
     *
     * @param string $page
     *
     * @return $this
     */
    public function options($page)
    {
        return $this->send($page, [], '-X OPTIONS');
    }

    /**
     *
     * A PATCH request
     *
     * @param string $page
     * @param array $data
     * @param array $files
     *
     * @return $this
     */
    public function patch($page, $data = [], $files = [])
    {
        return $this->send($page, $data, '-X PATCH', $files);
    }

    /**
     *
     * A POST request
     *
     * @param string $page
     * @param array $data
     * @param array $files
     *
     * @return $this
     */
    public function post($page, $data = [], $files = [])
    {
        return $this->send($page, $data, '-X POST', $files);
    }

    /**
     *
     * A PUT request
     *
     * @param string $page
     * @param array $data
     * @param array $files
     *
     * @return $this
     */
    public function put($page, $data = [], $files = [])
    {
        return $this->send($page, $data, '-X PUT', $files);
    }

    /**
     *
     * Set the resetHandlers property
     *
     * @param $reset
     *
     * @return $this
     */
    public function resetHandlersAfterRequest($reset)
    {
        $this->resetHandlers = @(boolean) $reset;
        return $this;
    }

    /**
     * @return ResponseHandler
     */
    public function returnResponseHandler()
    {
        return $this->responseHandler;
    }

    /**
     *
     * Save the cliCall into the provided variable
     *
     * @param $cliCall
     *
     * @return $this
     */
    public function saveCall(&$cliCall)
    {
        $cliCall = $this->cliCall;
        return $this;
    }

    /**
     *
     * Save the response handler into the provided variable
     *
     * @param ResponseHandler $responseHandler
     *
     * @return $this
     */
    public function saveResponseHandler(&$responseHandler)
    {
        $responseHandler = $this->responseHandler;
        return $this;
    }

    /**
     *
     * Save the Http code into the provided variable
     *
     * @param $code
     *
     * @return $this
     */
    public function saveResponseCode(&$code)
    {
        $code = $this->code;
        return $this;
    }

    /**
     *
     * Set the destination
     *
     * @param $destination
     *
     * @return $this
     *
     * @throws DiplomaticException
     */
    public function setDestination($destination)
    {
        $parsed = parse_url($destination);
        if (empty($parsed['host'])) {
            throw new DiplomaticException('Invalid argument.  Expected destination with a minimum of host and optionally a scheme, path and/or port.  Received: ' . @(string) $destination);
        }

        // Build up a proper destination based on the given one
        $this->destination = (empty($parsed['scheme']) ? 'https' : $parsed['scheme']) . '://';
        $this->destination .= $parsed['host'];
        $this->destination .= empty($parsed['port']) ? '' : (':' . $parsed['port']);
        $this->destination .= empty($parsed['path']) ? '' : $parsed['path'];
        return $this;
    }

    /**
     *
     * Set the Http headers for the request
     *
     * @param array $headers
     *
     * @return Client
     */
    public function setHeaders(array $headers)
    {
        $this->headers = [];
        return $this->addHeaders($headers);
    }

    /**
     *
     * Set the response handler
     *
     * @param ResponseHandler $responseHandler
     *
     * @return $this
     */
    public function setResponseHandler(ResponseHandler $responseHandler)
    {
        $this->responseHandler = $responseHandler;
        return $this;
    }

    /**
     *
     * A TRACE request
     *
     * @param string $page
     *
     * @return $this
     */
    public function trace($page)
    {
        return $this->send($page, [], '-X TRACE');
    }

    /**
     *
     * Tracks the Http headers in the response
     *
     * @param $curl
     * @param string $headerString
     *
     * @return int
     */
    protected function recordResponseHeaders($curl, $headerString)
    {
        // This is the first header, which should be providing the Http version info
        if (empty($this->responseHttpVersion)) {
            $this->responseHttpVersion = $headerString;
            return mb_strlen($headerString);
        }

        $parts = explode(':', $headerString, 2);
        if (!empty(trim($parts[0]))) {
            $this->responseHeaders[trim($parts[0])] = trim($parts[1]);
        }
        return mb_strlen($headerString);
    }

    /**
     *
     * Send the request
     *
     * @param string $page
     * @param array $data
     * @param string $method
     * @param array $files
     *
     * @return $this|mixed
     */
    protected function send($page, $data = [], $method = '-G', $files = [])
    {
        // TODO: handle files properly

        // make our data application/x-www-form-urlencoded
        $data = http_build_query($data);

        // initialize our cliCall. the -s options will silence. the -w option adds the Http response code to the output
        $cliCall = 'curl -s -w "%{http_code}" ' . $method;

        // setup the headers for curl and the cliCall
        $headers = [];
        foreach ($this->headers as $header => $value) {
            $cliCall .= ' -H "' . $header . ': ' . addslashes($value) . '"';
            $headers[] = $header . ': ' . $value;
        }

        // if this is not a GET request, we need to add the data to the cliCall
        if ($method != '-G' && $method != '-I') {
            $cliCall .= '-d "' . $data . '"';
        }

        // add the page to the destination, and if this is a GET request, the data as a query string
        $cliCall .= ' "' . $this->destination . $page . ($method == '-G' ? '?' . $data : '')  . '"';

        // reset for the new response coming up
        $this->responseHttpVersion = '';
        $this->responseHeaders = [];

        $curl = curl_init($this->destination . $page . ($method == '-G' ? '?' . $data : ''));

        // TODO: ssl and security type stuff

        // basic curl setup stuff
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, [$this, 'recordResponseHeaders']);
        if ($method != '-G' && $method != '-I') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        // set the proper request method
        switch ($method) {
            case '-I':
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
            case '-X CONNECT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'CONNECT');
                break;
            case '-X DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case '-X OPTIONS':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
                break;
            case '-X POST':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                break;
            case '-X PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case '-X TRACE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'TRACE');
                break;
            case '-G':
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                break;
        }

        // execute the request
        $rawResponse = curl_exec($curl);

        // if errored, we set the rawResponse to the curl error
        if (curl_errno($curl)) {
            $rawResponse = curl_error($curl);
        }

        // save the info from curl
        $curlInfo = curl_getinfo($curl);

        // close the curl session
        curl_close($curl);

        // set cliCall and code in case the Developer wants to save them as part of a chained method call
        $this->cliCall = $cliCall;
        $this->code = $curlInfo['http_code'];

        // initialize the response handler with the basics
        $this->responseHandler->initialize($rawResponse, $this->responseHttpVersion, $this->responseHeaders, $curlInfo['http_code'], $curlInfo, $cliCall);

        // check if the response handler is self-handling
        if ($this->responseHandler instanceof SelfHandling) {
            if ($this->responseHandler->wasErrored()) {
                return $this->responseHandler->onError();
            }

            if ($this->responseHandler->wasFailed()) {
                return $this->responseHandler->onFailure();
            }

            if ($this->responseHandler->wasSuccessful()) {
                return $this->responseHandler->onSuccess();
            }
        }

        // if errored, check for onError handler
        if ($this->onErrorHandler !== null && $this->responseHandler->wasErrored()) {
            return is_callable($this->onErrorHandler)
                ? call_user_func_array($this->onErrorHandler, Helpers::array_enqueue($this->onErrorHandlerExtraArgs, $this->responseHandler))
                : $this->onErrorHandler;
        }

        // if failed, check for onFailure handler
        if ($this->onFailureHandler !== null && $this->responseHandler->wasFailed()) {
            return is_callable($this->onFailureHandler)
                ? call_user_func_array($this->onFailureHandler, Helpers::array_enqueue($this->onFailureHandlerExtraArgs, $this->responseHandler))
                : $this->onFailureHandler;
        }

        // if successful, check for onSuccess handler
        if ($this->onSuccessHandler !== null && $this->responseHandler->wasSuccessful()) {
            return is_callable($this->onSuccessHandler)
                ? call_user_func_array($this->onSuccessHandler, Helpers::array_enqueue($this->onSuccessHandlerExtraArgs, $this->responseHandler))
                : $this->onSuccessHandler;
        }

        // check for onAny handler as a last fallback
        if ($this->onAnyHandler !== null) {
            return is_callable($this->onAnyHandler)
                ? call_user_func_array($this->onAnyHandler, Helpers::array_enqueue($this->onAnyHandlerExtraArgs, $this->responseHandler))
                : $this->onAnyHandler;
        }

        // if required reset the handlers
        if ($this->resetHandlers) {
            $this->onAnyHandler = null;
            $this->onErrorHandler = null;
            $this->onFailureHandler = null;
            $this->onSuccessHandler = null;
        }

        return $this;
    }

}