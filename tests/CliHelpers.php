<?php

use ForsakenThreads\Diplomatic\ResponseHandler;

trait CliHelpers {

    protected $getData = [
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

    protected $postData = [
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

    protected function assertResultsEqual($cliCall, ResponseHandler $handler)
    {
        list($headers, $htmlVersion, $code, $rawResponse) = $this->parseCliResponse(`$cliCall 2>&1`);
        $this->assertEquals($this->exceptForDate($headers), $this->exceptForDate($handler->getHeaders()));
        $this->assertEquals($htmlVersion, $handler->getHtmlVersion());
        $this->assertEquals($code, $handler->getCode());
        $this->assertEquals($rawResponse, $handler->getRawResponse());
    }

    protected function exceptForDate($array)
    {
        return array_filter($array, function($key) {
            return $key != 'Date';
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function parseCliResponse($response)
    {
        $code = substr($response, -3);
        if ($code == '000') {
            $response = explode("\n", $response, 2);
            $response = preg_replace('/^curl: \(\d+\) /', '', $response[0]);
            return [[], '', $code, $response];
        }
        $response = explode("\r\n\r\n", $response, 2);
        $headers = [];
        $headerStrings = explode("\r\n", $response[0]);
        $version = trim(array_shift($headerStrings));
        foreach ($headerStrings as $headerString) {
            $headerString = explode(':', $headerString, 2);
            $headers[trim($headerString[0])] = trim($headerString[1]);
        }
        $body = count($response) == 2 ? substr($response[1], 0, -3) : '';
        return [$headers, $version, $code, $body];
    }
}

