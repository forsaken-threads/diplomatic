## Test Setup

The tests require a local API server running on `localhost:8888` that serves the files in `{base_dir}/tests/api-server/`.  You can use PHP's built-in server to accomplish this with the following command:

```
php -S localhost:8888 -t tests/api-server
````
