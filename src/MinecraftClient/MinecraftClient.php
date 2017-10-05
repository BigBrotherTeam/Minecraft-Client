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

namespace MinecraftClient;

use MinecraftClient\utils\Config;
use MinecraftClient\utils\MainLogger;
use MinecraftClient\utils\CommandReader;
use MinecraftClient\utils\SocketReader;

class MinecraftClient{
	protected $path, $logger;
	protected static $interface;

	public function getPath(){
		return $this->path;
	}

	public function getLogger(){
		return $this->logger;
	}

	public function getDebugLevel(){
		return $this->config->get("debuglevel");
	}

	public static function getInterface(){
		return self::$interface;
	}

	public function __construct($path){
		set_error_handler(function($severity, $message, $file, $line){
			echo "ErrorLog: ".$message." : ".$file." : ".$line."\n";
			$debug = debug_backtrace();
			if(isset($debug[1]["file"]) and isset($debug[1]["function"])){
				echo "1: ".substr(strrchr($debug[1]["file"], "/"), 1).": ".$debug[1]["function"]."\n";
			}
			if(isset($debug[2]["file"]) and isset($debug[2]["function"])){
				echo "2: ".substr(strrchr($debug[2]["file"], "/"), 1).": ".$debug[2]["function"]."\n";
			}
			if(isset($debug[3]["file"]) and isset($debug[3]["function"])){
				echo "3: ".substr(strrchr($debug[3]["file"], "/"), 1).": ".$debug[3]["function"]."\n";
			}
			if(isset($debug[4]["file"]) and isset($debug[4]["function"])){
				echo "4: ".substr(strrchr($debug[4]["file"], "/"), 1).": ".$debug[4]["function"]."\n";
			}
		});

		$this->path = $path;
		self::$interface = $this;

		$this->config = new Config($this->path.DIRECTORY_SEPARATOR . "config.json", [
			"username" => "Steve",
			"serverip" => "0.0.0.0",
			"serverport" => "25565",
			"debuglevel" => 0,
		]);
		$this->config->save();

		$this->logger = new MainLogger($this->path, $this->config->get("debuglevel"));

		$this->logger->info("MinecraftClient starting now...");

		$this->working = true;

		$this->commandreader = new CommandReader();
		$this->socketreader = new SocketReader($this->logger, $this->config->get("username"), $this->config->get("serverip"), $this->config->get("serverport"));
		if(!$this->socketreader->isworking()){
			$this->shutdown();
			return;
		}

		$this->logger->info("MinecraftClient start!");

		echo "\x1b]0;MinecraftClient running!\x07";

		$this->tick();
	}

	public function tick(){
		while($this->working){
			//$this->getCommandLine();
			for($i = 0; $i <= 100000; $i++){
				if(!$this->working){
					return;
				}
				if($this->socketreader->tick()){
					$this->getCommandLine();
				}
			}
		}
	}

	public function getCommandLine(){
		$line = $this->commandreader->getCommandLine();
		if($line !== null){
			$line = explode(" ", $line);
			switch($line[0]){
				case "stop":
				case "shutdown":
					$this->shutdown();
				break;
				case "help":
					if(isset($line[1])){
						switch($line[1]){
							case "stop":
							case "shutdown":
								$this->logger->info("解析を終了します");
							break;
						}
					}else{
						$this->logger->info("使用できるコマンド\n-stop\n-shutdown : 解析を終了します");
					}
				break;
				default:
					$this->logger->info("UnknownCommand: ".$line[0]."");
				break;
			}
		}
	}

	public function shutdown(){
		$this->working = false;
		$this->config->save();
		$this->socketreader->shutdown();
		$this->logger->info("Shutdown a system now...");
	}

}