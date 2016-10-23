# Diplomatic

### A diplomatic cURL wrapper for PHP

This is a practical, yet sophisticated, HTTP client for making API calls and parsing their responses.  Some of the basic features are *Response Handler* dependency injection, callback registration for the three basic response results - errored, failed, and successful - and the ability to print out a CLI version of the underlying cURL call for the ultimate in hands on debugging.
     
### Installation

`composer require forsaken-threads/diplomatic`

### Usage

Handling the request is the easy part, so usage instructions for the *Client* are secondary.  First, we will take a look at the hard part of the request/response pair.  The **Diplomatic** way to handle the response is by injecting an extension of the abstract `ResponseHandler` class into the *Client*.  Read on for more information.

[Response Handlers](./documentation/ResponseHandler.md)

[**Diplomatic** Client](./documentation/Client.md)

### Still ToDo

* Expose methods to handle SSL security so the *Client* can be used in development environments
* Handle file inputs, currently being rather undiplomatically ignored
* Develop usable example *Response Handlers*
* Write tests