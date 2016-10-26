<?php

use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\ResponseHandler;
use PHPUnit\Framework\TestCase;

class ClientOnMethodsCallbackTest extends TestCase {

    /** @var Client */
    protected $client;

    public function setup()
    {
        $handler = new Handler();
        $this->client = new Client('http://localhost:8888', $handler);
        $this->client->resetHandlersAfterRequest(false)
            ->onError(function (ResponseHandler $response) { return substr($response->getRawResponse(), 0, 1); })
            ->onFailure(function (ResponseHandler $response) { return substr($response->getRawResponse(), 0, 1); })
            ->onSuccess(function (ResponseHandler $response) { return substr($response->getRawResponse(), 0, 1); });
    }

    public function testErroredResponse()
    {
        $result = $this->client->get('/errored.php');
        $this->assertEquals('E', $result);
    }

    public function testFailedResponse()
    {
        $result = $this->client->get('/failed.php');
        $this->assertEquals('F', $result);
    }

    public function testSuccessfulResponse()
    {
        $result = $this->client->get('/successful.php');
        $this->assertEquals('S', $result);
    }
}