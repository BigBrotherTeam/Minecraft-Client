<?php

/*
 *▪   ▄▄▄·       ▄▄· ▄ •▄ ▄▄▄ .▄▄▄▄▄
 *██ ▐█ ▄█▪     ▐█ ▌▪█▌▄▌▪▀▄.▀·•██
 *▐█· ██▀· ▄█▀▄ ██ ▄▄▐▀▀▄·▐▀▀▪▄ ▐█.▪
 *▐█▌▐█▪·•▐█▌.▐▌▐███▌▐█.█▌▐█▄▄▌ ▐█▌·
 *▀▀▀.▀    ▀█▄▀▪·▀▀▀ ·▀  ▀ ▀▀▀  ▀▀▀
 *
 *This program is free software:
 *ComputerEdition Packet Analyze.
 *
*/

namespace MinecraftClient\utils;

use MinecraftClient\protocol\PacketAnalyze;
use MinecraftClient\protocol\Client;

class SocketReader{
	private $working = true;

	public function __construct($logger, $username, $serverip, $serverport){
		$this->logger = $logger;
		$this->serverip = gethostbyname($serverip);
		$this->serverport = $serverport;
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(@socket_connect($this->socket, $this->serverip, $this->serverport) === true){
			$this->logger->debug("Connect to ".$this->serverip." : ".$this->serverport."");
		}else{
			$this->working = false;
			$this->logger->info("SocketError");
		}

		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, [
			"sec" => 5,
			"usec" => 0
		]);
		socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, [
			"sec" => 5,
			"usec" => 0
		]);
		
		if($this->working){
			$this->client = new Client($this->logger, $username, $serverip, $serverport, $this);
			$this->client->checkServer();
			$this->client->joinServer();
		}
	}

	public function reconnect(){
		socket_close($this->socket);

		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(@socket_connect($this->socket, $this->serverip, $this->serverport) === true){
			$this->logger->debug("Connect to ".$this->serverip." : ".$this->serverport."");
		}else{
			$this->working = false;
			$this->logger->info("SocketError");
		}

		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, [
			"sec" => 5,
			"usec" => 0
		]);
		socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, [
			"sec" => 5,
			"usec" => 0
		]);
	}

	public function isworking(){
		return $this->working;
	}

	public function tick(){
		if(!$this->working){
			return;
		}
		$this->client->tick();
	}

	public function write($data){
		return @socket_write($this->socket, $data);
	}

	public function read($len){
		return @socket_read($this->socket, $len, PHP_BINARY_READ);
	}

	public function shutdown(){
		$this->client->shutdown();
		$this->working = false;
		socket_close($this->socket);
	}

}
