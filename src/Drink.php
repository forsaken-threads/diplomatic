<?php namespace ForsakenThreads\Sip;

abstract class Drink {

    // An equivalent of the Sip as a CLI curl call
    protected $cliCall;

    // Http Status Code for the Drink
    protected $code;

    // The response after it has been mixed with the registered Mixers
    protected $mixedDrink;

    // An array of Mixers (callbacks) that will be applied right after the filters are applied
    protected $mixers;

    // Result of curl_info() for the Sip
    protected $nutritionalInfo;

    // Raw response from the Sip
    protected $rawDrink;

    /**
     *
     * Returns an arbitrary key from the Drink
     *
     * @param string $key
     * @param boolean $throwException
     * @param mixed $default
     *
     * @return mixed
     */
    abstract function getKey($key, $throwException = false, $default = null);


    /**
     *
     * Check to see if the Sip was errored
     *
     * @return boolean
     */
    abstract function wasErrored();

    /**
     * Check to see if the Sip failed
     *
     * @return boolean
     */
    abstract function wasFailed();

    /**
     *
     * Check to see if the Sip was successful (true) or failed/errored (false)
     *
     * @return boolean
     */
    abstract function wasSuccessful();

    /**
     *
     * Returns a CLI curl version of the Sip
     *
     * @return string
     */
    function getRecipe()
    {
        return $this->cliCall;
    }

    /**
     *
     * Http Status Code for the Drink
     *
     * @return mixed
     */
    function getCode()
    {
        return $this->code;
    }

    /**
     *
     * Provide access to the nutritionalInfo property
     *
     * @return NutritionalInfo
     */
    public function info()
    {
        return $this->nutritionalInfo;
    }

    /**
     *
     * Called by Sip to initialize the Drink
     *
     * @param string $rawDrink
     * @param integer $code
     * @param array $nutritionalInfo
     * @param string $cliCall
     */
    public function initializeDrink($rawDrink, $code, $nutritionalInfo, $cliCall)
    {
        $this->rawDrink = $rawDrink;
        $this->code = $code;
        $this->nutritionalInfo = new NutritionalInfo($nutritionalInfo);
        $this->cliCall = $cliCall;
        foreach ($this->mixers as $mixer) {
            $mix = \Closure::bind(is_array($mixer[0]) ? , $this);
            call_user_func_array($mix, $mixer[1]);
        }
    }

    /**
     *
     * Register a Drink Mixer callback.  These are called after the Drink is poured from the Sip result
     * The callback will be bound to the Drink
     * Optional extra arguments may be passed that will be forwarded to the Mixer when invoked
     *
     * @param callable $mixer
     *
     * @return $this
     */
    public function mix(callable $mixer)
    {
        $args = func_get_args();
        $mixer = array_shift($args);
        $this->mixers[] = [$mixer, $args];
        return $this;
    }

}