<?php
require_once(BASE_PATH . DS . 'framework/lib/qq/comm/config.php');

function qq_login($appid, $scope, $callback)
{
	\core\session::set('state', md5(uniqid(rand(), TRUE)));//CSRF protection
    $login_url = 'https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=' 
        . $appid . '&redirect_uri=' . urlencode($callback)
        . '&state=' . \core\session::get('state')
        . '&scope=' . $scope;
    header('Location:' . $login_url);
}
//用户点击qq登录按钮调用此函数
qq_login(\core\session::get('appid'), \core\session::get('scope'), \core\session::get('callback'));
?>
