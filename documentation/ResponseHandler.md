## Response Handlers

### Implementing the abstract methods

When creating a `Client` instance, you must an inject an extension of the abstract `ResponseHandler` class.  This class has three abstract methods that require implementation and are essentially a nudge to reasonably consider interaction with the response.  These methods are checks for the three basic response results - errored, failed, and successful.  In general, an errored response is one that results from a network condition, remote server error, or request dysfunction and causes the response format to be unparseable in the normal fashion.  A failed response and a successful response are ones that can be predictably parsed and contain an encoded result status that indicates either failure or success.

Consider [Markit On Demand's Market Data APIs](http://dev.markitondemand.com/MODApis/).  Their APIs return a serialized `Error` object when a request fails, and it contains a single key, `Message`.  Otherwise, the response will contain the data that was requested, in the appropriate format (XML, JSON, or JSONP).  A malformed response (invalid XML, JSON, or JSONP) should only ever occur if something like a network or server error happened or the request itself was malformed.  Below is one way to implement these methods for a `MarkitOnDemand` *Response Handler* extension (the full example can be found [here](./examples/MarkitOnDemand.php)).

```
<?php

use ForsakenThreads\Diplomatic\ResponseHandler;

class MarkitOnDemand extends ResponseHandler {

    ...

    function wasErrored()
    {
        // if the response failed to get parsed as JSON, the filtered response is the same as the raw response
        // if JSON decoding failed, something is seriously wrong, and we'll call that an errored response
        return $this->filteredResponse == $this->rawResponse;
    }

    function wasFailed()
    {
        // if this was errored, it is not failed.
        // if it wasn't errored, but the Message key doesn't exist, it is also not failed
        if ($this->wasErrored() || !key_exists('Message', $this->filteredResponse)) {
            return false;
        }
        
        // not errored and there is a Message key. that's a failure
        return true;
    }

    function wasSuccessful()
    {
        return !$this->wasErrored() && !$this->wasFailed();
    }
}
```

### Response filters

The `ResponseHandler` class holds the raw response text as well as a filtered response.  When the `Client` initializes the handler, the raw response and the filtered response are the same.  Each filter registered with the handler will then be invoked and passed, at a minimum, the filtered response.  The filter should return either the response manipulated in some way or the unchanged response.  If you are using an API that mixes response formats, you can then chain filters together, checking the format each time to ensure that only the proper filter is applied.  If you have a long chain of filters and you'd like to break out of the invocation loop, you can throw an `Interrupt` exception.

```
<?php

use ForsakenThreads\Diplomatic\Interrupt;

$handler->filter(function($filteredResponse)
{
    // if the response isn't an array yet or there are no widgets, then we can stop all future filters
    if (!is_array($filteredResponse) || empty($filteredResponse['widgets']) {
        throw new Interrupt();
    }
    
    // goodie! let's continue processing the response
    ...
});
```

If you simply want to skip a number of iterations in the loop (because filters are applied in the order they are registered), you can throw an `InterruptContinue` exception and provide the constructor with the number of iterations to skip.

```
<?php

use ForsakenThreads\Diplomatic\InterruptContinue;

$handler->filter(function($filteredResponse)
{
    // if we've made it this far, we know the $filteredResponse is an array
    // but if there are no sprockets, we can skip this filter and the next 3 as well
    if (empty($filteredResponse['sprockets']) {
        throw new InterruptContinue(3);
    }
    
    // goodie! let's continue processing the response
    ...
});
```

Filters are registered in the instantiated `ResponseHandler` by calling the public method `filter`.  This method accepts at least one argument, a `callable` that will receive the filtered response.  If you have extra arguments that you want the filter to receive at invocation, pass them to the `filter` method, and they will be appended to the argument list in the same order.  As an example you can look at how the two [basic filters](./src/Support/BasicFilters.php) supplied with **Diplomatic** work.  In most cases an API will return a response that's encoded in a specific way, often XML or JSON.  The `BasicFilters` class contains a filter for handling each of these cases.

```
<?php namespace ForsakenThreads\Diplomatic\Support;

class BasicFilters {

    static public function json($response, $assoc = false, $depth = 512, $options = 0)
    {
        if (!is_string($response)) {
            return $response;
        }

        $filteredResponse = json_decode($response, $assoc, $depth, $options);

        if (json_last_error() == JSON_ERROR_NONE) {
            return $filteredResponse;
        }

        return $response;
    }

    static public function simpleXml($response, $className = 'SimpleXMLElement', $options = 0, $ns = '', $isPrefix = false)
    {
        $xml = @simplexml_load_string($response, $className, $options, $ns, $isPrefix);
        if ($xml !== false) {
            return $xml;
        }
        return $response;
    }

}
```

These are really just wrappers around two built-in PHP functions.  If you want to use one of these filters to parse a response, register it with your *Response Handler* extension like this:

```
<?php

use ForsakenThreads\Diplomatic\Support\BasicFilters

// this registers the json filter and passes true as the $assoc argument
// to force the decoder to return an associative array rather than an object
$handler->filter([BasicFilters::class, 'json'], true);

// this registers the simpleXml filter and passes a custom class name to the second argument
$handler->filter([BasicFilters::class, 'simpleXml'], 'AcmeSimpleXml');
```

Because the filters only act on valid XML or JSON, they can both be safely registered on the *Response Handler*.

### Other Stuff

The HTTP headers, version, and status code for the response are stored in the `headers`, `htmlVersion`, and `code` properties of the `ResponseHandler`.  The headers are stored as an associative array where the keys are the header type and the values are the header value.  They all have standard getters to retrieve them, `getHeaders()` and `getHtmlVersion()`, and `getCode()`.

The *Response Handler* abstract class also contains two other useful properties, `cliCall` and `curlInfo`. A string that represents a CLI version of the underlying cURL call can be found in `cliCall`. This might come in handy in weird debugging situations.  This one has a standard getter, `getCliCall()`.  The other one, `curlInfo`, contains a `CurlInfo` object that is a wrapper around the information returned by the method `curl_getinfo()`.  The `CurlInfo` object has standard getters for all of the keys in the associative array returned by `curl_getinfo()`.  To access them you must use the `curlInfo` getter `info()`.

```
<?php

$sizeUpload = $handler->info()->getSizeUpload();
$contentType = $handler->info()->getContentType();
```

* [**Diplomatic** Client](./Client.md)