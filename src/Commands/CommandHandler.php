<?php


namespace Robo\Commands;

use TeamSpeak3\Node\Host;
use TeamSpeak3\Adapter\ServerQuery\Event;

class CommandHandler
{
    protected $localCommands = [
        '!ajuda'    =>  \Robo\Commands\Ajuda::class,
        '!ayuda'    =>  \Robo\Commands\Ayuda::class,
        '!help'     =>  \Robo\Commands\Help::class,
    ];

    public function input(Event $event , Host $host )
    {
        $msgType = $event->getData()['targetmode'];

        if($this->isLocalCommand($event->getData()['msg']))
        {
            return $this->executeLocalCommand($event , $host);
        }
        else
        {
            $response = json_decode($this->executeRemoteCommand($event , $host));

            if(($response->type == "notFound") && ($event->getData()['targetmode'] == 1))
            {
                // Somente responder "comando inválido" caso seja uma mensagem privada..
                $host->serverGetById($host->whoami()['client_origin_server_id'])
                    ->clientGetByUid($event->getData()['invokeruid'])
                    ->message("Comando inválido! :(   [b]!ajuda !ayuda !help[/b]");
            }
            else
            {
                $host->serverGetById($host->whoami()['client_origin_server_id'])
                    ->clientGetByUid($event->getData()['invokeruid'])
                    ->message($response->msg);
            }
        }

    }

    private function isLocalCommand($msg)
    {
        return in_array(strtolower(explode(' ',$msg)[0]) , array_keys($this->localCommands));
    }

    private function executeLocalCommand(Event $event , Host $host)
    {
        $class = $this->getCommandClassName(explode(' ',$event->getData()['msg'])[0]);
        $instance = new $class($event , $host);
        return $instance->handle();
    }

    private function executeRemoteCommand(Event $event , Host $host)
    {
        $data = $event->getData();
        $params = [
            'cid'   =>  urlencode($data['invokerid']),
            'uid'   =>  urlencode($data['invokeruid']),
            'msg'   =>  urlencode($data['msg']),
        ];
        $post = 'cid='.$params['cid'].'&uid='.$params['uid'].'&msg='.$params['msg'];
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


    private function getCommandClassName($command)
    {
        return $this->localCommands[strtolower($command)];
    }
}