<?php

use ForsakenThreads\Diplomatic\Interrupt;
use ForsakenThreads\Diplomatic\InterruptContinue;
use PHPUnit\Framework\TestCase;

class ResponseHandlerTest extends TestCase {

    public function testErroredResponse()
    {
        $handler = new Handler;
        $handler->initialize('Error', '', [], 500, [], '');
        $this->assertTrue($handler->wasErrored());
        $this->assertFalse($handler->wasFailed());
        $this->assertFalse($handler->wasSuccessful());
    }

    public function testFailedResponse()
    {
        $handler = new Handler;
        $handler->initialize('Failed', '', [], 422, [], '');
        $this->assertTrue($handler->wasFailed());
        $this->assertFalse($handler->wasErrored());
        $this->assertFalse($handler->wasSuccessful());
    }

    public function testSuccessfulResponse()
    {
        $handler = new Handler;
        $handler->initialize('Successful', '', [], 200, [], '');
        $this->assertTrue($handler->wasSuccessful());
        $this->assertFalse($handler->wasErrored());
        $this->assertFalse($handler->wasFailed());
    }

    public function testFilterManipulatesRawResponse()
    {
        $handler = new Handler;
        $handler->filter(function($response) {
            return $response . 'Received';
        });
        $handler->initialize('Successful', '', [], 200, [], '');
        $this->assertEquals('Successful', $handler->getRawResponse());
        $this->assertEquals('SuccessfulReceived', $handler->getFilteredResponse());
    }

    public function testFilterChainSimple()
    {
        $handler = new Handler;
        $handler->filter(function($response) {
            return $response . 'A';
        })->filter(function($response) {
            return 'B' . $response;
        });
        $handler->initialize('Successful', '', [], 200, [], '');
        $this->assertEquals('Successful', $handler->getRawResponse());
        $this->assertEquals('BSuccessfulA', $handler->getFilteredResponse());
    }

    public function testFilterChainComplex()
    {
        $handler = new Handler;
        $handler->filter(function($response) {
            return (array) $response;
        })->filter(function($response) {
            $response[] = 'Response';
            return $response;
        });
        $handler->initialize('Successful', '', [], 200, [], '');
        $this->assertEquals('Successful', $handler->getRawResponse());
        $this->assertEquals(['Successful', 'Response'], $handler->getFilteredResponse());
    }

    public function testInterrupt()
    {
        $handler = new Handler;
        $handler->filter(function() {
            throw new Interrupt();
        })->filter(function() {
            return 'Error';
        });
        $handler->initialize('Successful', '', [], 200, [], '');
        $this->assertTrue($handler->wasSuccessful());
    }

    public function testInterruptContinue()
    {
        $handler = new Handler;
        $handler->filter(function($response) {
            return ++$response;
        })->filter(function($response) {
            throw new InterruptContinue(2);
        })->filter(function($response) {
            return ++$response;
        })->filter(function($response) {
            return ++$response;
        })->filter(function($response) {
            return ++$response;
        });
        $handler->initialize('1', '', [], 200, [], '');
        $this->assertEquals(3, $handler->getFilteredResponse());
    }

}