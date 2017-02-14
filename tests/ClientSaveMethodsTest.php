<?php

use ForsakenThreads\Diplomatic\Client;
use PHPUnit\Framework\TestCase;

class ClientSaveMethodsTest extends TestCase {

    /** @var Client */
    protected $client;

    public function setup()
    {
        $handler = new Handler();
        $this->client = new Client('http://localhost:8888', $handler);
    }

    public function testErroredResponse()
    {
        /** @var Handler $handler */
        $this->client
            ->get('/errored.php')
            ->saveResponseHandler($handler)
            ->saveResponseCode($code)
            ->saveResponseHeaders($headers)
            ->saveRawResponse($response);
        $this->assertTrue($handler->wasErrored());
        $this->assertEquals(500, $handler->getCode());
        $this->assertEquals($code, $handler->getCode());
        $this->assertEquals($headers, $handler->getHeaders());
        $this->assertEquals($response, $handler->getRawResponse());
    }

    public function testFailedResponse()
    {
        /** @var Handler $handler */
        $this->client
            ->get('/failed.php')
            ->saveResponseHandler($handler)
            ->saveResponseCode($code)
            ->saveResponseHeaders($headers)
            ->saveRawResponse($response);
        $this->assertTrue($handler->wasFailed());
        $this->assertEquals(422, $handler->getCode());
        $this->assertEquals($code, $handler->getCode());
        $this->assertEquals($headers, $handler->getHeaders());
        $this->assertEquals($response, $handler->getRawResponse());
    }

    public function testSuccessfulResponse()
    {
        /** @var Handler $handler */
        $this->client
            ->get('/successful.php')
            ->saveResponseHandler($handler)
            ->saveResponseCode($code)
            ->saveResponseHeaders($headers)
            ->saveRawResponse($response);
        $this->assertTrue($handler->wasSuccessful());
        $this->assertEquals(200, $handler->getCode());
        $this->assertEquals($code, $handler->getCode());
        $this->assertEquals($headers, $handler->getHeaders());
        $this->assertEquals($response, $handler->getRawResponse());
    }
}