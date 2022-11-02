<?php

use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\Support\BasicFilters;
use PHPUnit\Framework\TestCase;

class ClientFileUploadTest extends TestCase {

    use CliHelpers;

    protected $fileInfo = [
        'basic_files' => [
            // we'll throw in some bad characters for good measure
            // hiding this because it breaks on winblows. ['/test-files/test-file1\'";.txt', 'ABC123'],
            ['/test-files/test-file2.txt', 'DEF456'],
            ['/test-files/test-file3.txt', 'GHI789'],
        ],
    ];

    /** @var Client */
    protected $client;

    public function setup(): void
    {
        $handler = new Handler();
        $handler->filter([BasicFilters::class, 'json'], true);
        $this->client = new Client('http://localhost:8888', $handler);
    }

    public function testBasicFiles()
    {
        /** @var Handler $handler */
        // This should totally fail, but diplomatically not throw an exception
        $this->client->post('/file-upload.php', $this->postData, ['basic_file' => false])
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertEquals('operation aborted by callback', $handler->getRawResponse());
        $this->assertMatchesRegularExpression('/^curl: option -F: is badly used here/', `$cliCall 2>&1`);

        $this->client->post('/file-upload.php', $this->postData, ['basic_file' => __DIR__ . $this->fileInfo['basic_files'][0][0]])
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertMatchesRegularExpression('/^' . $this->fileInfo['basic_files'][0][1] . '$/m', $handler->getRawResponse());

        $files = [];
        $regex = [];
        foreach ($this->fileInfo['basic_files'] as $index => $file) {
            $files["basic_file[$index]"] = __DIR__ . $file[0];
            $regex[] = $file[1];
        }
        $this->client->post('/file-upload.php', $this->postData, $files)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        foreach ($regex as $expression) {
            $this->assertMatchesRegularExpression('/^' . $expression . '$/m', $handler->getRawResponse());
        }

        $files = [];
        $regex = [];
        foreach ($this->fileInfo['basic_files'] as $index => $file) {
            $files["basic_file[$index]"] = [__DIR__ . $file[0], 'text/plain', "file$index.txt"];
            $regex[] = $file[1];
        }
        $this->client->post('/file-upload.php', $this->postData, $files)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        foreach ($regex as $expression) {
            $this->assertMatchesRegularExpression('/^' . $expression . '$/m', $handler->getRawResponse());
        }

        $files = [];
        $regex = [];
        foreach ($this->fileInfo['basic_files'] as $index => $file) {
            $files["basic_file[$index]"] = [__DIR__ . $file[0], 'text/plain'];
            $regex[] = $file[1];
        }
        $this->client->post('/file-upload.php', $this->postData, $files)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        foreach ($regex as $expression) {
            $this->assertMatchesRegularExpression('/^' . $expression . '$/m', $handler->getRawResponse());
        }

        $files = [];
        $regex = [];
        foreach ($this->fileInfo['basic_files'] as $index => $file) {
            $files["basic_file[$index]"] = [__DIR__ . $file[0], null, "file$index.txt"];
            $regex[] = $file[1];
        }
        $this->client->post('/file-upload.php', $this->postData, $files)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        foreach ($regex as $expression) {
            $this->assertMatchesRegularExpression('/^' . $expression . '$/m', $handler->getRawResponse());
        }
    }

    public function testSplFileInfoAndObject()
    {
        /** @var Handler $handler */
        $this->client->post('/file-upload.php', $this->postData, ['basic_file' => new SplFileInfo(__DIR__ . $this->fileInfo['basic_files'][0][0])])
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        $this->assertMatchesRegularExpression('/^' . $this->fileInfo['basic_files'][0][1] . '$/m', $handler->getRawResponse());

        $files = [];
        $regex = [];
        foreach ($this->fileInfo['basic_files'] as $index => $file) {
            $files["basic_file[$index]"] = new SplFileObject(__DIR__ . $file[0]);
            $regex[] = $file[1];
        }
        $this->client->post('/file-upload.php', $this->postData, $files)
            ->saveCall($cliCall)
            ->saveResponseHandler($handler);
        $this->assertResultsEqual($cliCall, $handler);
        foreach ($regex as $expression) {
            $this->assertMatchesRegularExpression('/^' . $expression . '$/m', $handler->getRawResponse());
        }
    }
}