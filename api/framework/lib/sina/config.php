<?php
header('Content-Type: text/html; charset=UTF-8');
//包含配置信息
$data = rkcache('setting', true);
//判读新浪微博登录是否开启
if ($data['sina_isuse'] != 1) {
    header('location: ' . WAP_SITE_URL);
    exit;
}
define('WB_AKEY', trim($data['sina_wb_akey']));
define('WB_SKEY', trim($data['sina_wb_skey']));
define('WB_CALLBACK_URL', BASE_SITE_URL . '/api/index.php?act=sinalogin_mobile&op=g');