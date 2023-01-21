<?php
require '../config.php';
require '../smtp.php';

if(!$service_status){ //总开关，定义在config.php
	echo json_encode($errmsg[1]);
	return;
}

$conn = mysqli_connect($mysql_server_host, $mysql_username, $mysql_password, $mysql_database);
if(!$conn){//连接数据库
	echo json_encode($errmsg[2]);
	return;
}


//判断操作
$action= isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if($action == 'login')login(true);
elseif($action == 'reg')register();
elseif($action == 'verify')verify();
elseif($action == 'gencode')gencode(true);
elseif($action == 'modify')modify();
else undefined();

cleanup();

/*****函数定义*****/

function query_sql($sql){
	global $conn, $debug_mode;
	$query_res = mysqli_query($conn,$sql);
	if($debug_mode){
		echo "//Debug:SQL:$sql".PHP_EOL;
		if(!$query_res)
			echo "//Warn:query returned error:".mysqli_error($conn).PHP_EOL;
	}
	return $query_res;
}

function real_ip(){
    if(isset($_SERVER)){
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $arr = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);

	    foreach($arr as $ip){
                $ip = trim($ip);
		
		if($ip!='unknow'){
                    $realip=$ip;
                    break;
		}
            }
	}elseif(isset($_SERVER['HTTP_CLIENT_IP']))
	    $realip=$_SERVER['HTTP_CLIENT_IP'];
	elseif(isset($_SERVER['REMOTE_ADDR']))
            $realip=$_SERVER['REMOTE_ADDR'];
	else
	    $realip='0.0.0.0';
    }elseif(getenv('HTTP_X_FORWARDED_FOR'))
	$realip=getenv('HTTP_X_FORWARDED_FOR');
    elseif(getenv('HTTP_CLIENT_IP'))
	$realip=getenv('HTTP_CLIENT_IP');
    else
	$realip=getenv('REMOTE_ADDR');

    preg_match('/[\\d\\.]{7,15}/',$realip,$onlineip);
    $realip=(!empty($onlineip[0])?$onlineip[0]:'0.0.0.0');
    return $realip;
}


/*****action处理*****/

function gencode($mode){//mode:为false时不输出内容
	global $errmsg;
	if( !(isset($_REQUEST['name']) || isset($_REQUEST['email'])) ){
		if($mode)echo(json_encode($errmsg[4]));
		return 4;
	}

	$use_name = isset($_REQUEST['name']);
	$auth = '';
	$email = '';
	$sql = "delete from verify_codes where timestampdiff(second,overtime,now()) < 0";
	$query_res = query_sql($sql);//删除已经超时的code
	if(!$query_res){
		if($mode)echo(json_encode($errmsg[5]));
		return 5;
	}

	if($use_name) $name = $auth = $_REQUEST['name'];//判断传入选项
	else $email = $auth = $_REQUEST['email'];

	$auth = base64_encode($auth);//转义防注入

	if($use_name) $sql = "select name,verify,email from users where name='$auth'";
	else $sql = "select name,verify,email from users where email='$auth'";

	$query_res = query_sql($sql);//检索用户

	if(!$row = mysqli_fetch_array($query_res)){//sql查出0rows
		if($mode)echo(json_encode($errmsg[6]));
		return 6;
	}

	if($row['verify'] == '1'){//已验证的用户
		if($mode)echo(json_encode($errmsg[7]));
		return 7;
	}

	if($use_name) $email = $row['email'];//从数据库比对获取email
	else $name = $row['name'];
	
	global $code_overtime;
	$code = rand(100000, 999999);
    $overtime = date('Y-m-d H:i:s', strtotime($code_overtime));

	$sql = "insert into verify_codes values('$code','".base64_encode($name)."','$overtime')";
	$query_res = query_sql($sql);

	if(!$query_res){
		if($mode)echo(json_encode($errmsg[5]));
		return 5;
	}

	global $smtpuser, $smtppass, $smtpserver, $smtpserverport;
	global $smtpusermail, $mailtitle, $mailcontenthead, $mailcontentfoot, $mailtype, $mailsender;

	$smtp = new Smtp($smtpuser, $smtppass, $smtpserver, $smtpserverport, true);
    $smtp->debug = false;
	$smtp->sendmail(base64_decode($email), $smtpusermail, $mailtitle, $mailcontenthead . $code . $mailcontentfoot, $mailtype, '', '', '', $mailsender, '');

	if($mode)echo(json_encode(array('result'=>0,'msg'=>'OK')));
	return 0;

}

function login($mode){//mode:为false时不输出内容
	global $errmsg, $token_overtime;
	if(!((isset($_REQUEST['name'])||isset($_REQUEST['email']))&&isset($_REQUEST['password']))){
		if($mode)echo(json_encode($errmsg[4]));
		return 4;
	}

	$sql = 'delete from tokens where timestampdiff(second,overtime,now()) < 0';//删除已超时的token
	$query_res = query_sql($sql);
	if(!$query_res){
		if($mode)echo(json_encode($errmsg[5]));
		return 5;
	}

	$use_name = isset($_REQUEST['name']);
	if($use_name) $auth = $_REQUEST['name'];
	else $auth = $_REQUEST['email'];
	$auth = base64_encode($auth);
	$password = base64_encode(md5($_REQUEST['password']));

	if($use_name)$sql = "select id,passwd from users where verify=1 and enable=1 and name='$auth' and passwd='$password'";//从数据库中已验证、未封禁的用户中选出符合auth条件的
	else $sql = "select id,passwd from users where verify=1 and enable=1 and email='$auth' and passwd='$password'";
	$query_res = query_sql($sql);
	if(!$query_res){
		if($mode)echo(json_encode($errmsg[5]));
		return 5;
	}

	if(!$row = mysqli_fetch_array($query_res)){//用户名或密码错误
		if($mode)echo(json_encode($errmsg[8]));
		return 8;
	}

	if($row['passwd'] != $password){//用户名或密码错误
		if($mode)echo(json_encode($errmsg[8]));
		return 8;
	}

	$id = $row['id'];
	$ip = real_ip();
	$sql = "select token from tokens where id=$id and ip='$ip'";
	$query_res = query_sql($sql);
	if(!$query_res){
		if($mode)echo(json_encode($errmsg[5]));
		return 5;
	}

	if($row = mysqli_fetch_array($query_res)){
		$token = base64_decode($row['token']);
		$sql = "delete from tokens where id=$id and ip='$ip'";
		$query_res = query_sql($sql);
		if(!$query_res){
			if($mode)echo(json_encode($errmsg[5]));
			return 5;
		}
	}
	else {
		$hashkey = $id . rand() . time();
		$token = md5($hashkey);
	}
	$date = date('Y-m-d H:i:s', strtotime($token_overtime));
	$sql = "insert into tokens (id,token,ip,overtime) values($id,'".base64_encode($token)."','$ip','$date')";
	$query_res = query_sql($sql);
	if(!$query_res){
		if($mode)echo(json_encode($errmsg[5]));
		return 5;
	}

	if($mode)echo(json_encode(array('result'=>0,'msg'=>'OK','token'=>$token)));
	return;

}

function register(){
	global $errmsg;
	if(!(isset($_REQUEST['name'])&&isset($_REQUEST['email'])&&isset($_REQUEST['password']))){
		echo(json_encode($errmsg[4]));
		return;
	}
	$email = base64_encode($_REQUEST['email']);
    $name = base64_encode($_REQUEST['name']);
    $password = base64_encode(md5($_REQUEST['password']));

	$sql = "select id from users where name='$name' or email='$email'";
	$query_res = query_sql($sql);
	if(!$query_res){
		echo(json_encode($errmsg[5]));
		return;
	}
	if($row = mysqli_fetch_array($query_res)){
		echo(json_encode($errmsg[9]));
		return;
	}
	$sql = "insert into users (name,email,passwd) values('$name','$email','$password')";
	$query_res = query_sql($sql);
	if(!$query_res){
		echo(json_encode($errmsg[5]));
		return;
	}

	$sql = "select id from users where name='$name' and email='$email'";
	$query_res = query_sql($sql);
	if(!$query_res){
		echo(json_encode($errmsg[5]));
		return;
	}
	$row = mysqli_fetch_array($query_res);
	$id=$row['id'];
	mkdir("../data/$id");
	gencode(false);

	echo json_encode(array('result'=>0,'msg'=>'OK'));
	return;
}

function verify(){
	global $errmsg;
	if(!((isset($_REQUEST['name'])||isset($_REQUEST['email']))&&isset($_REQUEST['code']))){
		echo json_encode($errmsg[4]);
		return;
	}
}

function modify(){
	global $errmsg;
	if(!((isset($_REQUEST['name'])||isset($_REQUEST['email']))&&isset($_REQUEST['password'])&&isset($_REQUEST['target'])&&isset($_REQUEST['content']))){
		echo json_encode($errmsg[4]);
		return;
	}
}

function undefined(){//未定义操作
	global $errmsg;
	echo json_encode($errmsg[3]);
	return;
}

function cleanup(){
	global $conn;
	mysqli_close($conn);
}

?>
