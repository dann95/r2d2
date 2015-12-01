<?php


namespace Robo\Commands;

use TeamSpeak3\Node\Host;
use TeamSpeak3\Adapter\ServerQuery\Event;
use Robo\Commands\VirtualServerTrait;

class Help
{
    private $host;
    private $event;

    use VirtualServerTrait;

    public function __construct(Event $event , Host $host)
    {
        $this->event = $event;
        $this->host = $host;
    }

    public function handle()
    {
        $msg = file_get_contents(__ROOT__.'/messages/help/ingles.txt');
        return $this->messageClientByUid($this->event->getData()['invokeruid'] , $msg);
    }
}