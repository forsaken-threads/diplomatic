<?php namespace ForsakenThreads\Sip;

class Sip {

    static protected $creators = [];

    // An equivalent of the Sip as a CLI curl call
    protected $cliCall;

    // Http response code of the Drink
    protected $code;

    // The schema, host, port, and/or path for the Sip
    protected $glass;

    // The Drink returned by the Sip
    protected $drink;

    // Callback handlers and extra arguments for the various Drink results
    protected $onAnyHandler;
    protected $onAnyHandlerExtraArgs;

    protected $onErrorHandler;
    protected $onErrorHandlerExtraArgs;

    protected $onFailureHandler;
    protected $onFailureHandlerExtraArgs;

    protected $onSuccessHandlerExtraArgs;
    protected $onSuccessHandler;

    // Boolean that determines whether to reset all handlers after a Sip
    protected $resetHandlers = true;

    // headers for the Sip
    protected $straws = [];

    /**
     *
     * Register a callback that can be used to setup initial conditions for each new Sip instance
     *
     * @param callable $creator
     */
    public static function addCreator(callable $creator)
    {
        static::$creators[] = $creator;
    }

    /**
     *
     * Sip constructor
     *
     * @param $destination
     * @param Drink $drink
     * @param bool $useCreators
     *
     * @throws Backwash
     */
    public function __construct($destination, Drink $drink, $useCreators = true)
    {
        $parsed = parse_url($destination);
        if (empty($parsed['host'])) {
            throw new Backwash('Invalid argument.  Expected destination with a minimum of host and optionally a scheme, path and/or port.  Received: ' . @(string) $destination);
        }

        // Build up a proper Glass based on the given destination
        $this->glass = (empty($parsed['scheme']) ? 'https' : $parsed['scheme']) . '://';
        $this->glass .= $parsed['host'];
        $this->glass .= empty($parsed['path']) ? '' : $parsed['path'];
        $this->glass .= empty($parsed['port']) ? '' : (':' . $parsed['port']);

        $this->drink = $drink;

        if ($useCreators) {
            foreach (static::$creators as $creator) {
                $creator($this);
            }
        }
    }

    /**
     *
     * Add headers to the Sip
     *
     * @param array $headers
     *
     * @return $this
     */
    public function addHeaders(array $headers)
    {
        $this->straws = array_merge($this->straws, $headers);

        return $this;
    }

    /**
     *
     * Sip a 'delete' flavored Drink
     *
     * @param string $page
     * @param array $data
     *
     * @return $this|Sip|mixed
     *
     * @throws Backwash
     */
    public function delete($page, $data = [])
    {
        return $this->sip($page, $data, '-X DELETE');
    }

    /**
     *
     * Sip a 'get' flavored Drink
     *
     * @param string $page
     * @param array $data
     *
     * @return $this|Sip|mixed
     *
     * @throws Backwash
     */
    public function get($page, $data = [])
    {
        return $this->sip($page, $data);
    }

    /**
     *
     * Register a callback (or anything but `null`) for any Drink - supersedes all other handlers
     * The callback will receive the Drink as the first argument
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
     * Register a callback (or anything but `null`) for an errored Drink
     * The callback will receive the Drink as the first argument
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
     * Register a callback (or anything but `null`) for a failed Drink
     * The callback will receive the Drink as the first argument
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
     * Register a callback (or anything buty `null`) for a successful Drink
     * The callback will receive the Drink as the first argument
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
     * Register an onSuccess handler that simply returns the Drink
     *
     * @return $this
     */
    public function onSuccessReturnDrink()
    {
        return $this->onSuccess([$this, 'returnDrink']);
    }


    /**
     *
     * Sip a 'post' flavored Drink
     *
     * @param string $page
     * @param array $data
     * @param array $files
     *
     * @return $this
     *
     * @throws Backwash
     */
    public function post($page, $data = [], $files = [])
    {
        return $this->sip($page, $data, '-X POST', $files);
    }

    /**
     *
     * Sip a 'put' flavored Drink
     *
     * @param string $page
     * @param array $data
     * @param array $files
     *
     * @return $this
     *
     * @throws Backwash
     */
    public function put($page, $data = [], $files = [])
    {
        return $this->sip($page, $data, '-X PUT', $files);
    }

    /**
     *
     * Set the resetHandlers property
     *
     * @param $reset
     *
     * @return $this
     */
    public function resetHandlersAfterSip($reset)
    {
        $this->resetHandlers = @(boolean) $reset;
        return $this;
    }

    /**
     * @return Drink
     */
    public function returnDrink()
    {
        return $this->drink;
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
     * Save the Drink into the provided variable
     *
     * @param Drink $drink
     *
     * @return $this
     */
    public function saveResponse(&$drink)
    {
        $drink = $this->drink;
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
     * Add a variable number of elements to the front of an array
     *
     * @param array $array
     *
     * @return array
     */
    protected function array_enqueue(array $array)
    {
        // Grab all of the arguments
        $args = func_get_args();

        // The working array should be the first argument
        $array = array_shift($args);

        // add each element to the beginning of the working array, preserving order
        while ($arg = array_shift($args)) {
            array_unshift($array, $arg);
        }

        // return the working array with the
        return $array;
    }

    /**
     *
     * Grab the glass, put in our straws and ice, choose a flavor, and take a Sip
     *
     * @param string $page
     * @param array $ice
     * @param string $flavor
     * @param array $files
     *
     * @return $this|mixed
     *
     * @throws Backwash
     */
    protected function sip($page, $ice = [], $flavor = '-G', $files = [])
    {
        // make our ice cubes application/x-www-form-urlencoded
        $ice = http_build_query($ice);

        // initialize our cliCall. the -s options will silence. the -w option adds the http response code to the output
        $cliCall = 'curl -s -w "%{http_code}" ' . $flavor;

        // setup the straws for curl and the cliCall
        $straws = [];
        foreach ($this->straws as $straw => $value) {
            $cliCall .= ' -H "' . $straw . ': ' . $value . '"';
            $straws[] = $straw . ': ' . $value;
        }

        // if this is not a get, we need to add the ice to the cliCall
        if ($flavor != '-G') {
            $cliCall .= '-d "' . $ice . '"';
        }

        // add the page, and if this is a get the ice, to the glass
        $cliCall .= ' "' . $this->glass . $page . ($flavor == '-G' ? '?' . $ice : '')  . '"';
        $curl = curl_init($this->glass . $page . ($flavor == '-G' ? '?' . $ice : ''));

        // basic curl setup stuff
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $straws);
        if ($flavor != '-G') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $ice);
        }

        // set the proper flavor for our Sip
        switch ($flavor) {
            case '-G':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                break;
            case '-X DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case '-X POST':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                break;
            default:
                throw new Backwash('Invalid method. Received ' . Helpers::str_cast($flavor));
        }

        // take a Sip
        $rawDrink = curl_exec($curl);

        // if errored, we set the rawDrink to the error
        if (curl_errno($curl)) {
            $rawDrink = curl_error($curl);
        }

        // save the nutritional info result
        $nutritionalInfo = curl_getinfo($curl);

        // finish the Sip
        curl_close($curl);

        // set cliCall and code in case the Developer wants to save them as part of a chained method call
        $this->cliCall = $cliCall;
        $this->code = $nutritionalInfo['http_code'];

        // initialize the Drink with the basics
        $this->drink->initializeDrink($rawDrink, $this->code, $nutritionalInfo, $cliCall);

        // check for onAny handler
        if ($this->onAnyHandler !== null) {
            return is_callable($this->onAnyHandler)
                ? call_user_func_array($this->onErrorHandler, $this->array_enqueue($this->onAnyHandlerExtraArgs, $this->drink))
                : $this->onAnyHandler;
        }

        // if errored, check for onError handler
        if ($this->onErrorHandler !== null && $this->drink->wasErrored()) {
            return is_callable($this->onErrorHandler)
                ? call_user_func_array($this->onErrorHandler, $this->array_enqueue($this->onErrorHandlerExtraArgs, $this->drink))
                : $this->onErrorHandler;
        }

        // if failed, check for onFailure handler
        if ($this->onFailureHandler !== null && $this->drink->wasFailed()) {
            return is_callable($this->onFailureHandler)
                ? call_user_func_array($this->onFailureHandler, $this->array_enqueue($this->onFailureHandlerExtraArgs, $this->drink))
                : $this->onFailureHandler;
        }

        // if successful, check for onSuccess handler
        if ($this->onSuccessHandler !== null && $this->drink->wasSuccessful()) {
            return is_callable($this->onSuccessHandler)
                ? call_user_func_array($this->onSuccessHandler, $this->array_enqueue($this->onSuccessHandlerExtraArgs, $this->drink))
                : $this->onSuccessHandler;
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