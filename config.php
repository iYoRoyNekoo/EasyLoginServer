<?php
//Database config
$mysql_server_host = 'localhost';
$mysql_username = 'login';
$mysql_password = '061207';
$mysql_database = 'login';


$service_status = false;

$errmsg=array(
	1=>array('result'=>1,'msg'=>'Service is unavailable'),
	2=>array('result'=>2,'msg'=>'Failed to connect to Database'),
	3=>array('result'=>3,'msg'=>'Undefined action'),
	4=>array('result'=>4,'msg'=>'Missing parameter')
);



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

?>
