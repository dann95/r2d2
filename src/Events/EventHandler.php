<?php

namespace Robo\Events;

use TeamSpeak3\Adapter\ServerQuery\Event;
use TeamSpeak3\Node\Host;
use Robo\Commands\CommandHandler;

class EventHandler
{

    private $command;

    public function __construct()
    {
        $this->command = new CommandHandler();
    }

    public function onMessage(Event $event , Host $host)
    {
        // Não é minha a mensagem!
        if($event->getData()['invokeruid'] != $host->whoami()['client_unique_identifier'])
        {
            $this->command->input($event , $host);
        }
    }

    public function onEvent(Event $event , Host $host)
    {
        //echo "hehehe";
    }
}