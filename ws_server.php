<?php
include_once 'player_match.php';
include_once 'playerToFile.php';
include_once 'mysqlconn.php';       //数据库什么时候建立连接需要认真考虑
include_once 'question.php';
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
				//这种随机提取方法效率不高，需以后改进
				$questionArray = array();
				$strsql = "SELECT * FROM tb_question where type = \"$type\" order by rand() limit 0,3";
				$result = mysql_query($strsql, $myconn);
				while($test = mysql_fetch_array($result)) {
					$questioninfo = new question(urlencode($test[1]), urlencode($test[2]), 
												urlencode($test[3]), urlencode($test[4]), 
												urlencode($test[5]), urlencode($test[6]), $test[7]);
					array_push($questionArray, $questioninfo);
				}
				/*while ($test) {
					$questioninfo = new question();
				}
				var_dump($test);
				var_dump($test);*/
				var_dump($questionArray);

				$infoarray = array();
				array_push($infoarray, $playerinfo);
				array_push($infoarray, $questionArray);

				$ws->push($tarplayer->fd, urldecode(json_encode($infoarray)));
				$infoarray = array();
				array_push($infoarray, $tarplayer);
				array_push($infoarray, $questionArray);
				$ws->push($playerinfo->fd, urldecode(json_encode($infoarray)));
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