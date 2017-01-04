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

class Config{
	protected $path, $content, $overwrite;

	public function __construct($path, $content = [], $overwrite = false){
		$this->path = $path;
		$this->content = $content;
		$this->overwrite = $overwrite;
		if(file_exists($this->path)){
			$this->content = json_decode(file_get_contents($this->path), true);
		}else{
			$this->save();
		}
	}

	public function get($name){
		if(isset($this->content[$name])){
			return $this->content[$name];
		}
		return null;
	}

	public function set($name, $content){
		return $this->content[$name] = $content;
	}

	public function save(){
		file_put_contents($this->path, json_encode($this->content, JSON_PRETTY_PRINT));
	}

}
?>
