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
