<?php

$app->on('connect', function ($context) use ($app) {
	// extract($context);
});

$app->on('login', function ($context) use ($app) {
	extract($context);
	echo "$fd ".$message->username;
	$app->users->login($fd, $message->username);
});

$app->on('close', function ($context) use ($app) {
	extract($context);

	$user = $app->users->logout($fd);
	
});

$app->on('chat', [
	function ($context) use ($app) {
		extract($context);
		sendMessage($server, 'chat',$message);
	}
]);

$app->on('load_history', [
	function ($context) use ($app) {
		extract($context);
		loadHistory($server,$message);
	}
]);


$app->on('list', [
	function ($context) use ($app) {
		extract($context);
		reply($server, $fd, 'list', $app->users->all());
	}
]);


$app->on('messages', [
	function ($context) use ($app) {
		extract($context);
	
		reply($server, $fd, 'messages', 
			$app->messages->orWhere([
				'fd' => $fd,
				'from_fd' => $fd
			])
		);
	}
]);
