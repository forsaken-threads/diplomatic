## Response Handlers

### Implementing the abstract methods

When creating a `Client` instance, you can an inject an extension of the abstract `ResponseHandler` class.  This class has three abstract methods that require implementation and are essentially a nudge to reasonably consider interaction with the response.  These methods are checks for the three basic response results - errored, failed, and successful.

In general, an errored response is one that results from a network condition, remote server error, or request dysfunction and causes the response format to be unparseable in the normal fashion.  A failed response and a successful response are ones that can be predictably parsed and contain an encoded result status that indicates either failure or success.

If no `ResponseHandler` is provided to the `Client`, it will automatically use the `BasicHandler`.  This handler uses the HTTP status code of the response to determine errored, failed, and successful.  A 2XX code is successful, a 5XX code is errored, and anything else is failed.  This may meet the simplest of needs.

**Diplomatic** also provides three simple handlers that will process the raw response in addition to the HTTP status code determination.  These do pretty much what you would expect. `SimpleJsonArrayHandler` processes a JSON raw response into an associative array.  `SimpleJsonObjectHandler` processes a JSON raw response into a `stdClass` object (unless, of course, the JSON represents an array, in which case you'll get an array).  `SimpleXmlHandler` processes an XML raw response into a `SimpleXMLElement` object.

But what about situations where the HTTP status code isn't always meaningful?  Consider [Markit On Demand's Market Data APIs](http://dev.markitondemand.com/MODApis/).  Their APIs return a serialized `Error` object when a request fails, and it contains a single key, `Message`.  Otherwise, the response will contain the data that was requested, in the appropriate format (XML, JSON, or JSONP).  A malformed response (invalid XML, JSON, or JSONP) should only ever occur if something like a network or server error happened or the request itself was malformed.  Below is one way to implement the abstract methods for a `MarkitOnDemand` *Response Handler* extension (the full example can be found [here](../examples/MarkitOnDemandHandler.php)).

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

The `ResponseHandler` class holds the raw response text as well as a filtered response.  When the `Client` initializes the handler, the raw response and the filtered response are the same.  Each filter registered with the handler will then be invoked and passed, at a minimum, the filtered response.  The filter should return either the response manipulated in some way or the unchanged response.  If you are using an API that mixes response formats, you can then chain filters together, checking the format each time to ensure that only the proper filter is applied.

Two common response formats are JSON and XML.  The `ResponseHandler` class has three properties that can be overridden to automatically handle these formats.  To convert JSON to an associative array, set `$filtersJsonArray` to `true` in your handler class.  To convert JSON to a `stdClass` object, set `$filtersJsonObject` to `true`.  Finally, if you want to convert XML to a `SimpleXMLElement` object, set `$filtersXml` to `true`.  (By default, these are all set to `false`.)  These three filters will be applied **before** any other custom filter callbacks, and because they only act on valid XML or JSON, each kind can be safely set to run.  Note that the JSON array conversion occurs first, so activating both object and array types will always lead to array conversion.

To register a filter callback, use the `filter()` method on the handler.  This method accepts a `callable` as the first parameter.  Any other parameters will be passed to the `callable`.  Here's an example handler that will first apply the JSON array filter, and then two custom filters.

```
<?php

use ForsakenThreads\Diplomatic\ResponseHandler;

class ExampleHandler extends ResponseHandler
{
    protected $filtersJsonArray = true;
    
    public function __construct(array $sensitiveKeys)
    {
        // the filter always receives the current value of the filteredResponse as the first argument
        $this->filter(function ($filteredResponse) {
            // in here make sure the response is in the proper format, and if not, return it unchanged
            if (!is_array($filteredResponse) || !key_exists('csv_report', $filteredResponse)) {
                return $filteredResponse;
            }
            
            $filteredResponse['csv_report'] = $this->parseCsvReport($filteredResponse['csv_report']));
            
            return $filteredResponse;
        }
        
        // $sensitiveKeys will be passed to hideSensitiveData as the second argument
        $this->filter([$this, 'hideSensitiveData'], $sensitiveKeys);
    }
    
    protected function hideSensitiveData($filteredResponse, $sensitiveKeys)
    {
        if (!is_array($filteredResponse)) {
            return $filteredResponse;
        }
        
        foreach ($sensitiveKeys as $key) {
            unset($filteredResponse[$key]);
        }
        
        return $filteredResponse;
    }
}
```

These filters can be registered in the constructor for the handler, but they can also be added to an instance of the handler.  Say, for instance, you have a number of API calls that are fairly simple, but one that is very different and requires some complex processing.  For the easy ones, you can instantiate the **Diplomatic** client with just the class name of your handler.  The client will instantiate the handler itself.  For the complex call, you could instantiate the handler yourself, register the complex filter, and then construct the client with your modified instance of the handler.  

If you have a long chain of filters and you'd like to break out of the invocation loop, you can throw an `Interrupt` exception.  This will stop all future filters from being applied.

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

### Other Stuff

The raw response, and the filtered response are stored in the `rawResponse` and `filteredResponse` properties of the `ResponseHandler`.  They have standard getters to retrieve them, `getRawResponse()` and `getFilteredResponse()`.

The HTTP headers, version, and status code for the response are stored in the `headers`, `htmlVersion`, and `code` properties of the `ResponseHandler`.  The headers are stored as an associative array where the keys are the header type and the values are the header value.  They all have standard getters to retrieve them, `getHeaders()`, `getHtmlVersion()`, and `getCode()`.

The *Response Handler* abstract class also contains two other useful properties, `cliCall` and `curlInfo`. A string that represents a CLI version of the underlying cURL call can be found in `cliCall`. This might come in handy in weird debugging situations.  This one has a standard getter, `getCliCall()`.  The other one, `curlInfo`, contains a `CurlInfo` object that is a wrapper around the information returned by the method `curl_getinfo()`.  The `CurlInfo` object has standard getters for all of the keys in the associative array returned by `curl_getinfo()`.  To access them you must use the `curlInfo` getter `info()`.

```
<?php

$sizeUpload = $handler->info()->getSizeUpload();
$contentType = $handler->info()->getContentType();
```

* [**Diplomatic** Client](./Client.md)
* [Appendix](./Appendix.md)
