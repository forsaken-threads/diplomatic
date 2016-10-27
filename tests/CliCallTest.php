<?php

use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\Support\BasicFilters;
use PHPUnit\Framework\TestCase;

class CliCallTest extends TestCase {

    use CliHelpers;

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

    public function testInsecureConnection()
    {
        /** @var Handler $handler */
        $client = new Client('https://madlib.tirekickin.com', new Handler);

        $client->get('/index.php')
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);

        $client->insecure()
            ->get('/index.php')
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        list($headers, $htmlVersion, $code, $rawResponse) = $this->parseCliResponse(`$cliCall 2>&1`);
        // Remove some headers that could be understandably different
        $testHeaders = $handler->getHeaders();
        unset($testHeaders['Set-Cookie']);
        unset($headers['Set-Cookie']);
        unset($testHeaders['Date']);
        unset($headers['Date']);
        unset($testHeaders['Expires']);
        unset($headers['Expires']);
        $this->assertEquals($headers, $testHeaders);
        $this->assertEquals($htmlVersion, $handler->getHtmlVersion());
        $this->assertEquals($code, $handler->getCode());
        $this->assertEquals($rawResponse, $handler->getRawResponse());
    }

    public function testEchoData()
    {
        $getData = [
            'a' => 'simple variable',
            'b' => [
                'complex',
                'variable',
                'nested' => [
                    'deeply',
                    'deeply' => ['nested'],
                ],
            ],
        ];

        $postData = [
            'c' => 'simple variable',
            'd' => [
                'complex',
                'variable',
                'nested' => [
                    'deeply',
                    'deeply' => ['nested'],
                ],
            ],
        ];

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

        $this->client->get('/echo-data.php', $getData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('GET', $handler->getRawResponse());

        $this->client->delete('/echo-data.php?' . http_build_query($getData), $postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('DELETE', $handler->getRawResponse());

        $this->client->post('/echo-data.php?' . http_build_query($getData), $postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('POST', $handler->getRawResponse());

        $this->client->put('/echo-data.php?' . http_build_query($getData), $postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('PUT', $handler->getRawResponse());

        $this->client->patch('/echo-data.php?' . http_build_query($getData), $postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('PATCH', $handler->getRawResponse());

        $this->client->trace('/echo-data.php?' . http_build_query($getData), $postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertStringStartsWith('TRACE', $handler->getRawResponse());

        $this->client
            ->setMultipart()
            ->post('/echo-data.php?' . http_build_query($getData), $postData)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
    }

}