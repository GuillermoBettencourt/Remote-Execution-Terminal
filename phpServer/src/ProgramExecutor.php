<?php
// ProgramExecutor.php
namespace RemoteExecutionServer;

use Ratchet\ConnectionInterface;

class ProgramExecutor {
    private $processMap = [];
    private $pipesMap = [];

    public function startProcess(ConnectionInterface $conn) {
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];

        $process = proc_open(getcwd() . '/c-program/myprogram', $descriptorspec, $pipes);
        if (is_resource($process)) {

            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            $conn->send("\r\nC program started.\r\n\r\n");

            $this->processMap[spl_object_hash($conn)] = $process;
            $this->pipesMap[spl_object_hash($conn)] = $pipes;
        } else {
            $conn->send("\r\nFailed to start C program.\r\n");
        }
    }

    public function isProcessRunning(ConnectionInterface $conn) {
        return isset($this->processMap[spl_object_hash($conn)]);
    }

    public function sendInputToProgram(ConnectionInterface $conn, $input) {
        $pipes = $this->pipesMap[spl_object_hash($conn)] ?? null;
        if ($pipes) {
            fwrite($pipes[0], $input . "\n");
        }
    }

    public function terminateProcess(ConnectionInterface $conn) {
        $process = $this->processMap[spl_object_hash($conn)] ?? null;
        if ($process) {
            proc_terminate($process);
            unset($this->processMap[spl_object_hash($conn)]);
        }
    }

    public function checkOutput(ConnectionInterface $conn) {
        $process = $this->processMap[spl_object_hash($conn)] ?? null;
        $pipes = $this->pipesMap[spl_object_hash($conn)] ?? null;

        if ($process && $pipes) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            $status = proc_get_status($process);

            if (!empty($output)) {
                $conn->send($output);
            }

            if (!empty($error)) {
                $conn->send($error);
            }
            
            if (!$status['running']) {
                $this->closeProcess($conn);
                $conn->send("\r\nC program finished.\r\n");
                $conn->close();
            }
        }
    }

    public function closeProcess(ConnectionInterface $conn) {
        $process = $this->processMap[spl_object_hash($conn)] ?? null;
        $pipes = $this->pipesMap[spl_object_hash($conn)] ?? null;

        if ($process && $pipes) {
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            proc_close($process);
            unset($this->processMap[spl_object_hash($conn)], $this->pipesMap[spl_object_hash($conn)]);
        }
    }
}
?>
