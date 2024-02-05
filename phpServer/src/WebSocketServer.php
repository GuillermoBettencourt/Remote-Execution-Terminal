<?php

namespace RemoteExecutionServer;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $programExecutor;

    public function __construct(ProgramExecutor $programExecutor) {
        $this->clients = new \SplObjectStorage;
        $this->programExecutor = $programExecutor;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $conn->send("Connection established.\r\n");
        $this->programExecutor->startProcess($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        if ($this->programExecutor->isProcessRunning($from)) {
            $this->programExecutor->sendInputToProgram($from, $msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if($this->programExecutor->isProcessRunning($conn)) {
            $this->programExecutor->terminateProcess($conn);
        }
        $conn->send("Goodbye!\r\n");
        $this->clients->detach($conn);
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

    public function checkProcesses() {
        foreach ($this->clients as $client) {
            $this->programExecutor->checkOutput($client);
        }
    }
}
?>
