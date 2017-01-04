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

class MainLogger{

	public function __construct($path, $debuglevel){
		$this->path = $path;
		$this->debuglevel = $debuglevel;
	}

	public function info($message){
		$this->message("[INFO]", $message);
	}

	public function debug($message, $level = 1){
		if($this->debuglevel > $level){
			$this->message("[DEBUG]", $message);
		}
	}

	public function message($level, $message){
		echo "[MainLogger]".$level." ".$message.PHP_EOL;
	}

}
?>
