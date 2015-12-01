<?php

use TeamSpeak3\Adapter\ServerQuery\Event;
use TeamSpeak3\Node\Host;
use TeamSpeak3\Ts3Exception;

function onMessage(Event $event, Host $host)
{
    global $ts;

    if($event['invokername'] != $host->whoami()['client_nickname'])
    {
        $response = parseMessage($event["msg"] , $event->getData()['invokeruid'] , $event->getData()['invokerid'] , $event->getData()['invokername']);
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

function parseMessage($msg , $uid , $id , $name)
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
        return postMessageToServer($msg , $uid , $id , $name);
    }

}

function postMessageToserver($msg , $uid , $id , $name)
{
    $post = "uid=".urlencode($uid)."&msg=".urlencode($msg)."&name={$name}"."&id={$id}";
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
