<?php namespace ForsakenThreads\Diplomatic;

use CURLFile;
use ForsakenThreads\Diplomatic\Support\DiplomaticException;
use ForsakenThreads\Diplomatic\Support\Helpers;
use SplFileInfo;

class Client {

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
     * A DELETE request
     *
     * @param string $page
     * @param array $data
     *
     * @return $this|Client|mixed
     */
    public function delete($page, array $data = [])
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
    public function get($page, array $data = [])
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

    public function insecure($isInsecure = true)
    {
        $this->isInsecure = (boolean) $isInsecure;
        return $this;
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
    public function patch($page, array $data = [], array $files = [])
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
    public function post($page, array $data = [], array $files = [])
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
    public function put($page, array $data = [], array $files = [])
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
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = [];
        return $this->addHeaders($headers);
    }

    /**
     *
     * Set whether POST data is multipart/form-data
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
     * A TRACE request
     *
     * @param string $page
     * @param array $data
     * @param array $files
     *
     * @return $this
     */
    public function trace($page, array $data = [], array $files = [])
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
     * Instantiate a CURLFile based on parameters provided. Accepts the following:
     * string: path to file
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
            $this->responseHttpVersion = trim($headerString);
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
        if (!empty($files)) {
            $files = $this->processFiles($files);
            if (!empty($files)) {
                $this->setMultipart();
                $data = array_merge($files, $data);
            }
        }

        if ($method == '-G' || !$this->isMultipart) {
            // format data as application/x-www-form-urlencoded
            $data = http_build_query($data);
        }

        // initialize our cliCall. the -s option will silence progress. the -S will display an error on failure
        // the -w option adds the Http response code to the output
        // the -A option set the user agent string
        $cliCall = 'curl -Ssw "%{http_code}" ' . $method . ' -A ' . escapeshellarg($this->userAgent);

        // the -D option outputs headers to a file, with - , in this case, being standard out
        // if this is a HEAD request, though, it's not needed, otherwise the -I option would cause a double output of headers
        if ($method != '-I') {
            $cliCall .= ' -D -';
        }

        // setup the headers for curl and the cliCall
        $headers = [];
        foreach ($this->headers as $header => $value) {
            $cliCall .= ' -H "' . $header . ': ' . addslashes($value) . '"';
            $headers[] = $header . ': ' . $value;
        }

        // if this is not a GET request, we need to add the data to the cliCall
        if ($method != '-G' && !empty($data)) {
            // application/x-www-form-urlencoded can be done like this
            if (!$this->isMultipart) {
                $cliCall .= ' -d "' . $data . '"';
            // multipart-form data like so
            } else {
                $cliCall .= $this->convertDataToMultipart($data);
            }
        }


        $curl = curl_init($this->destination . $page . ($method == '-G' ? '?' . $data : ''));

        if ($this->isInsecure) {
            $cliCall .= ' -k ';
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        } else {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        }

        // add the page to the destination, and if this is a GET request, the data as a query string
        $cliCall .= ' "' . $this->destination . $page . ($method == '-G' ? '?' . $data : '')  . '"';

        // reset for the new response coming up
        $this->responseHttpVersion = '';
        $this->responseHeaders = [];

        // TODO: ssl and security type stuff

        // basic curl setup stuff
        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, [$this, 'recordResponseHeaders']);
        if ($method != '-G' && $method != '-I') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($data) ? $this->flattenDataToMultipart($data) : $data);
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