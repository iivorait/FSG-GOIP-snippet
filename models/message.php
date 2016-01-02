<?php

namespace FSG;

/**
 * An SMS message value object. ID, receiver and message are used by the Goip 
 * class. The rest are for database storage.
 *
 * @author Iivo Raitahila
 */
class MessageVO {
    private $id;
    private $receiver;
    private $sender;
    private $message;
    private $status;
    private $created;
    
    /**
     * The required fields for GOIP are these ones
     * 
     * @param int $id A (semi)unique ID, valid through the send process
     * @param string $receiver Receiver phone number
     * @param string $message The message itself
     */
    function __construct($id, $receiver, $message) {
        $this->id = $id;
        $this->receiver = $receiver;
        $this->message = $message;
    }
    
    function getId() {
        return $this->id;
    }

    function getReceiver() {
        return $this->receiver;
    }

    function getSender() {
        return $this->sender;
    }

    function getMessage() {
        return $this->message;
    }

    function getStatus() {
        return $this->status;
    }

    function getCreated() {
        return $this->created;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setReceiver($receiver) {
        $this->receiver = $receiver;
    }

    function setSender($sender) {
        $this->sender = $sender;
    }

    function setMessage($message) {
        $this->message = $message;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setCreated($created) {
        $this->created = $created;
    }


}
