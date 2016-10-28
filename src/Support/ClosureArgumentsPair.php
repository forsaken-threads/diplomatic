<?php namespace ForsakenThreads\Diplomatic\Support;

use Closure;

class ClosureArgumentsPair {

    /** @var Closure */
    protected $closure;

    /** @var array */
    protected $arguments;

    /**
     *
     * Accepts an array of arguments, normally the result of `func_get_args()`
     *
     * @param $func_args
         */
    public function __construct($func_args)
    {
        $closure = array_shift($func_args);
        $this->closure = $closure;
        $this->arguments = $func_args;
    }

    /**
     *
     * Get the arguments for the closure
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     *
     * Get the closure
     *
     * @return Closure
     */
    public function getClosure()
    {
        return $this->closure;
    }

}