<?php namespace ForsakenThreads\Sip;

abstract class Mixers extends Drink {

    /**
     *
     * Common Mixer to parse a Drink as JSON
     *
     * @param bool $asArray
     */
    public function jsonOnTheRocks($asArray = true)
    {
        $isJson = (boolean) ( is_string($this->rawDrink) && json_decode($this->rawDrink));
        if ($isJson) {
            $this->mixedDrink = json_decode($this->rawDrink, $asArray);
        } else {
            $this->mixedDrink = false;
        }
    }

}