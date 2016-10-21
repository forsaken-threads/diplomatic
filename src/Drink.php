<?php namespace ForsakenThreads\Sip;

abstract class Drink {

    // An equivalent of the Sip as a CLI curl call
    protected $cliCall;

    // Http Status Code for the Drink
    protected $code;

    // Result of curl_info() for the Sip
    protected $nutritionalInfo;

    // Raw response from the Sip
    protected $rawDrink;

    /**
     *
     * Returns a CLI curl version of the Sip
     *
     * @return string
     */
    abstract function getRecipe();

    /**
     *
     * Http Status Code for the Drink
     *
     * @return mixed
     */
    abstract function getCode();

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
     * Called after initialization so the Developer can do any necessary setup
     *
     * @return mixed
     */
    abstract function mixDrink();

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
    }
}