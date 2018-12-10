<?php
require_once BASE_PATH . DS . 'framework/lib/qq/comm/config.php';
require_once BASE_PATH . DS . 'framework/lib/qq/comm/utils.php';

function get_user_info()
{
    $get_user_info = 'https://graph.qq.com/user/get_user_info?'
        . 'access_token=' . $_SESSION['access_token']
        . '&oauth_consumer_key=' . \core\session::get('appid')
        . '&openid=' . \core\session::get('openid')
        . '&format=json';

    $info = get_url_contents($get_user_info);
    $arr = json_decode($info, true);
    $arr = getGBK($arr,CHARSET);

    return $arr;
}
