<?php namespace ForsakenThreads\Sip;

class MarkitOnDemand extends Mixers {

    public function __construct()
    {
        $this->mix([$this, 'jsonOnTheRocks'], false);
    }

    /**
     *
     * Returns an arbitrary key from the Drink
     *
     * @param string $key
     * @param boolean $throwException
     * @param mixed $default
     *
     * @return mixed
     *
     * @throws Backwash
     */
    function getKey($key, $throwException = false, $default = null)
    {
        $key = @(string) $key;
        if ($this->wasErrored() || !property_exists($this->mixedDrink, $key)) {
            if ($throwException) {
                throw new Backwash('Errored response. Cannot retrieve key: ' . $key);
            }
            return $default;
        }
        return $this->mixedDrink->$key;
    }

    /**
     *
     * Check to see if the Sip was errored
     *
     * @return boolean
     */
    function wasErrored()
    {
        return !$this->mixedDrink;
    }

    /**
     * Check to see if the Sip failed
     *
     * @return boolean
     */
    function wasFailed()
    {
        return !$this->wasErrored() && $this->getKey('Status') != 'SUCCESS';
    }

    /**
     *
     * Check to see if the Sip was successful (true) or failed/errored (false)
     *
     * @return boolean
     */
    function wasSuccessful()
    {
        return !$this->wasFailed();
    }
}