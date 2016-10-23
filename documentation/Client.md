
## The **Diplomatic** *Client*

### Setup

Instantiate a *Client* by calling the constructor and supplying a `destination` and an instance of a class that extends `ResponseHandler`.  The `destination` must be, at a minimum, a domain name.  If you do not provide a scheme, it will default to using `https://`.  Once instantiated, you can add custom headers to every request the *Client* makes by calling `addHeaders` and providing an associative array of header type/values.  We will continue with the Markit On Demand examples started in the *Response Handler* section. 

```
<?php

use ForsakenThreads\Diplomatic\Client;

$client = new Client('http://dev.markitondemand.com/MODApis/Api/v2/', new MarkitOnDemandHandler);

$client->addHeaders([
    'Accept' => 'application/json',
    'Pragma' => 'no-cache',
]);
```

### Basic Usage

The simplest way to use the *Client* is to run the request and save the response handler using the `saveResponseHandler()` chained method call.  In a similar fashion, you can save the HTTP response code by method chaining `saveResponseCode()`.  This may seem a bit odd, but roll with it.  It should become clear later why this is the **Diplomatic** way.  So, first you call the particular HTTP method that you want to use - `get`, `post`, `put`, `patch`, `delete`, `head`, `options`, `trace`, or `connect` - and then you chain on the `save` methods you want to use.  **An important note.  These cannot be combined with the `on` methods noted below.  You can only chain `on` handlers *before* the HTTP method, or `save` methods *after* the HTTP method, not both.**

```
<?php

use ForsakenThreads\Diplomatic\Client;

$client = new Client('http://dev.markitondemand.com/MODApis/Api/v2/', new MarkitOnDemandHandler);

$client->get('Quote/json', ['symbol' => 'NDAQ'])->saveResponseHandler($handler)->saveResponseCode($code);

// now we can use $handler to do some stuff with the response
if ($handler->wasSuccessful()) {
    return $handler->getFilteredResponse()['LastPrice'];
}

// something went wrong
throw new AcmeException("API Call failed with response code: $code");
```

The **Diplomatic** way is to attempt to be discrete at all costs.  It should almost never throw an exception.  The *Client* safely tucks away all of the ugliness of handling the request and leaves any exception throwing for you to handle, in your own way, in your own time.  You extend the `ResponseHandler` class, inject it into the *Client*, and the *Client* will silently collect all of the information it can about the response and pass it on to your handler.  What you do with it from there is up to you.

### Intermediate Usage

What if you just wanted to do something very basic depending on the response result, i.e. errored, failed, or successful.  Then the `on` handlers are for you.  Chain them in your *Client* invocation before the HTTP method call and supply them with a `callable` function.  The *Client* will trigger the appropriate callback and pass it a copy of the *Response Handler*.  The whole method chain will then return the value of that callback. **An important note.  These cannot be combined with the `save` methods noted above.  You can only chain `on` handlers *before* the HTTP method, or `save` methods *after* the HTTP method, not both.**

```
<?php

use ForsakenThreads\Diplomatic\Client;

$client = new Client('http://dev.markitondemand.com/MODApis/Api/v2/', new MarkitOnDemandHandler);

// this will return the result of whichever callback is appropriate based on the response result
// the Client calls the handler's wasErrored(), wasFailed(), and wasSuccessful() methods 
// to determine which callback to use
return $client->onError(function($handler) {
        return [$handler->getCode(), $this->getRawResponse()]; 
    })->onFailure(function($handler) { 
        return [$handler->getCode(), $handler->getFilteredResponse()['Message']]; 
    })->onSuccess(function($handler) { 
        return [$handler->getCode(), $handler->getFilteredResponse()['LastPrice']];
    })->get('Quote/json', ['symbol' => 'NDAQ']);
```

You can don't have to supply a callback if you don't want to.  You can provide anything you want except for `null`.  If it's not a `callable`, it will simply be returned as is.

```
<?php

use ForsakenThreads\Diplomatic\Client;

$client = new Client('http://dev.markitondemand.com/MODApis/Api/v2/', new MarkitOnDemandHandler);

// not a very useful example in this particular case, but the ability might come in handy
return $client->onError('Error')
    ->onFailure('Failure')
    ->onSuccess('Success')
    ->get('Quote/json', ['symbol' => 'NDAQ']);
```

There is also an `onAny` handler that will be called in the event that no appropriate `on` handler was registered.

```
<?php

use ForsakenThreads\Diplomatic\Client;

$client = new Client('http://dev.markitondemand.com/MODApis/Api/v2/', new MarkitOnDemandHandler);

// return Success on success, otherwise return the raw response.
return $client->onSuccess('Success')
    ->onAny(function ($handler) {
        return $handler->getRawResponse();
    })->get('Quote/json', ['symbol' => 'NDAQ']);
```

A couple of important notes.  One, the order of the method chaining does not matter, even when using `onAny`.  Two, you must use either all three `on` response result handlers, or the `onAny` handler and some combination of the three response result handlers.  If, for example, you only used `onSuccess`, and the response result was errored or failed, the return value of the chain would simply be the *Client* itself because the HTTP method call returns `$this` to allow for chaining the `save` methods after the response has been retrieved.

### Advanced Usage

Instead of chaining the `on` handlers like above, you could have your `ResponseHandler` extension class implement the `SelfHandling` interface.  This interface requires the implementation of three methods: `onError`, `onFailure`, and `onSuccess`.  The *Client* will detect the that your handler has implemented the interface and then call the appropriate method on the handler.  If you have an API process that is heavily redundant, this nifty feature might just be for you.

What if you wanted to string together a bunch of API calls at once?  With a **Diplomatic** *Client* you can.

```
<?php

use ForsakenThreads\Diplomatic\Client;

$client = new Client('http://dev.markitondemand.com/MODApis/Api/v2/', new MarkitOnDemandHandler);

$client->get('Quote/json', ['symbol' => 'NDAQ'])->saveResponseHandler($nasdaq)
    ->get('Quote/json', ['symbol' => 'CAKE'])->saveResponseHandler($cheesecakeFactory)
    ->get('Quote/json', ['symbol' => 'TXRH'])->saveResponseHandler($texasRoadhouse);
    
// do something spiffy with those three handlers
```

If you are stringing together calls like that, but you need to specify different HTTP request headers for each call, you can use the `setHeaders()` method to do so.  This would also be useful if you are re-using the *Client*.  Of course, re-using the *Client* brings up a set of problems.  If you need to change the *Response Handler* or the `destination`, you can do that with setters, `setResponseHandler()` and `setDestination()` (method chainable, naturally).  By default, the `on` handlers are reset after each request, but if you didn't want them to reset, you can invoke `resetHandlersAfterRequest()` with `false` as the argument and they will be preserved from call-to-call.
  
```
<?php

use ForsakenThreads\Diplomatic\Client;

$client = new Client('http://dev.markitondemand.com/MODApis/Api/v2/', new MarkitOnDemandHandler);

$nasdaq = $client->resetHandlersAfterRequest(false)
    ->onSuccess('Success')
    ->onAny(function ($handler) {
        return $handler->getRawResponse();
    })->get('Quote/json', ['symbol' => 'NDAQ']);

// the onSuccess and onAny handlers are preserved for this call
$lookup = $client->setDestination('http://acme.api')
    ->setResponseHandler(new AcmeResponseHandler)
    ->get('/lookup', ['type' => 'TNT']);
```

* [Response Handlers](./documentation/ResponseHandler.md)