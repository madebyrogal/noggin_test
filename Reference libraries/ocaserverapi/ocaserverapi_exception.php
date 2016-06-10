<?php

class OCAServerApi_Exception extends Exception {
    protected $messages = array();

    public function __construct($messages = null, $code = 0) {
        $this->messages = (array)$messages;
        parent::__construct(implode(', ', $this->messages), $code);
    }

    public function getMessages() {
        return $this->messages;
    }
}
