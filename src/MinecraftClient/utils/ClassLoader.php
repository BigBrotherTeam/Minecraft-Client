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

class ClassLoader{
	private $path = [];

	public function addPath($path){
		$this->path[] = $path;
	}

	public function register(){
		return spl_autoload_register([$this, "loadClass"]);
	}

	public function findClass($name){
		$components = explode("\\", $name);

		$baseName = implode(DIRECTORY_SEPARATOR, $components);

		foreach($this->path as $path){
			if(file_exists($path.DIRECTORY_SEPARATOR.$baseName.".php")){
				return $path.DIRECTORY_SEPARATOR.$baseName.".php";
			}else{
				echo "NotPath: ".$path.DIRECTORY_SEPARATOR.$baseName.".php".PHP_EOL;//Debug
			}
		}
		return null;
	}

	public function loadClass($name){
		$path = $this->findClass($name);
		if($path !== null){
			include($path);
			return true;
		}
		return false;
	}

}
