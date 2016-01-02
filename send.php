<?php
/*
 * Usage: send JSON data as HTTP POST
 * example message:
 * {
 *    "receiver": "0121212123",
 *    "message": "Hello World!"
 * }
 * You may also set "id": 1234 (where 1234 is a unique ID) if you want to 
 * avoid collisions with multiple simultaneous sendings
 * 
 * @author Iivo Raitahila
 */

error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

require_once './models/message.php';
require_once './classes/goip.php';
$settings = require 'settings.php';

if($_SERVER['REQUEST_METHOD'] != "POST") {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: POST');
    exit("405 Method Not Allowed");
}

$data = json_decode(file_get_contents('php://input'));

if (is_null($data) || json_last_error() != JSON_ERROR_NONE) {
    header('HTTP/1.1 400 Bad Request');
    die("400 Bad Request, error with JSON-data decoding");
}

$message = new FSG\MessageVO(rand(1000, 9999), $data->receiver, $data->message);

if(!empty($data->id)) {
    $message->setId($data->id);
}

$goip = new FSG\Goip($settings['goipAddress'], $settings['goipPort'], $settings['goipPassword']);

$result = $goip->sendSMS($message);

if($result === true) {
    header('HTTP/1.1 200 OK');
} else {
    header('HTTP/1.1 400 Bad Request');
}

echo $result;

$goip->close();