#!/usr/local/bin/php -q
<?php
/**
 * Listens to GOIP messages in a specific address and port (set in GOIP 
 * management) and responds to keepalive messages. This functionality is 
 * NOT required for sending SMS messages.
 * 
 * Usage: php keepalive.php SERVERIPADDRESS PORT
 * Use GNU Screen or similar to keep the script running on your server. On 
 * Windows just leave the terminal window open.
 * 
 * @author Iivo Raitahila
 */

error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing to see what we're getting as it comes in. */
ob_implicit_flush();

$address = $argv[1];
$port = $argv[2];

if (($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
    exit("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . PHP_EOL);
}

if (socket_bind($sock, $address, $port) === false) {
    exit("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . PHP_EOL);
}

echo "Start listening for GOIP messages" . PHP_EOL;

while(true) {
    socket_recvfrom($sock, $buffer, 2048, 0, $from, $port);

    echo "Received " . $buffer . " from remote address " . $from . " and remote port " . $port . PHP_EOL;
    
    $csv = str_replace(":", ";", $buffer);
    $messageArray = str_getcsv($csv, ";");
    
    if($messageArray[0] == "req") { //this is a keepalive message that needs acking
        $msg = "reg:" . $messageArray[1] . ";status:200;";
        socket_sendto($sock, $msg, strlen($msg), 0, $from, $port);
        echo "Sent " . $msg . PHP_EOL;
    }
}
