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

namespace MinecraftClient\protocol;

use MinecraftClient\utils\Binary;

class Client{
	public $status = "";
	public $wait = false;
	public $endstatus = [];
	public $args = [];
	public $waitstatus = [];
	public $packetname;

	public function __construct($logger, $username, $serverip, $serverport, $socketreader){
		$this->logger = $logger;
		$this->username = $username;
		$this->serverip = $serverip;
		$this->serverport = $serverport;
		$this->socketreader = $socketreader;
		$this->threshold = null;

		$this->packetname = file(__DIR__."/packetname.txt");
	}

	public function receive(){
		//echo "receive\n";
		$len = Binary::readVarIntSession($this->socketreader, $offset);
		$buffer = $this->socketreader->read($len);
		if($this->threshold !== null){
			$offset = 0;//Reset
			$dataLength = Binary::readVarInt($buffer, $offset);
			if($dataLength !== 0){
				if($dataLength < $this->threshold){
					echo "Invalid compression threshold\n";
				}else{
					$buffer = zlib_decode(substr($buffer, $offset));
					$offset = 0;
				}
			}else{
				$buffer = substr($buffer, $offset);
				$offset = 0;
			}
		}

		return $buffer;
	}

	public function send($data){
		if($this->threshold === null){
			$this->socketreader->write(Binary::writeVarInt(strlen($data)) . $data);
		}else{
			$dataLength = strlen($data);
			if($dataLength >= $this->threshold){
				$data = zlib_encode($data, ZLIB_ENCODING_DEFLATE, 7);
			}else{
				$dataLength = 0;
			}

			$data = Binary::writeVarInt($dataLength) . $data;
			$this->socketreader->write(Binary::writeVarInt(strlen($data)) . $data);
		}
	}

	public function start($function){
		if(!isset($this->endstatus[$function]) and $this->status !== $function){
			if($this->status !== ""){
				$this->waitstatus[] = $function;
				return false;
			}else{
				$this->status = $function;
			}
		}
		return true;
	}

	public function finish($function){
		//if(){
			$this->args = [];
			$this->status = "";
			$this->endstatus[$function] = 0;
		//}
	}

	public function say(string $text){
		$payload = Binary::writeVarInt(0x02).Binary::writeVarInt(strlen($text)).$text;

		$this->send($payload);
	}

	public function say(string $text){
		$payload = Binary::writeVarInt(0x02).Binary::writeVarInt(strlen($text)).$text;

		$this->send($payload);
	}

	public function loginsuccess(){
		if(!$this->start(__FUNCTION__)){
			return;
		}

		$data = $this->receive();
		$offset = 0;
		$pid = Binary::readVarInt($data, $offset);

		/*if($pid !== 54 and $pid !== 40 and $pid !== 39 and $pid !== 38){
			echo $this->packetname[$pid]."\n";
		}*/

		switch($pid){
			case 0x0f:
				$length = Binary::readVarInt($data, $offset);
				$data = substr($data, $offset, $length);

				$text = json_decode($data, true);
				var_dump($text);
			break;
			case 0x1f://keep alive
				$keepaliveid = Binary::readLong(substr($data, $offset, 8));
				$offset += 8;

				$payload = Binary::writeVarInt(0x0b).Binary::writeLong($keepaliveid);
				$this->send($payload);
			break;
			case 0x1a://disconnect (play)
				$length = Binary::readVarInt($data, $offset);
				$data = substr($data, $offset, $length);

				$reasontext = json_decode($data, true);
				var_dump($reasontext);

				$this->finish(__FUNCTION__);
			break;
			case 0x4d:
				//file_put_contents("Advancements.dat", substr($data, $offset));
			break;
			case 0x17:
				//file_put_contents("CraftingBookData.dat", substr($data, $offset));
			break;
			case 0x31:
				file_put_contents("UnlockRecipes.dat", substr($data, $offset));
			break;
			default:
			break;
		}
		if($this->wait){
			$this->wait = false;
		}else{
			$this->wait = true;
		}
	}

	public function joinServer(){
		if(!$this->start(__FUNCTION__)){
			return;
		}

		if(isset($this->args["Status"])){
			switch($this->args["Status"]){
				case "Send":
					$data = $this->receive();

					$offset = 0;
					$pid = Binary::readVarInt($data, $offset);
					switch($pid){
						case 0x00://Disconnect (login)
							$length = Binary::readVarInt($data, $offset);
							$data = substr($data, $offset, $length);

							$reasontext = json_decode($data, true);

							$reason = $reasontext["translate"];
							switch($reason){
								case "multiplayer.disconnect.outdated_client":
									$reason = "This is oldClient....";
								break;
							}

							if(isset($serverstatus["translate"]["extra"])){
								foreach($reasontext["extra"] as $text){
									$reason .= $text["text"];
								}
							}

							echo "Reason: ".$reason."\n\n";

							$this->finish(__FUNCTION__);
						break;
						case 0x01://Encryption Request
							echo "This software can offline server!\n";
							$this->finish(__FUNCTION__);
						break;
						case 0x02://Login Success
							$length = Binary::readVarInt($data, $offset);
							$uuid = substr($data, $offset, $length);

							echo "uuid: ".$uuid."\n";

							$offset += $length;

							$length = Binary::readVarInt($data, $offset);
							$username = substr($data, $offset, $length);

							$offset += $length;

							echo "username: ".$username."\n\n";

							$this->finish(__FUNCTION__);

							$this->loginsuccess();
						break;
						case 0x03://Set Compression
							$threshold = Binary::readVarInt($data, $offset);
							$this->threshold = $threshold;
						break;
						default:
							echo "Unknown: ".$pid."\n";
							$this->finish(__FUNCTION__);
						break;
					}
				break;
				default:
					echo "Huh?\n";
				break;
			}
		}else{
			$this->args["Status"] = "Send";

			$payload = Binary::writeVarInt(0x00).Binary::writeVarInt(340).Binary::writeVarInt(strlen($this->serverip)).$this->serverip.
						Binary::writeShort(25565).Binary::writeVarInt(2);

			$this->send($payload);

			$payload = Binary::writeVarInt(0x00).Binary::writeVarInt(strlen($this->username)).$this->username;

			$this->send($payload);
		}
	}

	public function checkServer(){
		if(!$this->start(__FUNCTION__)){
			return;
		}

		if(isset($this->args["Status"])){
			switch($this->args["Status"]){
				case "Send":
					$data = $this->receive();

					$offset = 0;
					$pid = Binary::readVarInt($data, $offset);
					$length = Binary::readVarInt($data, $offset);
					$data = substr($data, $offset, $length);
					$serverstatus = json_decode($data, true);

					$description = $serverstatus["description"]["text"];
					if(isset($serverstatus["description"]["extra"])){
						foreach($serverstatus["description"]["extra"] as $text){
							$description .= $text["text"];
						}
					}

					echo "\n--- Server Status ---\n".
						"description: ".$description."\n".
						"now/max players: ".$serverstatus["players"]["online"]." / ".$serverstatus["players"]["max"]."\n".
						"version: \"".$serverstatus["version"]["name"]."\" protocolnumber: ".$serverstatus["version"]["protocol"]."\n\n";

					$this->socketreader->reconnect();

					$this->finish(__FUNCTION__);
				break;
				default:
					echo "Huh?\n";
				break;
			}
		}else{
			$this->args["Status"] = "Send";

			$payload = Binary::writeVarInt(0x00).Binary::writeVarInt(316).Binary::writeVarInt(strlen($this->serverip)).$this->serverip.
						Binary::writeShort(25565).Binary::writeVarInt(1);

			$this->send($payload);

			$payload = Binary::writeVarInt(0x00);

			$this->send($payload);
		}
	}

	public function tick(){
		if($this->status === ""){
			$this->status = array_shift($this->waitstatus);
			if($this->status === null){
				$this->status = "";
			}
			$this->wait = true;
		}
		if($this->status !== ""){
			call_user_func([$this, $this->status]);
		}
		return $this->wait;
	}

	public function shutdown(){
		/*$reason = json_encode(["text" => "Log out"]);
		$payload = Binary::writeVarInt(0x1a).Binary::writeVarInt(strlen($reason)).$reason;
		$this->send($payload);*/

		$this->finish(__FUNCTION__);
	}

}