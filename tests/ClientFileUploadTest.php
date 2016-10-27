<?php

use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\ResponseHandler;
use ForsakenThreads\Diplomatic\Support\BasicFilters;
use PHPUnit\Framework\TestCase;

class ClientFileUploadTest extends TestCase {

    use CliHelpers;

    /** @var Client */
    protected $client;

    public function setup()
    {
        $handler = new Handler();
        $handler->filter([BasicFilters::class, 'json'], true);
        $this->client = new Client('http://localhost:8888', $handler);
    }

    public function testBasicFile()
    {
        /** @var Handler $handler */
        $this->client->post('/file-upload.php', $this->postData, ['basic_file' => __DIR__ . '/test-file.txt'])
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
    }

}