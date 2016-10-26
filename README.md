# Diplomatic

### A diplomatic cURL wrapper for PHP

This is a practical, yet sophisticated, HTTP client for making API calls and parsing their responses.  Some of the basic features are *Response Handler* dependency injection, callback registration for the three basic response results - errored, failed, and successful - and the ability to print out a CLI version of the underlying cURL call for the ultimate in hands on debugging.

The **Diplomatic** way is to attempt to be discrete at all costs. It should almost never throw an exception. The *Client* safely tucks away all of the ugliness of handling the request and leaves any `Exception` throwing for you to handle, in your own way, in your own time. You extend the `ResponseHandler` class, inject it into the *Client*, and the *Client* will silently collect all of the information it can about the response and pass it on to your handler. What you do with it from there is up to you.
     
### Installation

`composer require forsaken-threads/diplomatic`

### Usage

Handling the request is the easy part, so usage instructions for the *Client* are secondary on this list.  First, we will take a look at the hard part of the request/response pair.  The **Diplomatic** way to handle the response is by injecting an extension of the abstract `ResponseHandler` class into the *Client*.  Read on for more information.

* [Response Handlers](./documentation/ResponseHandler.md)

* [**Diplomatic** Client](./documentation/Client.md)

### Still To Do

* Expose methods to handle SSL security so the *Client* can be used in development environments
* Handle file inputs, currently being rather undiplomatically ignored
* Develop usable example *Response Handlers*
