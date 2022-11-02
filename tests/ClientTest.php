<?php

use ForsakenThreads\Diplomatic\BasicHandler;
use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\SimpleJsonArrayHandler;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase {

    /** @var Client */
    protected $client;

    public function setup(): void
    {
        $this->client = new Client('http://localhost:8888', SimpleJsonArrayHandler::class);
    }

    public function testAddSetHeaders()
    {
        $rand1 = rand(1000, 9999) . '-Diplomatic';
        $this->client->addHeaders(['X-Diplomatic-Test-1' => $rand1])
            ->get('/set-headers.php')
            ->saveResponseHandler($handler);
        /** @var Handler $handler */
        $this->assertContains('X-Diplomatic-Test-1', array_keys($handler->getFilteredResponse()));
        $this->assertContains($rand1, $handler->getFilteredResponse());

        $rand2 = $rand1 . rand(1000, 9999) . '-Diplomatic';
        $this->client->addHeaders(['X-Diplomatic-Test-2' => $rand2])
            ->get('/set-headers.php')
            ->saveResponseHandler($handler);
        /** @var Handler $handler */
        $this->assertContains('X-Diplomatic-Test-2', array_keys($handler->getFilteredResponse()));
        $this->assertContains($rand2, $handler->getFilteredResponse());

        $rand3 = $rand2 . rand(1000, 9999) . '-Diplomatic';
        $this->client->setHeaders(['X-Diplomatic-Test-3' => $rand3])
            ->get('/set-headers.php')
            ->saveResponseHandler($handler);
        /** @var Handler $handler */
        $this->assertNotContains('X-Diplomatic-Test-1', array_keys($handler->getFilteredResponse()));
        $this->assertNotContains($rand1, $handler->getFilteredResponse());
        $this->assertNotContains('X-Diplomatic-Test-2', array_keys($handler->getFilteredResponse()));
        $this->assertNotContains($rand2, $handler->getFilteredResponse());
        $this->assertContains('X-Diplomatic-Test-3', array_keys($handler->getFilteredResponse()));
        $this->assertContains($rand3, $handler->getFilteredResponse());
    }

    public function testSetResponseHandler()
    {
        $client = new Client('http://localhost:8888');
        $client->get('/successful.php')->saveResponseHandler($handler);
        $this->assertEquals(get_class($handler), BasicHandler::class);

        $this->client->setResponseHandler(null);
        $this->client->get('/successful.php')->saveResponseHandler($handler);
        $this->assertEquals(get_class($handler), BasicHandler::class);

        $this->client->setResponseHandler(SimpleJsonArrayHandler::class);
        $this->client->get('/successful.php')->saveResponseHandler($handler);
        $this->assertEquals(get_class($handler), SimpleJsonArrayHandler::class);

        $this->client->setResponseHandler(new SimpleJsonArrayHandler());
        $this->client->get('/successful.php')->saveResponseHandler($handler);
        $this->assertEquals(get_class($handler), SimpleJsonArrayHandler::class);
    }

    public function testResponseHeaders()
    {
        $this->client->get('/response-header.php')
            ->saveResponseHandler($handler);
        /** @var Handler $handler */
        $this->assertContains('X-Diplomatic-Response-Header', array_keys($handler->getHeaders()));
        $this->assertContains('ThisIsADiplomaticTest', $handler->getHeaders());
    }

}