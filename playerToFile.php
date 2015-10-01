<?php
include_once 'player_match.php';

class playerToFile
{
	static protected $arrayOfPlayer = array();
	static protected $fileOfPlayer = "./player.txt";

	/*function __construct()
	{
		self::$fileOfPlayer = "./player.txt";
		if(file_exists(self::$fileOfPlayer)) {
			$content = file_get_contents(self::$fileOfPlayer);
			$this->$arrayOfPlayer = json_decode($content);
		}
		else {
			$this->$arrayOfPlayer = null;
		}
	}*/

	static function writeToFile()
	{
		$content = json_encode(self::$arrayOfPlayer);
		file_put_contents(self::$fileOfPlayer, $content);
	}

	static function readFromFile()
	{
		if(file_exists(self::$fileOfPlayer)) {
			$content = file_get_contents(self::$fileOfPlayer);
			self::$arrayOfPlayer = json_decode($content);
		}
		else {
			self::$arrayOfPlayer = array();
		}
	}

	static function addPlayer($playerinfo)
	{
		self::readFromFile();
		array_push(self::$arrayOfPlayer, $playerinfo);
		self::writeToFile();
	}

	static function searchPlayer($playerinfo)
	{
		self::readFromFile();
		$targetPlayer = null;
		$target = -1;
		if(!empty(self::$arrayOfPlayer)){
			foreach (self::$arrayOfPlayer as $key => $value) {
			if(($playerinfo->playerlevel == $value->playerlevel) && ($playerinfo->questiontype == $value->questiontype)) {
				$targetPlayer = $value;
				$target = $key;
				break;
			}
		}
		}	
		if($target >= 0) {
			array_splice(self::$arrayOfPlayer, $target, 1);
		}
		self::writeToFile();
		return $targetPlayer;
	}

	static function delPlayer($fd)
	{
		self::readFromFile();
		$target = -1;
		foreach (self::$arrayOfPlayer as $key => $value) {
			if($fd == $value->fd) {
				$target = $key;
				break;
			}
		}
		//var_dump($target);
		if($target >= 0) {
			//echo '1'.PHP_EOL;
			array_splice(self::$arrayOfPlayer, $target, 1);
		}
		//var_dump(self::$arrayOfPlayer);
		self::writeToFile();
	}	
}