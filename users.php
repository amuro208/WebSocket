<?php

class WebSocketUser {

  public $socket;
  public $id;
  public $ip;
  public $tcs_id;
  public $headers = array();
  public $handshake = false;

  public $handlingPartialPacket = false;
  public $partialBuffer = "";

  public $sendingContinuous = false;
  public $partialMessage = "";
  
  public $hasSentClose = false;

  function __construct($id, $socket) {
    $this->id = $id;
    $this->socket = $socket;
  }
  
  function indentify(){
	echo "Socket : ".$this->id." : ".$this->ip."\n";
  }
}