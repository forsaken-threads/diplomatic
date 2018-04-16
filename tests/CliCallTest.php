<?php

use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\Support\BasicFilters;
use PHPUnit\Framework\TestCase;

class CliCallTest extends TestCase {

    use CliHelpers;

    /** @var Client */
    protected $client;

    protected $stringData;

    public function setup()
    {
        $handler = new Handler();
        $handler->filter([BasicFilters::class, 'json'], true);
        $this->client = new Client('http://localhost:8888', $handler);
        $this->stringData = json_encode($this->getData);
    }

    public function testAddSetHeaders()
    {
        /** @var Handler $handler */
        $rand1 = rand(1000, 9999) . '-Diplomatic';
        $this->client->addHeaders(['X-Diplomatic-Test-1' => $rand1])
            ->get('/set-headers.php')
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);

        $rand2 = $rand1 . rand(1000, 9999) . '-Diplomatic';
        $this->client->addHeaders(['X-Diplomatic-Test-2' => $rand2])
            ->get('/set-headers.php')
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);


        $rand3 = $rand2 . rand(1000, 9999) . '-Diplomatic';
        $this->client->setHeaders(['X-Diplomatic-Test-3' => $rand3])
            ->get('/set-headers.php')
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);

    }

    public function testResponseHeaders()
    {
        /** @var Handler $handler */
        $this->client->get('/response-header.php')
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
    }

    public function testEchoData()
    {
        /** @var Handler $handler */
        $this->client->head('/echo-data.php')
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertEquals('', $handler->getRawResponse());

        $this->client->options('/echo-data.php')
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('OPTIONS', $handler->getRawResponse());

        $this->client->get('/echo-data.php', $this->getData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('GET', $handler->getRawResponse());

        $this->client->delete('/echo-data.php?' . http_build_query($this->getData), $this->postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('DELETE', $handler->getRawResponse());

        $this->client->post('/echo-data.php?' . http_build_query($this->getData), $this->postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('POST', $handler->getRawResponse());

        $this->client->put('/echo-data.php?' . http_build_query($this->getData), $this->postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('PUT', $handler->getRawResponse());

        $this->client->patch('/echo-data.php?' . http_build_query($this->getData), $this->postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('PATCH', $handler->getRawResponse());

        $this->client->trace('/echo-data.php?' . http_build_query($this->getData), $this->postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('TRACE', $handler->getRawResponse());

        $this->client
            ->setMultipart()
            ->post('/echo-data.php?' . http_build_query($this->getData), $this->postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
    }

    public function testEchoBody()
    {
        /** @var Handler $handler */
        $this->client->setHeaders(['Content-type' => 'application/json']);
        $this->client->get('/echo-data.php', $this->getData, $this->stringData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('GET', $handler->getRawResponse());

        $this->client->get('/echo-data.php', $this->stringData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('GET', $handler->getRawResponse());

        $this->client->delete('/echo-data.php?' . http_build_query($this->getData), $this->stringData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('DELETE', $handler->getRawResponse());

        $this->client->post('/echo-data.php?' . http_build_query($this->getData), $this->stringData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('POST', $handler->getRawResponse());

        $this->client->put('/echo-data.php?' . http_build_query($this->getData), $this->stringData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('PUT', $handler->getRawResponse());

        $this->client->patch('/echo-data.php?' . http_build_query($this->getData), $this->stringData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('PATCH', $handler->getRawResponse());

        $this->client->trace('/echo-data.php?' . http_build_query($this->getData), $this->stringData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('TRACE', $handler->getRawResponse());
    }

}