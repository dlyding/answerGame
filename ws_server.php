<?php
include_once 'player_match.php';
include_once 'playerToFile.php';
include_once 'mysqlconn.php';       //数据库什么时候建立连接需要认真考虑
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9502);
/*$ws->set(array(
	'worker_num' => 1,
));*/

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
	global $myconn;
	$content = json_decode($frame->data, true);
	switch ($content["type"]) {
		case 'login':
			$name = $content["name"];
			$password = $content["password"];
			//var_dump($name);
			//var_dump($password);
			$strsql = "select name from tb_player where name = \"$name\" and password = \"$password\"";
			//var_dump($strsql);
			$result = mysql_query($strsql, $myconn);
			//var_dump($result);
			$test = mysql_fetch_array($result);
			//var_dump($test);
			if($test == false) {
				$ws->push($frame->fd, "failed");
			}
			else {
				$ws->push($frame->fd, "success");
			}
			break;

		case 'register':
			$name = $content["name"];
			$password = $content["password"];
			$strsql = "select name from tb_player where name = \"$name\"";
			$result = mysql_query($strsql, $myconn);
			$test = mysql_fetch_array($result);
			if($test == true) {
				$ws->push($frame->fd, "failed");
				return;
			}
			//echo "123";
			$strsql = "insert into tb_player(name,password) values(\"$name\", \"$password\")";
			$result = mysql_query($strsql, $myconn);
			if($result == true) {
				$ws->push($frame->fd, "success");
			}
			else {
				$ws->push($frame->fd, "failed");
			}
			break;

		case 'plarerinformation':
			$playerinfo = new player_match($content["playername"], $content["playerlevel"], $frame->fd);

			break;
		case 'playermatch':
			$type = $content["questiontype"];
			$name = $content["playername"];
			$strsql = "select $type from tb_player where name = \"$name\"";
			$result = mysql_query($strsql, $myconn);
			$test = mysql_fetch_array($result);
			//var_dump($test);
			$playerinfo = new player_match($name, $type, $test[0], $frame->fd);	
			var_dump($playerinfo);	
			$tarplayer = playerToFile::searchPlayer($playerinfo);
			var_dump($tarplayer);
			if($tarplayer == null) {
				playerToFile::addPlayer($playerinfo);
			}
			else {
				$ws->push($tarplayer->fd, json_encode($playerinfo));
				$ws->push($playerinfo->fd, json_encode($tarplayer));
			}
			break;
		default:
			# code...
			break;
	}
    
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
	playerToFile::delPlayer($fd);
    echo "client-{$fd} is closed\n";
    //global $myconn;
	//mysql_close($myconn);
});

$ws->start();