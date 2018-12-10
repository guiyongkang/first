<?php
$UA = strtoupper($_SERVER['HTTP_USER_AGENT']);
if(strpos($UA, 'WINDOWS NT') == false){
	header('location:/wap/index.html');
}else{
	header('location:/biz/index.php');
}

