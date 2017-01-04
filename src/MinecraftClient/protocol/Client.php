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

namespace MinecraftClient\protocol;

use MinecraftClient\utils\Binary;

class Client{
	public $status = "";
	public $endstatus = [];
	public $args = [];
	public $waitstatus = [];

	public function __construct($logger, $username, $serverip, $serverport, $socketreader){
		$this->logger = $logger;
		$this->username = $username;
		$this->serverip = $serverip;
		$this->serverport = $serverport;
		$this->socketreader = $socketreader;
		$this->threshold = null;
	}

	public function receive(){
		echo "receive\n";
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

	public function loginsuccess(){
		if(!$this->start(__FUNCTION__)){
			return;
		}

		$data = $this->receive();
		$offset = 0;
		$pid = Binary::readVarInt($data, $offset);
		echo $pid."\n";
		echo bin2hex(chr($pid))."\n";
		switch($pid){
			case 0x03:

			break;
			case 0x07:

			break;
			case 0x0d://Server Difficulty

			break;
			case 0x18:

			break;
			case 0x1b:

			break;
			case 0x1f:

			break;
			case 0x20:
				$x = Binary::readInt(substr($data, $offset, 4));

				$offset += 4;

				$z = Binary::readInt(substr($data, $offset, 4));

				$offset += 4;

				$GroundUp = Binary::readByte(substr($data, $offset, 1));

				echo "GroundUp: ".$GroundUp."\n";

				$offset += 1;

				$bak = $offset;

				$bitmask = Binary::readVarInt($data, $offset);

				echo "bitmask: ".$bitmask."\n";

				$size = Binary::readVarInt($data, $offset);

				echo "size: ".$size."\n";

				$reason = "Chunk Loaded! (".$x.":".$z.")";
				$payload = Binary::writeVarInt(0x02).Binary::writeVarInt(strlen($reason)).$reason;
				$this->send($payload);

				file_put_contents($x.":".$z.".dat", substr($data, $bak));
			break;
			case 0x23://JoinGame

				
			break;
			case 0x2b://Player Ab
				/*$payload = Binary::writeVarInt(0x04).Binary::writeVarInt(strlen($reason)).$reason;
				$this->send($payload);*/
			break;
			case 0x2d:

			break;
			case 0x43:

			break;
			default:
				echo "Unknown: ".$pid."\n";


				/*$reason = json_encode(["text" => "Log out"]);
				$payload = Binary::writeVarInt(0x1a).Binary::writeVarInt(strlen($reason)).$reason;
				$this->send($payload);*/
				

				$this->finish(__FUNCTION__);
			break;
			case 55:

			break;
		}
	}

	public function joinServer(){
		if(!$this->start(__FUNCTION__)){
			return;
		}
		
		echo "joinServer\n";

		if(isset($this->args["Status"])){
			switch($this->args["Status"]){
				case "Send":
					$data = $this->receive();

					$offset = 0;
					$pid = Binary::readVarInt($data, $offset);
					echo $pid."\n";
					switch($pid){
						case 0x00://Disconnect (login)
							$length = Binary::readVarInt($data, $offset);
							$data = substr($data, $offset, $length);

							$reasontext = json_decode($data, true);
							echo $reasontext."\n";

							$reason = $reasontext["text"];
							if(isset($serverstatus["text"]["extra"])){
								foreach($reasontext["extra"] as $text){
									$reason .= $text["text"];
								}
							}

							echo "Reason: ".$reason."\n\n";
						break;
						case 0x01://Encryption Request
							echo "This software can offline server!\n";
							$this->finish(__FUNCTION__);
						break;
						case 0x02://Login Success
							echo "Login\n";
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
							//var_dump($data);
						break;
					}
				break;
				default:
					echo "Huh?\n";
				break;
			}
		}else{
			$this->args["Status"] = "Send";
			echo "Send\n";

			$payload = Binary::writeVarInt(0x00).Binary::writeVarInt(316).Binary::writeVarInt(strlen($this->serverip)).$this->serverip.
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

		echo "checkServer\n";

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
			echo "Send\n";

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
		}
		if($this->status !== ""){
			call_user_func([$this, $this->status]);
		}
	}

	public function shutdown(){
		$reason = json_encode(["text" => "Log out"]);
		$payload = Binary::writeVarInt(0x1a).Binary::writeVarInt(strlen($reason)).$reason;
		$this->send($payload);

		$this->finish(__FUNCTION__);
	}

}