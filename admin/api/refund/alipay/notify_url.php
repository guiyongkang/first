<?php
/**
 * 支付宝服务器异步通知页面
 *
 */
$_GET['act']	= 'notify_refund';
$_GET['op']		= 'alipay';
require_once(dirname(__FILE__).'/../../../index.php');
?>