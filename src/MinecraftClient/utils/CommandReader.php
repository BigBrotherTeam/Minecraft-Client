<?php

/*
 *▪   ▄▄▄·       ▄▄· ▄ •▄ ▄▄▄ .▄▄▄▄▄
 *██ ▐█ ▄█▪     ▐█ ▌▪█▌▄▌▪▀▄.▀·•██
 *▐█· ██▀· ▄█▀▄ ██ ▄▄▐▀▀▄·▐▀▀▪▄ ▐█.▪
 *▐█▌▐█▪·•▐█▌.▐▌▐███▌▐█.█▌▐█▄▄▌ ▐█▌·
 *▀▀▀.▀    ▀█▄▀▪·▀▀▀ ·▀  ▀ ▀▀▀  ▀▀▀
 *
 *This program is free software:
 *PocketEdition Packet Analyze.
 *
*/

namespace MinecraftClient\utils;

class CommandReader{

	public function __construct(){
		$this->read = [];
		$this->write = null;
		$this->except = null;
	}

	public function getCommandLine(){
		$this->read[] = STDIN;
		if(stream_select($this->read, $this->write, $this->except, 0, 0) > 0){
		//if(stream_select($this->read, $this->write, $this->except, 0, 200000) > 0){
			$line = trim(fgets(STDIN));
			return $line;
		}
		return null;
	}

}
