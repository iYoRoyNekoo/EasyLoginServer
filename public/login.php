<?php
require '../config.php';

if(!$service_status){ //总开关，定义在config.php
	echo json_encode($errmsg[1]);
	return;
}

$conn=mysqli_connect($mysql_server_name,$mysql_username,$mysql_password,$mysql_database);
if(!$conn){//连接数据库
	echo json_encode($errmsg[2]);
	return;
}


//判断操作
$action=isset($_REQUEST['action'])?$_REQUEST['action']:'';

if($action=='login')login(true);
elseif($action=='reg')register();
elseif($action=='verify')verify();
elseif($action=='gencode')gencode(true);
elseif($action=='modify')modify();
else undefined();

cleanup();

/*****函数定义*****/

//mode:为false时不输出内容
function gencode($mode){
	if(!(isset($_REQUEST['name'])||isset($_REQUEST['email']))){
		echo json_encode($errmsg[4]);
		return;
	}

	global $conn;
	mysqli_query($conn,

}

function login($mode){
	if(!((isset($_REQUEST['name'])||isset($_REQUEST['email']))&&isset($_REQUEST['password']))){
		echo json_encode($errmsg[4]);
		return;
	}
}

function register(){
	if(!(isset($_REQUEST['name'])&&isset($_REQUEST['email'])&&isset($_REQUEST['password']))){
		echo json_encode($errmsg[4]);
		return;
	}
}

function verify(){
	if(!((isset($_REQUEST['name'])||isset($_REQUEST['email']))&&isset($_REQUEST['code']))){
		echo json_encode($errmsg[4]);
		return;
	}
}

function modify(){

}

function undefined(){//未定义操作
	echo json_encode($errmsg[3]);
	return;
}

function cleanup(){
	global $conn;
	mysqli_close($conn);
}

?>
