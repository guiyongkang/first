<?php
/**
 * 队列
 */
error_reporting(E_ALL);
ini_set('display_errors','on');
// linux 使用
//$_SERVER['argv'][1] = $_GET['act'];
//$_SERVER['argv'][2] = $_GET['op'];
if (empty($_SERVER['argv'][1])) {
    //exit('Access Invalid!');
}
define('APP_ID', 'crontab');
define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
define('TRANS_MASTER', true);
if (PHP_SAPI == 'cli') {
    //$_GET['act'] = $_SERVER['argv'][1];
    //$_GET['op'] = empty($_SERVER['argv'][2]) ? 'index' : $_SERVER['argv'][2];
}
include dirname(BASE_PATH) . '/core/core.php';