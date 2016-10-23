<?php namespace ForsakenThreads\Diplomatic\Support;

use Closure;

class ClosureArgumentsPair {

    /** @var Closure */
    protected $closure;

    /** @var array */
    protected $arguments;

    public function __construct($closure, $arguments = [])
    {
        $this->closure = $closure;
        $this->arguments = $arguments;
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