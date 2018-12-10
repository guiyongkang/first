<?php
/**
 * PHP SDK for QQ登录 OpenAPI
 *
 * @version 1.2
 * @author connect@qq.com
 * @copyright © 2011, Tencent Corporation. All rights reserved.
 */
/**
 * 正式运营环境请关闭错误信息
 * ini_set("error_reporting", E_ALL);
 * ini_set("display_errors", TRUE);
 * QQDEBUG = true  开启错误提示
 * QQDEBUG = false 禁止错误提示
 * 默认禁止错误信息
 */
define('QQDEBUG', true);
if (defined('QQDEBUG') && QQDEBUG)
{
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', TRUE);
}

//包含配置信息
$data = rkcache('setting', true);
//qq互联是否开启
if($data['qq_isuse'] != 1){
	header('location: '. WAP_SITE_URL);
	exit;
}

//申请到的appid
\core\session::set('appid', trim($data['qq_appid']));

//申请到的appkey
\core\session::set('appkey', trim($data['qq_appkey']));

//QQ登录成功后跳转的地址,请确保地址真实可用，否则会导致登录失败。
\core\session::set('callback', BASE_SITE_URL . '/api/index.php?act=qqlogin_mobile&op=get_access_token');

//QQ授权api接口.按需调用
\core\session::set('scope', 'get_user_info');
//print_r ($_SESSION);
?>
