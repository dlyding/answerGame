<?php

// 简介：玩家匹配类
// 作者：丁燎原
// 时间：2015年9月20日
// 版本一

class player_match
{
	public $playername;        // 玩家昵称
	public $questiontype;      // 问题类型
	public $playerlevel;       // 玩家该问题等级
	public $fd;                 // 玩家连接号

	function __construct($name, $type, $level, $fd)
	{
		$this->playername = $name;
		$this->questiontype = $type;
		$this->playerlevel = $level;
		$this->fd = $fd; 
	}

}