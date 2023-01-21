<?php
//Database config
$mysql_server_host = 'localhost';
$mysql_username = 'login';
$mysql_password = '061207';
$mysql_database = 'login';

$service_status = true;
$debug_mode = false;
$code_overtime = '+1 hour';
$token_overtime = '+3 months';

$errmsg=array(//错误返回信息
	1=>array('result'=>1,'msg'=>'Service is unavailable'),			//登录服务已禁用
	2=>array('result'=>2,'msg'=>'Failed to connect to Database'),	//数据库连接失败
	3=>array('result'=>3,'msg'=>'Undefined action'),				//无效操作（未知/未传入action）
	4=>array('result'=>4,'msg'=>'Missing parameter'),				//缺少参数（见上表，若传参缺失会报此错误码）
	5=>array('result'=>5,'msg'=>'Database query Failed'),			//数据库操作失败
	6=>array('result'=>6,'msg'=>'Unknow user/email'),				//未知用户
	7=>array('result'=>7,'msg'=>'Unnecessary operation'),			//无需执行操作
	8=>array('result'=>8,'msg'=>'Incorrect user name or password'),	//用户名或密码错误
	9=>array('result'=>9,'msg'=>'Username or email unavailable')	//用户名或邮箱已被注册
);

$smtpserver = "localhost";											//SMTP服务器
$smtpserverport = 25;												//SMTP服务器端口
$smtpusermail = "noreply@iyoroy.cn";								//SMTP服务器的用户邮箱  （你自己的邮箱地址）
$smtpuser = "noreply@iyoroy.cn";									//SMTP服务器的用户帐号 （你自己的邮箱地址）
$smtppass = "thisisthepasswordforthenoreplyemail(";					//SMTP服务器的用户密码 （你自己的邮箱地址 的客户端授权密码）
$mailtitle = "EasyLogin Verify Code";								//邮件主题 （不要动）
$mailcontenthead = "账户验证码为：";
$mailcontentfoot = "。如果不是您本人操作，请忽略这封邮件。";
$mailsender = "iYoRoy Network";										//发件人
$mailtype = "TXT";													//邮件格式（HTML/TXT）,TXT为文本邮件

?>
