<?php
error_reporting(E_ALL);
ini_set('display_errors','on');

define('APP_ID', 'admin');
define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
include(dirname(BASE_PATH) . '/core/core.php');
?>