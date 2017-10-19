<?php

/**
 *  ______  __         ______               __    __
 * |   __ \|__|.-----.|   __ \.----..-----.|  |_ |  |--..-----..----.
 * |   __ <|  ||  _  ||   __ <|   _||  _  ||   _||     ||  -__||   _|
 * |______/|__||___  ||______/|__|  |_____||____||__|__||_____||__|
 *             |_____|
 *
 * BigBrother plugin for PocketMine-MP
 * Copyright (C) 2014-2015 shoghicp <https://github.com/shoghicp/BigBrother>
 * Copyright (C) 2016- BigBrotherTeam
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author BigBrotherTeam
 * @link   https://github.com/BigBrotherTeam/BigBrother
 *
 */

namespace MinecraftClient\utils;

use MinecraftClient\protocol\PacketAnalyze;
use MinecraftClient\protocol\Client;

class SocketReader{
	private $working = true, $client = null;

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

	public function getClient(){
		return $this->client;
	}

	public function tick(){
		if(!$this->working){
			return false;
		}
		return $this->client->tick();
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
