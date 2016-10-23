<?php namespace ForsakenThreads\Diplomatic\Support;

class Helpers {

    /**
     *
     * Add a variable number of elements to the front of an array
     *
     * @param array $array
     *
     * @return array
     */
    static public function array_enqueue(array $array)
    {
        // Grab all of the arguments
        $args = func_get_args();

        // The working array should be the first argument
        $array = array_shift($args);

        // add each element to the beginning of the working array, preserving order
        while ($arg = array_shift($args)) {
            array_unshift($array, $arg);
        }

        // return the working array
        return $array;
    }

    /**
     *
     * Determine if the provided argument is properly encoded json
     *
     * @param $string
     *
     * @return bool
     */
    static public function is_json($string)
    {
        return (boolean) (is_string($string) && json_decode($string) !== null);
    }


}