<?php namespace ForsakenThreads\Diplomatic\Support;

class BasicFilters {

    /**
     *
     * Filter that checks for valid json and decodes it as directed
     *
     * @param string $response
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     *
     * @return mixed
     */
    static public function json($response, $assoc = false, $depth = 512, $options = 0)
    {
        if (Helpers::is_json($response)) {
            return json_decode($response, $assoc, $depth, $options);
        }
        return $response;
    }

    /**
     *
     * Filter that checks for valid XML and loads it as directed
     *
     * @param string $response
     * @param string $className
     * @param int $options
     * @param string $ns
     * @param bool $isPrefix
     *
     * @return \SimpleXMLElement
     */
    static public function simpleXml($response, $className = 'SimpleXMLElement', $options = 0, $ns = '', $isPrefix = false)
    {
        $xml = @simplexml_load_string($response, $className, $options, $ns, $isPrefix);
        if ($xml !== false) {
            return $xml;
        }
        return $response;
    }

}