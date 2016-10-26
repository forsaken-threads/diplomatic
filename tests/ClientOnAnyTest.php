<?php

use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\ResponseHandler;
use PHPUnit\Framework\TestCase;

class ClientOnAnyTest extends TestCase {

    /** @var Client */
    protected $client;

    public function setup()
    {
        $handler = new Handler();
        $this->client = new Client('http://localhost:8888', $handler);
    }

    public function testAnyFirstCallback()
    {
        $this->client
            ->onAny(function (ResponseHandler $response) { return substr($response->getRawResponse(), 0, 1); })
            ->onSuccess(function (ResponseHandler $response) { return substr($response->getRawResponse(), 0, 1); });
        $result = $this->client->get('/errored.php');
        $this->assertEquals('E', $result);
    }

    public function testAnyLastCallback()
    {
        $this->client
            ->onSuccess(function (ResponseHandler $response) { return substr($response->getRawResponse(), 0, 1); })
            ->onAny(function (ResponseHandler $response) { return substr($response->getRawResponse(), 0, 1); });
        $result = $this->client->get('/errored.php');
        $this->assertEquals('E', $result);
    }

    public function testAnyFirstNonCallback()
    {
        $this->client
            ->onAny('A')
            ->onSuccess('S');
        $result = $this->client->get('/errored.php');
        $this->assertEquals('A', $result);
    }

    public function testAnyLastNonCallback()
    {
        $this->client
            ->onSuccess('S')
            ->onAny('A');
        $result = $this->client->get('/errored.php');
        $this->assertEquals('A', $result);
    }

}