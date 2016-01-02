<?php

namespace FSG;

/**
 * A class for interfacing with a GOIP GSM Gateway
 *
 * @author Iivo Raitahila
 */
class Goip {

    private $socket;
    private $address;
    private $port;
    private $password;
    
    /**
     * Initialize the connection to GOIP
     * 
     * @param string $address The IP address of the GOIP
     * @param int $port The port of the corresponding SIM card (ex. 9991)
     * @param string $password The password set in GOIP management
     */
    function __construct($address, $port, $password) {
        $this->address = $address;
        $this->port = $port;
        $this->password = $password;
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }
        
    /**
     * Closes the socket
     */
    public function close() {
        socket_close($this->socket);
    }
    
    /**
     * Instructs the GOIP to send an SMS message
     * 
     * @param \FSG\MessageVO $message Message to be sent
     * @param type $socket
     * @param string $address
     * @param type $port
     * @param type $password
     * @return string
     */
    public function sendSMS(MessageVO $message) {
        $request = $this->bulkSMSRequest($message);
        if($request !== true) {
            $this->endRequest($message);
            return $request;
        }
        $authentication = $this->authenticationRequest($message);
        if($authentication !== true) {
            $this->endRequest($message);
            return $authentication;
        }
        $send = $this->submitNumberRequest($message);
        if($send !== true) {
            $this->endRequest($message);
            return $send;
        }
        return $this->endRequest($message);
    }
    
    protected function bulkSMSRequest(MessageVO $message) {
        //GOIP message max length is 3000 bytes
        $cutmessage = mb_strcut($message->getMessage(), 0, 3000);
        
        $msg = "MSG " . $message->getId() . " " . strlen($cutmessage) . " " . $cutmessage . "\n";
        
        socket_sendto($this->socket, $msg, strlen($msg), 0, $this->address, $this->port);

        return $this->listenToReply($message, "BulkSMSConfirm", "PASSWORD");
    }
    
    protected function authenticationRequest(MessageVO $message) {
        $msg = "PASSWORD " . $message->getId() . " " . $this->password;
        socket_sendto($this->socket, $msg, strlen($msg), 0, $this->address, $this->port);
        
        return $this->listenToReply($message, "AuthenticationConfirm", "SEND");
    }
    
    protected function submitNumberRequest(MessageVO $message) {
        $msg = "SEND " . $message->getId() . " 1 " . $message->getReceiver();

        for($i = 1; $i <= 30; $i++) {
            socket_sendto($this->socket, $msg, strlen($msg), 0, $this->address, $this->port);
            socket_recvfrom($this->socket, $buffer, 2048, 0, $fromip, $fromport);
            if(substr($buffer, 0, (3 + strlen($message->getId()))) === "OK " . $message->getId()) {
                return true;
            } else if(substr($buffer, 0, (6 + strlen($message->getId()))) === "ERROR " . $message->getId()) {
                return "Error in SubmitNumberStatus: " . $buffer . " - maybe the phone number is incorrect";
            } 
            sleep(1);
        }

        return "Timeout in SubmitNumberStatus";
    }
    
    protected function endRequest(MessageVO $message) {
        $msg = "DONE " . $message->getId();
        socket_sendto($this->socket, $msg, strlen($msg), 0, $this->address, $this->port);

        return $this->listenToReply($message, "EndConfirm", "DONE");
    }
    
    /**
     * Listens to the reply from the GOIP
     * 
     * @param \FSG\MessageVO $message
     * @param string $phase Phase name for error reporting
     * @param string $expect The success message from GOIP
     * @return true if success or error message
     */
    private function listenToReply(MessageVO $message, $phase, $expect) {
        for($i = 1; $i <= 30; $i++) {
            socket_recvfrom($this->socket, $buffer, 2048, 0, $fromip, $fromport);
            
            if(substr($buffer, 0, (6 + strlen($message->getId()))) === "ERROR " . $message->getId()) {
                return "Error in " . $phase . ": " . $buffer;
            } else if(substr($buffer, 0, (1 + strlen($expect) + strlen($message->getId()))) === $expect . " " . $message->getId()) {
                return true;
            }
        }
        return "Timeout in " . $phase;
    }
}
