<?php

namespace LessPHP\H5app;

class Client
{
    const StatusOK                  = "0";
    const StatusErr                 = "1";

    const EventNone                 = "10";
    const EventNodeCreated          = "11";
    const EventNodeDeleted          = "12";
    const EventNodeDataChanged      = "13";
    const EventNodeChildrenChanged  = "14";

    // Socket connection to the h5app service
    // @var resource
    // @access private
    private $sock;

    // The structure representing the data source of the h5app server
    // @var array
    // @access public
    public $dsn;

    // The queue of commands to be sent to the Redis server
    // @var array
    // @access private
    private $queue = array();
    
    // Creates a connection to the h5app at the address specified by {@link $dsn}.
    // The default connection is to the server running on localhost on port 9530.
    // @param string $dsn The data source name of the h5app server
    // @param float $timeout The connection timeout in seconds
    public function __construct($dsn = 'h5app://127.0.0.1:9530', $timeout = null)
    {
        $this->dsn = parse_url($dsn);
        $host = isset($this->dsn['host']) ? $this->dsn['host'] : '127.0.0.1';
        $port = isset($this->dsn['port']) ? $this->dsn['port'] : '9530';
        $timeout = $timeout ?: ini_get("default_socket_timeout");
        $this->sock = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($this->sock === FALSE) {
            throw new Exception("{$errno} - {$errstr}");
        }
        if (isset($this->dsn['pass'])) {
            $this->auth($this->dsn['pass']);
        }
    }
    
    public function __destruct()
    {
        fclose($this->sock);
    }
    
    // Flushes the commands in the pipeline queue to h5app and returns the responses.
    // @see pipeline
    // @access public
    public function uncork()
    {
        // Open a h5app connection and execute the queued commands
        foreach ($this->queue as $cmd) {
            for ($written = 0; $written < strlen($cmd); $written += $fwrite) {
                $fwrite = fwrite($this->sock, substr($cmd, $written));
                if ($fwrite === FALSE) {
                    throw new Exception('Failed to write entire command to stream');
                }
            }
        }

        // Read in the results from the pipelined commands
        $responses = array();
        for ($i = 0; $i < count($this->queue); $i++) {
            $responses[] = $this->readResponse();
        }

        // Clear the queue and return the response
        $this->queue = array();
        return $responses[0];
    }
    
    public function getChildren($path)
    {
        $rs = $this->__call("List", array($path));
        return json_decode($rs, true);
    }

    public function __call($name, $args)
    {
        // Build the h5app unified protocol command
        array_unshift($args, strtoupper($name));
        $cmd = sprintf("*%d\r\n%s%s", count($args), implode(array_map(function($arg) {
            return sprintf("$%d\r\n%s", strlen($arg), $arg);
        }, $args), "\r\n"), "\r\n");

        // Add it to the pipeline queue
        $this->queue[] = $cmd;

        return $this->uncork();
    }
    
    private function readResponse()
    {
        // Parse the response based on the reply identifier
        $reply = trim(fgets($this->sock, 512));
        
        switch (substr($reply, 0, 1)) {
            // Error reply
            case '-':
                throw new Exception(trim(substr($reply, 4)));
                break;
            // Inline reply
            case '+':
                $response = substr(trim($reply), 1);
                if ($response === 'OK') {
                    $response = TRUE;
                }
                break;
            // Bulk reply
            case '$':
                $response = NULL;
                if ($reply == '$-1') {
                    break;
                }
                $read = 0;
                $size = intval(substr($reply, 1));
                if ($size > 0) {
                    do {
                        $block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
                        $r = fread($this->sock, $block_size);
                        if ($r === FALSE) {
                            throw new \Exception('Failed to read response from stream');
                        } else {
                            $read += strlen($r);
                            $response .= $r;
                        }
                    } while ($read < $size);
                }
                fread($this->sock, 2); // discard crlf
                break;
            // Multi-bulk reply
            case '*':
                $count = intval(substr($reply, 1));
                if ($count == '-1') {
                    return NULL;
                }
                $response = array();
                for ($i = 0; $i < $count; $i++) {
                    $response[] = $this->readResponse();
                }
                break;
            // Integer reply
            case ':':
                $response = intval(substr(trim($reply), 1));
                break;
            default:
                throw new Exception("Unknown response: {$reply}");
                break;
        }

        return $response;
    }
}
