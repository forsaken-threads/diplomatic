<?php

use ForsakenThreads\Diplomatic\Client;
use PHPUnit\Framework\TestCase;

class ClientOnMethodsNonCallbackTest extends TestCase {

    /** @var Client */
    protected $client;

    public function setup(): void
    {
        $handler = new Handler();
        $this->client = new Client('http://localhost:8888', $handler);
        $this->client->resetHandlersAfterRequest(false)
            ->onError('E')
            ->onFailure('F')
            ->onSuccess('S');
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