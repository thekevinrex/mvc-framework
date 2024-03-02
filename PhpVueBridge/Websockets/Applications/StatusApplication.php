<?php

namespace app\Core\Websockets\Applications;

use app\Core\Websockets\Connection;

class StatusApplication extends WsApplication
{
    private $_clients = [];
    private $_serverClients = [];
    private $_serverInfo = [];
    private $_serverClientCount = 0;

    private function _encodeData($action, $data)
    {
        return json_encode(['action' => $action, 'data' => $data]);
    }

    public function onConnected(Connection $client): void
    {
        $id = $client->getId();
        $this->_clients[$id] = $client;
        $this->_sendServerinfo($client);
        $this->clientConnected($client->getIp(), $client->getPort());
    }

    private function _sendServerinfo($client)
    {
        if (count($this->_clients) < 1) {
            return false;
        }

        $currentServerInfo = $this->_serverInfo;
        $currentServerInfo['clientCount'] = count($this->_serverClients);
        $currentServerInfo['clients'] = $this->_serverClients;
        $encodedData = $this->_encodeData('serverInfo', $currentServerInfo);

        $client->send($encodedData);
    }

    public function onDisconnected(Connection $client): void
    {
        $id = $client->getId();
        unset($this->_clients[$id]);
        $this->clientDisconnected($client->getIp(), $client->getPort());
    }

    public function setServerInfo($serverInfo)
    {
        if (is_array($serverInfo)) {
            $this->_serverInfo = $serverInfo;
            return true;
        }

        return false;
    }

    public function clientConnected($ip, $port)
    {
        $this->_serverClients[$port] = $ip;
        $this->_serverClientCount++;
        $this->statusMsg('Client connected: ' . $ip . ':' . $port);

        $data = [
            'ip' => $ip,
            'port' => $port,
            'clientCount' => $this->_serverClientCount,
        ];

        $encodedData = $this->_encodeData('clientConnected', $data);

        $this->_sendAll($encodedData);
    }

    /**
     * @param string $text
     */
    public function statusMsg($text, $type = 'info')
    {
        $data = [
            'type' => $type,
            'text' => '[' . date('Y-m-d H:i:s') . '] ' . $text,
        ];

        $encodedData = $this->_encodeData('statusMsg', $data);

        $this->_sendAll($encodedData);
    }

    private function _sendAll($encodedData)
    {
        if (count($this->_clients) < 1) {
            return false;
        }

        foreach ($this->_clients as $sendto) {
            $sendto->send($encodedData);
        }
    }

    public function clientDisconnected($ip, $port)
    {
        if (!isset($this->_serverClients[$port])) {
            return false;
        }

        unset($this->_serverClients[$port]);

        $this->_serverClientCount--;
        $this->statusMsg('Client disconnected: ' . $ip . ':' . $port);

        $data = [
            'port' => $port,
            'clientCount' => $this->_serverClientCount,
        ];

        $encodedData = $this->_encodeData('clientDisconnected', $data);

        $this->_sendAll($encodedData);
    }

    public function clientActivity($port)
    {
        $encodedData = $this->_encodeData('clientActivity', $port);
        $this->_sendAll($encodedData);
    }

    public function onData($data, Connection $connection): void
    {
        # code...
    }
}

?>