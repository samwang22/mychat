<?php

function sendMessage($server, $type, $msg)
{
	antixss($msg);
	global $app;

	$user = $app->users->getByUsername($msg->to_name);
	$read_time=0;
	if($user['fd']){
		$read_time=time();
	}
	// 记录消息
	$app->messages->save($msg->content,$msg->from_name,$msg->to_name,$type,$read_time); 
	
	if($user['fd']){
		$server->push($user['fd'], json_encode([
			$type,
			[
				'from_name' => $msg->from_name,
				'content' => $msg->content
			]
		]));
	}
}

function loadHistory($server,$msg){
	antixss($msg);
	global $app;
	$user = $app->users->getByUsername($msg->to_name);
	// 记录消息
	$historyContent=$app->messages->getHistoryContent($msg->from_name,$msg->to_name,$user["login_time"]); 
	
	
	if($user['fd']){
		$server->push($user['fd'], json_encode([
			"load_history",$historyContent
		]));
	}
}

function service($server,$type,$msg){
	antixss($msg);
	global $app;

	$user = $app->users->getByUsername($msg->username);
	// 记录消息
	$app->service->orSave($msg->username,""); 
	$count=$app->service->selectCurrFrontWaiters($msg->username);
	
	if($user['fd']){
		$server->push($user['fd'], json_encode([
			$type,
			[
				'from_name' => "客服服务",
				'content' => "当前等待用户".$count.'位'
			]
		]));
	}
}
function receive($server,$type,$msg){
	antixss($msg);
	global $app;

	$servicer = $app->users->getByUsername($msg->username);
	$_user=$app->service->receiveUser();
	if(empty($_user)){
		$server->push($servicer['fd'], json_encode([
			"system",
			[
				'from_name' =>"系统消息",
				'content' => "当前没有可接待用户！"
			]
		]));
		return;
	}
	
	//更新service表状态
	$app->service->updateStatusName($msg->username,$_user["s_id"]); 
	$content="您好,我是客服".$servicer['u_username']."请问有什么可以帮到您?";
	// 记录消息
	$app->messages->save($content,$servicer['u_username'],$_user['u_username'],"service",0); 
	//发送给会员消息
		$server->push($_user['fd'], json_encode([
			$type,
			[
				'from_name' => $servicer['u_username'],
				'content' => $content
			]
		]));
	//给客服发送消息
		$server->push($servicer['fd'], json_encode([
			$type,
			[
				'to_name' => $_user['u_username'],
				'from_name' => $servicer['u_username'],
				'content' => $content
			]
		]));
}


function antixss($message) {		//'/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/,'/meta/'','/xml/','/base/',
	$ra=Array('/script/','/javascript/','/vbscript/','/expression/','/applet/','/blink/','/link/','/style/','/embed/','/object/','/frame/','/layer/','/bgsound/','/onload/','/onunload/','/onchange/','/onsubmit/','/onreset/','/onselect/','/onblur/','/onfocus/','/onabort/','/onkeydown/','/onkeypress/','/onkeyup/','/onclick/','/ondblclick/','/onmousedown/','/onmousemove/','/onmouseout/','/onmouseover/','/onmouseup/','/onunload/');
	$value=$message;
	if (!is_numeric($value)){
		if (!get_magic_quotes_gpc()) {
			$value=@addslashes($value);
		}
		$value = preg_replace($ra,'',$value);
		$message =@htmlspecialchars(strip_tags($value),ENT_NOQUOTES);
	}
}