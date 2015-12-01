<?php

namespace Robo\Commands;


trait VirtualServerTrait
{
    private function getVirtualServer()
    {
        return $this->host->serverGetById($this->host->whoami()['client_origin_server_id']);
    }

    private function getClientByUid($uid)
    {
        return $this->getVirtualServer()->clientGetByUid($uid);
    }

    private function messageClientByUid($uid , $message)
    {
        return $this->getClientByUid($uid)->message($message);
    }

    private function pokeClientByUid($uid , $message)
    {
        return $this->getClientByUid($uid)->poke($message);
    }

    private function pokeAllClients($message)
    {
        //todo poke all clients
    }

    private function messageAllClients($message)
    {
        //todo message with private all clients..
    }
}