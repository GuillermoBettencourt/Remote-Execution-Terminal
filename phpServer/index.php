<?php 

require __DIR__ . '/vendor/autoload.php';

use RemoteExecutionServer\WebSocketServer;
use RemoteExecutionServer\ProgramExecutor;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$programExecutor = new ProgramExecutor();
$webSocketServer = new WebSocketServer($programExecutor);

$server = IoServer::factory(
    new HttpServer(new WsServer($webSocketServer)),
    8080
);

$loop = $server->loop;
$loop->addPeriodicTimer(0.2, function () use ($webSocketServer) {
    $webSocketServer->checkProcesses();
});

$server->run();

?>