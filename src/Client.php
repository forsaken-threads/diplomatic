<?php namespace ForsakenThreads\Diplomatic;

use CURLFile;
use ForsakenThreads\Diplomatic\Support\DiplomaticException;
use ForsakenThreads\Diplomatic\Support\Helpers;
use SplFileInfo;

class Client {

    // The body of the request, only used for `GET` requests, otherwise body is sent as `$data`
    protected $body = '';

    // An equivalent of the request as a CLI curl call
    protected $cliCall;

    // Http response code of the response
    protected $code;

    // The schema, host, port, and/or path for the request
    protected $destination;

    // Http headers for the request
    protected $headers = [];

    // Boolean that determines whether to ignore SSL errors
    protected $isInsecure = false;

    // Boolean that determines whether POST should be done as multipart/form-data or application/x-www-form-urlencoded
    protected $isMultipart = false;

    // Callback handlers and extra arguments for the various response results
    protected $onAnyHandler;
    protected $onAnyHandlerExtraArgs;

    protected $onErrorHandler;
    protected $onErrorHandlerExtraArgs;

    protected $onFailureHandler;
    protected $onFailureHandlerExtraArgs;

    protected $onSuccessHandler;
    protected $onSuccessHandlerExtraArgs;

    // The raw response from the curl call
    protected $rawResponse;

    // Boolean that determines whether to reset all handlers after a request
    protected $resetHandlers = true;

    /**
     *
     * The response handler
     *
     * @var ResponseHandler
     */
    protected $responseHandler;

    // Http response headers
    protected $responseHeaders;

    // Http response version
    protected $responseHttpVersion;

    // the user agent string to set for all requests
    protected $userAgent;

    /**
     *
     * Client constructor
     *
     * The scheme for the destination defaults to `https://` if it is not provided
     *
     * @param $destination
     * @param null|string|ResponseHandler $responseHandler
     *
     * @throws DiplomaticException
     */
    public function __construct($destination, $responseHandler = null)
    {
        $this->setDestination($destination)
            ->setResponseHandler($responseHandler);
        $this->userAgent = "forsaken-threads/diplomatic wrapping cURL version " . curl_version()['version'];
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
     * A `DELETE` request
     *
     * @param string $page
     * @param array|string $data
     *
     * @return $this|mixed
     */
    public function delete($page, $data = [])
    {
        return $this->send($page, $data, '-X DELETE');
    }

    /**
     *
     * A `GET` request
     *
     * To send query string parameters, `$data` must be an associative array.
     * To send only a body, `$data` can be a string, and the `$body` argument will be ignored.
     * Of course, to use both, send an array and a string.
     *
     * @param string $page
     * @param array|string $data
     * @param string $body
     *
     * @return $this|mixed
     */
    public function get($page, $data = [], $body = '')
    {
        if (is_string($data)) {
            $this->body = $data;
            $data = [];
        } else {
            $this->body = $body;
        }
        return $this->send($page, $data);
    }

    /**
     *
     * A `HEAD` request
     *
     * @param string $page
     *
     * @return $this|mixed
     */
    public function head($page)
    {
        return $this->send($page, [], '-I');
    }

    public function insecure($isInsecure = true)
    {
        $this->isInsecure = (boolean) $isInsecure;
        return $this;
    }

    /**
     *
     * Register a callback (or anything but `null`) for any request result - acts as a catch-all for missing handlers
     *
     * The callback will receive the response handler as the first argument.
     * Optional extra arguments may be passed here that will also be provided to the callback when invoked.
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
     *
     * The callback will receive the response handler as the first argument.
     * Optional extra arguments may be passed here that will also be provided to the callback when invoked.
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
     *
     * The callback will receive the response handler as the first argument.
     * Optional extra arguments may be passed here that will also be provided to the callback when invoked.
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
     *
     * The callback will receive the response handler as the first argument
     * Optional extra arguments may be passed here that will also be provided to the callback when invoked.
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
     * An `OPTIONS` request
     *
     * @param string $page
     *
     * @return $this|mixed
     */
    public function options($page)
    {
        return $this->send($page, [], '-X OPTIONS');
    }

    /**
     *
     * A `PATCH` request
     *
     * @param string $page
     * @param array|string $data
     * @param array $files
     *
     * @return $this|mixed
     */
    public function patch($page, $data = [], array $files = [])
    {
        return $this->send($page, $data, '-X PATCH', $files);
    }

    /**
     *
     * A `POST` request
     *
     * @param string $page
     * @param array|string $data
     * @param array $files
     *
     * @return $this|mixed
     */
    public function post($page, $data = [], array $files = [])
    {
        return $this->send($page, $data, '-X POST', $files);
    }

    /**
     *
     * A `PUT` request
     *
     * @param string $page
     * @param array|string $data
     * @param array $files
     *
     * @return $this|mixed
     */
    public function put($page, $data = [], array $files = [])
    {
        return $this->send($page, $data, '-X PUT', $files);
    }

    /**
     *
     * Set the `resetHandlers` property
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
     *
     * Save the `cliCall` into the provided variable
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
     * Save the raw response into the provided variable
     *
     * @param $rawResponse
     * @return $this
     */
    public function saveRawResponse(&$rawResponse)
    {
        $rawResponse = $this->rawResponse;
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
     * Save the response headers into the provided variable
     *
     * @param $responseHeaders
     * @return $this
     */
    public function saveResponseHeaders(&$responseHeaders)
    {
        $responseHeaders = $this->responseHeaders;
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
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = [];
        return $this->addHeaders($headers);
    }

    /**
     *
     * Set whether `POST data` is multipart/form-data
     * @param bool $multipart
     * @return $this
     */
    public function setMultipart($multipart = true)
    {
        $this->isMultipart = (boolean) $multipart;
        return $this;
    }

    /**
     *
     * Set the response handler
     *
     * @param null|string|ResponseHandler $responseHandler
     * @return $this
     * @throws DiplomaticException
     */
    public function setResponseHandler($responseHandler = null)
    {
        if (is_string($responseHandler)) {
            $responseHandler = new $responseHandler;
        } elseif (is_null($responseHandler)) {
            $responseHandler = new BasicHandler();
        }

        if (is_object($responseHandler) && ! $responseHandler instanceof ResponseHandler) {
            throw new DiplomaticException('Invalid response handler. Expected extension of ForsakenThreads\Diplomatic\ResponseHandler. Received: ' . get_class($responseHandler));
        }

        $this->responseHandler = $responseHandler;
        return $this;
    }

    /**
     *
     * Set the user agent string for all requests
     *
     * @param $userAgent
     *
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = @(string) $userAgent;
        return $this;
    }

    /**
     *
     * A `TRACE` request
     *
     * @param string $page
     * @param array|string $data
     * @param array $files
     *
     * @return $this|mixed
     */
    public function trace($page, $data = [], array $files = [])
    {
        return $this->send($page, $data, '-X TRACE', $files);
    }

    /**
     *
     * Convert array to CLI cURL multipart/form-data format
     *
     * @param $data
     * @param bool $array_field
     *
     * @return string
     */
    protected function convertDataToMultipart($data, $array_field = false)
    {
        $multipartData = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $multipartData .= $this->convertDataToMultipart($value, !$array_field ? $key : $array_field . '[' . $key . ']');
            } elseif (is_a($value, CURLFile::class)) {
                /** @var CURLFile $value */
                $multipartData .= ' -F ' . escapeshellarg((!$array_field ? $key : $array_field . '[' . $key . ']') . '=@"' . addcslashes($value->getFilename(), '"\\') . '";type=' . $value->getMimeType() . ';filename="' . addcslashes($value->getPostFilename(), '"\\') . '"');
            } else {
                $multipartData .= ' -F ' . escapeshellarg((!$array_field ? $key : $array_field . '[' . $key . ']') . '=' . $value);
            }
        }
        return $multipartData;
    }

    /**
     *
     * Flattens nested arrays to a single depth array
     *
     * @param $data
     * @param bool $baseKey
     *
     * @return array
     */
    protected function flattenDataToMultipart($data, $baseKey = false)
    {
        $multipartData = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $useKey = $baseKey
                    ? $baseKey . '[' . $key . ']'
                    : $key;
                $multipartData = array_merge($multipartData, $this->flattenDataToMultipart($value, $useKey));
            } else {
                $useKey = $baseKey
                    ? $baseKey . '[' . $key . ']'
                    : $key;
                $multipartData[$useKey] = $value;
            }
        }
        return $multipartData;
    }

    /**
     *
     * Instantiate a CURLFile based on parameters provided
     *
     * Accepts the following:
     *
     * string: path to file
     *
     * object: SplFileInfo or SplFileObject
     *
     * @param mixed $fileInfo
     * @param string $mimeType
     * @param null $postName
     *
     * @return CURLFile
     */
    protected function getCurlFile($fileInfo, $mimeType = 'application/octet-stream', $postName = null)
    {
        if (is_string($fileInfo)) {
            return new CURLFile($fileInfo, $mimeType != null ? $mimeType : 'application/octet-stream', $postName != null ? $postName : $fileInfo);
        }

        if (is_a($fileInfo, SplFileInfo::class)) {
            /** @var SplFileInfo $fileInfo */
            return new CURLFile($fileInfo->getPathname(), $mimeType != null ? $mimeType : 'application/octet-stream', $postName != null ? $postName : $fileInfo->getFilename());
        }

        // this will cause an error, but diplomatically not throw an exception.
        return new CURLFile('');
    }

    /**
     *
     * Convert files to CURLFiles
     *
     * @param array $fileData
     *
     * @return array
     */
    protected function processFiles(array $fileData)
    {
        $processed = [];
        foreach ($fileData as $name => $fileInfo) {
            if (is_array($fileInfo)) {
                $processed[$name] = $this->getCurlFile($fileInfo[0], $fileInfo[1], isset($fileInfo[2]) ? $fileInfo[2] : null);
                continue;
            }
            $processed[$name] = $this->getCurlFile($fileInfo);
        }
        return $processed;
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
            // We care only if not a 100 Continue
            if (strpos($headerString, '100 Continue') === false) {
                $this->responseHttpVersion = trim($headerString);
            }
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
     */
    protected function resetHandlers()
    {
        // if required reset the handlers
        if ($this->resetHandlers) {
            $this->onAnyHandler = null;
            $this->onErrorHandler = null;
            $this->onFailureHandler = null;
            $this->onSuccessHandler = null;
        }
    }

    /**
     *
     * Send the request
     *
     * @param string $page
     * @param array|string $data
     * @param string $method
     * @param array $files
     *
     * @return $this|mixed
     */
    protected function send($page, $data = [], $method = '-G', $files = [])
    {
        // initialize the request body. either it was supplied as data, or it's empty
        if (!is_string($data)) {
            $body = $this->body;
        } else {
            $body = $data;
            $data = [];
        }

        if (!empty($files)) {
            $files = $this->processFiles($files);
            if (!empty($files)) {
                $this->setMultipart();
                $data = array_merge($files, $data);
            }
        }

        // initialize our cliCall. the -s option will silence progress. the -S will display an error on failure
        // the -w option adds the Http response code to the output
        // the -A option set the user agent string
        $cliCall = 'curl -Ssw "%{http_code}" ' . ($method == '-G' && !empty($body) ? '-X GET' : $method) . ' -A ' . escapeshellarg($this->userAgent);

        if (!empty($data)) {
            // if method is `GET` or this is a multipart request, we need to urlencode `$data`
            if ($method == '-G' || !$this->isMultipart) {
                // format data as application/x-www-form-urlencoded
                $data = http_build_query($data);

                // with method `GET`, the `$data` gets appended to the query string which will happen later
                // if not `GET` however, we need to add the `$data` for non-multipart requests
                if ($method != '-G') {
                    $cliCall .= ' -d ' . escapeshellarg($data);
                }
            // if this is not a `GET` request and it is multipart, we need to add the data to the cliCall
            } else {
                $cliCall .= $this->convertDataToMultipart($data);
            }
        }

        // we need to send the body
        if (!empty($body)) {
            $cliCall.= ' -d ' . escapeshellarg($body);
        }

        // the -D option outputs headers to a file, with - , in this case, being standard out
        // if this is a `HEAD` request, though, it's not needed, otherwise the -I option would cause a double output of headers
        if ($method != '-I') {
            $cliCall .= ' -D -';
        }

        // setup the headers for curl and the cliCall
        $headers = [];
        foreach ($this->headers as $header => $value) {
            $cliCall .= ' -H "' . $header . ': ' . addslashes($value) . '"';
            $headers[] = $header . ': ' . $value;
        }

        $curl = curl_init($this->destination . $page . ($method == '-G' && !empty($data) ? '?' . $data : ''));

        if ($this->isInsecure) {
            $cliCall .= ' -k ';
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        } else {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        }

        // add the page to the destination, and if this is a GET request, the data as a query string
        $cliCall .= ' "' . $this->destination . $page . ($method == '-G' && !empty($data) ? '?' . $data : '')  . '"';

        // reset for the new response coming up
        $this->rawResponse = null;
        $this->responseHttpVersion = '';
        $this->responseHeaders = [];

        // basic curl setup stuff
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, [$this, 'recordResponseHeaders']);

        // We need to add the data to the request
        if ($method != '-G' && $method != '-I') {
            // if non-empty and an array, then `$data` needs to be flattened to a single dimensional array
            if (!empty($data) && is_array($data)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $this->flattenDataToMultipart($data));
            // if `$data` is non-empty string, we just add it
            } elseif (!empty($data)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            // otherwise, if `$body` is non-empty, we add it
            } elseif (!empty($body)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            }
        } elseif ($method == '-G' && !empty($body)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        // set the proper request method
        switch ($method) {
            case '-I':
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
            case '-X PATCH':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
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
        $this->rawResponse = curl_exec($curl);

        // if errored, we set the rawResponse to the curl error
        if (curl_errno($curl)) {
            $this->rawResponse = curl_error($curl);
        }

        // save the info from curl
        $curlInfo = curl_getinfo($curl);

        // close the curl session
        curl_close($curl);

        // set cliCall and code in case the Developer wants to save them as part of a chained method call
        $this->cliCall = $cliCall;
        $this->code = $curlInfo['http_code'];

        // reset the request body
        $this->body = '';

        // no response handler provided, so we bail early
        if (empty($this->responseHandler)) {
            return $this;
        }

        // initialize the response handler with the basics
        $this->responseHandler->initialize($this->rawResponse, $this->responseHttpVersion, $this->responseHeaders, $curlInfo['http_code'], $curlInfo, $cliCall);

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
            $result = is_callable($this->onErrorHandler)
                ? call_user_func_array($this->onErrorHandler, Helpers::array_enqueue($this->onErrorHandlerExtraArgs, $this->responseHandler))
                : $this->onErrorHandler;

            $this->resetHandlers();
            return $result;
        }

        // if failed, check for onFailure handler
        if ($this->onFailureHandler !== null && $this->responseHandler->wasFailed()) {
            $result = is_callable($this->onFailureHandler)
                ? call_user_func_array($this->onFailureHandler, Helpers::array_enqueue($this->onFailureHandlerExtraArgs, $this->responseHandler))
                : $this->onFailureHandler;

            $this->resetHandlers();
            return $result;
        }

        // if successful, check for onSuccess handler
        if ($this->onSuccessHandler !== null && $this->responseHandler->wasSuccessful()) {
            $result = is_callable($this->onSuccessHandler)
                ? call_user_func_array($this->onSuccessHandler, Helpers::array_enqueue($this->onSuccessHandlerExtraArgs, $this->responseHandler))
                : $this->onSuccessHandler;

            $this->resetHandlers();
            return $result;
        }

        // check for onAny handler as a last fallback
        if ($this->onAnyHandler !== null) {
            $result = is_callable($this->onAnyHandler)
                ? call_user_func_array($this->onAnyHandler, Helpers::array_enqueue($this->onAnyHandlerExtraArgs, $this->responseHandler))
                : $this->onAnyHandler;

            $this->resetHandlers();
            return $result;
        }

        return $this;
    }

}