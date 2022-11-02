<?php

use ForsakenThreads\Diplomatic\Client;
use PHPUnit\Framework\TestCase;

class ClientSelfHandlingTest extends TestCase {

    /** @var Client */
    protected $client;

    public function setup(): void
    {
        $handler = new SelfHandler();
        $this->client = new Client('http://localhost:8888', $handler);
    }

    public function testErroredResponse()
    {
        $result = $this->client->get('/errored.php');
        $this->assertEquals('WasErrored', $result);
    }

    public function testFailedResponse()
    {
        $result = $this->client->get('/failed.php');
        $this->assertEquals('WasFailed', $result);
    }

    public function testSuccessfulResponse()
    {
        $result = $this->client->get('/successful.php');
        $this->assertEquals('WasSuccessful', $result);
    }
}