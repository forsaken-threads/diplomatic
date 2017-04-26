# Diplomatic

### A diplomatic cURL wrapper for API calls

This is a practical, yet sophisticated, HTTP client for making API calls and parsing their responses.  Some of the basic features are *Response Handler* dependency injection, callback registration for the three basic response results - errored, failed, and successful - and the ability to print out a CLI version of the underlying cURL call for the ultimate in hands on debugging.

The **Diplomatic** way is to attempt to be discrete at all costs. It should almost never throw an exception. The *Client* safely tucks away all of the ugliness of handling the request and leaves any `Exception` throwing for you to handle, in your own way, in your own time. You extend the `ResponseHandler` class, inject it into the *Client*, and the *Client* will silently collect all of the information it can about the response and pass it on to your handler. What you do with it from there is up to you.
     
### Installation

`composer require forsaken-threads/diplomatic`

### Basic Usage

The simplest usage relies on a default `ResponseHandler` that determines if a request was successful by looking at the HTTP status code of the response: a 2XX code is successful, a 5XX code is errored, and anything else is failed.  Check it out [here](./src/BasicHandler.php).

```
<?php

use ForsakenThreads\Diplomatic\Client;

$client = new Client('http://api.example.com');

// a simple get, and we don't care about the response
$client->get('/endpoint/url');

// a simple get, and we just want the raw response text
$client-get('/endpoint/url')->saveRawResponse($response);

echo $response;

// a simple post, and we'll save the response handler to inspect
$client->post('/endpoint/url', ['id' => 123])
    ->saveResponseHandler($handler);

if ($handler->wasSuccessful()) {
    echo "such API, much success";
} else {
    echo "bad juju";
}

// now for some JSON requests
$client->addHeaders(['Content-type' => 'text/json']);

// make two posts and save each handler
$client->post('/endpoint/url?json', json_encode(['id' => 123])->saveResponseHandler($handler1)
    ->post('/endpoint/url?json', json_encode(['id' => 456])->saveResponseHandler($handler2);
    
if ($handler1->wasSuccessful() && $handler2->wasSuccessful()) {
    echo "that's a two-fer!";
} else {
    echo "no go";
}
```

# Advanced Usage

Handling the request is the easy part, so instructions for the *Client* are secondary on this list.  First, we will take a look at the hard part of the request/response pair.  The **Diplomatic** way to handle the response is by injecting an extension of the abstract `ResponseHandler` class into the *Client*.  Read on for more information.

* [Response Handlers](./documentation/ResponseHandler.md)
* [**Diplomatic** Client](./documentation/Client.md)
* [Appendix](./documentation/Appendix.md)

Of course, if you really don't care about the response, you can also leave out the `ResponseHandler` implementation and just start sending requests.  See the *Client* docs for details.

### Still To Do

* Support curl_multi
* Develop usable example *Response Handlers*
* Allow custom HTTP methods

### Sami Documentation

Check out [diplomatic.forsaken-threads.com](http://diplomatic.forsaken-threads.com) for the lowdown.