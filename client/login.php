<?php
opcache_reset();

require '../vendor/autoload.php';
$username = $_REQUEST["username"];
$password = md5($_REQUEST["password"]);
if(empty($username)){
	$username="sam";
}
if(empty($_REQUEST["password"])){
	$password=md5("sam");
}
try {
	$db = core\DB::getInstance();
	$sql = "select * from hx_user where u_username='" . $username . "' and u_password='" . $password . "'";
	$rst = $db -> fetch_first($sql);
	if ($rst) {
		session_start();
		$user = array("uid" => $rst["u_id"], "username" => $rst["u_username"], "agent" => $rst["u_agent"]);
		$_SESSION['user']=$user;
		
		$query = http_build_query($user);
		if($rst['u_type']==1){
			header("Location:user.php" . "?" . $query);
		}else{
			header("Location:service.php" . "?" . $query);
		}
		
	} else {
		echo "<font color='red'>用户名密码不正确</font><p><a href='index.html'>返回登录页面</a></p>";
	}
} catch(Exception $e) {
	var_dump($e);
}
