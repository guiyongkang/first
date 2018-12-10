<?php
error_reporting(E_ALL);
ini_set('display_errors','on');
define('APP_ID', 'mobile');
define('IGNORE_EXCEPTION', true);
define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));

//define('MOBILE_RESOURCE_SITE_URL', APP_URL . DS . 'resource');
if (!empty($_GET['key']) && !is_null($_GET['key']) && !is_string($_GET['key'])) {
    $_GET['key'] = null;
}
if (!empty($_POST['key']) && !is_null($_POST['key']) && !is_string($_POST['key'])) {
    $_POST['key'] = null;
}
if (!empty($_REQUEST['key']) && !is_null($_REQUEST['key']) && !is_string($_REQUEST['key'])) {
    $_REQUEST['key'] = null;
}
include(dirname(BASE_PATH) . '/core/core.php');
