# About

This small PHP program sends SMS messages to a GOIP GSM Gateway. The messages 
are received through a simple restful API.

This is a standalone code snippet from a larger software project.

GOIP documentation (doc/goip_sms_Interface_en.pdf) is property of someone else,
most likely the GOIP manufacturer.

# System requirements
- A GOIP SMS Gateway
- Web server with PHP support (for example Apache and PHP 5.6)
- CLI access for the keepalive.php (not mandatory)

# Installation

1. Copy the project to a web server that has IP access to the GOIP
2. Adjust GOIP's SMS server settings (see screenshot in /doc)
3. Run keepalive.php to catch the port number (see screenshot in /doc)
4. Rename settings_dist.php as settings.php
5. Fill the blanks in settings.php
6. Start sending SMS messages through the API

# Usage

## Sending messages

To send an SMS message, send JSON data as HTTP POST to the send.php

Example message as the content of POST https://server.example/send.php:
```
{
    "receiver": "0121212123",
    "message": "Hello World!"
}
```

You may also set "id": 1234 (where 1234 is a unique ID) if you want to avoid 
collisions with multiple simultaneous sendings. Read more from the GOIP 
documentation about $sendid in chapter 3.

The request returns an HTTP status code 200 if sent was successful and code 400 
with an error message if the sending failed or a timeout occured.

## GOIP keep alive

keepalive.php listens to GOIP messages in a specific address and port (set in 
GOIP management) and responds to keepalive messages. This functionality is 
**NOT required** for sending SMS messages.

Usage: php keepalive.php SERVERIPADDRESS PORT

The IP address is the server's address that runs the keepalive.php as it binds 
to a specific address. Adjust the IP address and port accordingly with the
GOIP management. See screenshots from the doc directory. 

Use GNU Screen or similar to keep the script running on your server. On 
Windows just leave the terminal window open.
