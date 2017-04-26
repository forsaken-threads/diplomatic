<?php

use ForsakenThreads\Diplomatic\BasicHandler;
use ForsakenThreads\Diplomatic\Client;
use PHPUnit\Framework\TestCase;

class BasicResponseHandlerTest extends TestCase {

    /** @var Client */
    protected $client;

    public function setup()
    {
        $this->client = new Client('http://localhost:8888');
    }

    public function testIsBasicHandler()
    {
        $this->client->get('/successful.php')->saveResponseHandler($handler);
        $this->assertEquals(BasicHandler::class, get_class($handler));
    }

    public function testErroredResponse()
    {
        /** @var BasicHandler $handler */
        $result = $this->client->get('/errored.php')->saveResponseHandler($handler);
        $this->assertEquals(BasicHandler::class, get_class($handler));
        $this->assertTrue($handler->wasErrored());
        $this->assertFalse($handler->wasFailed());
        $this->assertFalse($handler->wasSuccessful());
    }

    public function testFailedResponse()
    {
        /** @var BasicHandler $handler */
        $result = $this->client->get('/failed.php')->saveResponseHandler($handler);
        $this->assertEquals(BasicHandler::class, get_class($handler));
        $this->assertFalse($handler->wasErrored());
        $this->assertTrue($handler->wasFailed());
        $this->assertFalse($handler->wasSuccessful());
    }

    public function testSuccessfulResponse()
    {
        /** @var BasicHandler $handler */
        $result = $this->client->get('/successful.php')->saveResponseHandler($handler);
        $this->assertEquals(BasicHandler::class, get_class($handler));
        $this->assertFalse($handler->wasErrored());
        $this->assertFalse($handler->wasFailed());
        $this->assertTrue($handler->wasSuccessful());
    }
}