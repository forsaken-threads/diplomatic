<?php namespace ForsakenThreads\Diplomatic\Support;

class CallableArgumentsPair {

    /** @var callable */
    protected $callable;

    /** @var array */
    protected $arguments;

    public function __construct($callable, $arguments = [])
    {
        $this->callable = $callable;
        $this->arguments = $arguments;
    }

    /**
     *
     * Get the arguments for the callable
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     *
     * Get the callable
     *
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

}