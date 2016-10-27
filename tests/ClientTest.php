<?php

use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\Support\BasicFilters;
use PHPUnit\Framework\TestCase;

class ClientGeneralTest extends TestCase {

    /** @var Client */
    protected $client;

    public function setup()
    {
        $handler = new Handler();
        $handler->filter([BasicFilters::class, 'json'], true);
        $this->client = new Client('http://localhost:8888', $handler);
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

    public function testResponseHeaders()
    {
        $this->client->get('/response-header.php')
            ->saveResponseHandler($handler);
        /** @var Handler $handler */
        $this->assertContains('X-Diplomatic-Response-Header', array_keys($handler->getHeaders()));
        $this->assertContains('ThisIsADiplomaticTest', $handler->getHeaders());
    }

    public function testInsecureConnection()
    {
        /** @var Handler $handler */
        $this->client->setDestination('https://madlib.tirekickin.com')
            ->get('/index.php')
            ->saveResponseHandler($handler)
            ->saveResponseCode($code);
        $this->assertEquals('SSL certificate problem: unable to get local issuer certificate', $handler->getRawResponse());
        $this->assertEquals(0, $code);

        $this->client->insecure()
            ->get('/index.php')
            ->saveResponseHandler($handler)
            ->saveResponseCode($code);
        $this->assertRegExp('/MAD LIB 3.0/', $handler->getRawResponse());
        $this->assertEquals(200, $code);
    }

}