#!/usr/bin/env php
<?php

use TeamSpeak3\TeamSpeak3;
use TeamSpeak3\Ts3Exception;
use TeamSpeak3\Helper\Signal;

use TeamSpeak3\Adapter\ServerQuery\Event;
use TeamSpeak3\Node\Host;

/**
 * r2d2 Bot.
 */



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




$ts = TeamSpeak3::factory("serverquery://{$credentials->user}:{$credentials->pass}@{$credentials->ip}:10011/?server_port={$credentials->port}&blocking=0&nickname={$credentials->nick}#no_query_clients");

// Register the following events to subscribe..
$ts->notifyRegister("textprivate");
$ts->notifyRegister("textserver");
$ts->notifyRegister("server");

// Define the callback to the subscribed events..
Signal::getInstance()->subscribe("notifyTextmessage", 'onMessage');
Signal::getInstance()->subscribe("notifyEvent", 'onEvent');

while(true)
{
    $ts->getAdapter()->wait();
}


function onMessage(Event $event, Host $host)
{
    global $ts;

    if($event['invokername'] != $host->whoami()['client_nickname'])
    {
        $response = parseMessage($event["msg"] , $event['invokeruid']);
        try{
            $ts->clientGetByName($event['invokername'])->message($response);
        }catch (Ts3Exception $exception)
        {
            //...
        }

    }
}

function onEvent(Event $event, Host $host)
{
    if($event->getType() == "cliententerview")
    {
        global $ts;
        $data = $event->getData();

        try{
        $ts->clientGetByName($data['client_nickname'])->message(detectLang($data['client_country']));
        }catch (Ts3Exception $exception)
        {
            //...
        }
    }
}

function detectLang($flag)
{
    switch($flag)
    {
        case in_array($flag , ['BR','POR']):
            return file_get_contents(__DIR__.'/messages/welcome/portugues.txt');
        break;

        case in_array($flag , ['ES','CL','VNZL','MX']):
            return file_get_contents(__DIR__.'/messages/welcome/espanhol.txt');
        break;
        default:
            return file_get_contents(__DIR__.'/messages/welcome/ingles.txt');
        break;
    }
}

function parseMessage($msg , $uid)
{
    $explode = explode(' ' , $msg);

    if(strtolower($explode[0]) == '!ajuda')
    {
        return file_get_contents(__DIR__.'/messages/help/portugues.txt');
    }elseif(strtolower($explode[0]) == '!ayuda'){
        return file_get_contents(__DIR__.'/messages/help/espanhol.txt');
    }elseif(strtolower($explode[0]) == '!help'){
        return file_get_contents(__DIR__.'/messages/help/ingles.txt');
    }else{
        return postMessageToServer($msg , $uid);
    }

}

function postMessageToserver($msg , $uid)
{
    $post = "uid=".urlencode($uid)."&msg=".urlencode($msg);
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, API_URL.'request');
    curl_setopt($ch,CURLOPT_POST, 2);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $response = curl_close($ch);

    return $result;
}
