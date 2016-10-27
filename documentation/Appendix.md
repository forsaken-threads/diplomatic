## Appendix

### Other *Client* features

_SSL Verification_

When it comes to dealing with SSL verification, **Diplomatic** defaults to stringent checking.  If you need to turn this off for some reason, you can use the `insecure()` method to do so.  To turn stringent checking back on, simply call that method again with `false` as the argument.

_User Agent string_

The default user agent string that diplomatic uses is something like `forsaken-threads/diplomatic wrapping cURL version 7.38.0`.  You can supply your own user agent string with the setter method, `setUserAgent()`.

* [Response Handler](./ResponseHandler.md)
* [**Diplomatic** Client](./Client.md)

_Multipart/form-data_

By default, **Diplomatic** will use `application/x-www-form-urlencoding` and only use `multipart/form-data` when a file upload is performed.  If you want to force multipart encoding, you can use the setter, `setMultipart()`.  Much like the `insecure()` method, passing false will turn multipart back to being automatically set. 