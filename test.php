<?php

require './vendor/autoload.php';

$sipper = new \ForsakenThreads\Sip\Sip('http://dev.markitondemand.com/MODApis/Api/v2/', new \ForsakenThreads\Sip\MarkitOnDemand);

$sipper
    ->onAny(function ($drink) {
        var_dump($drink);
    })
    ->get('Quote', ['symbol' => 'NDAQ']);