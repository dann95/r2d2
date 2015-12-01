#!/usr/bin/env php
<?php

set_time_limit(0);

define("__ROOT__" , realpath(__DIR__));

use TeamSpeak3\TeamSpeak3;
use TeamSpeak3\Helper\Signal;
use Robo\Events\EventHandler;


// Autoload from composer.
require_once(__DIR__.'/vendor/autoload.php');

if(! isset($argv[1]))
{
    exit("No api code! :(");
}
defined('API_URL') || define('API_URL',"http://localhost:666/api/r2d2/{$argv[1]}/");

$credentials = json_decode(@file_get_contents(API_URL."credentials"));

if(!$credentials)
{
    exit("wrong api code!");
}

$handler = new EventHandler();

$ts = TeamSpeak3::factory("serverquery://{$credentials->user}:{$credentials->pass}@{$credentials->ip}:10011/?server_port={$credentials->port}&blocking=0&nickname={$credentials->nick}#no_query_clients");

// Register the following events to subscribe..
$ts->notifyRegister("textprivate");
$ts->notifyRegister("textserver");
$ts->notifyRegister("server");

// Define the callback to the subscribed events..
Signal::getInstance()->subscribe("notifyTextmessage", array($handler ,'onMessage'));
Signal::getInstance()->subscribe("notifyEvent", array($handler , 'onEvent'));

while(true)
{
    $ts->getAdapter()->wait();
}

