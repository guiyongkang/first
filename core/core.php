<?php
namespace core;
include(dirname(dirname(__FILE__)) . '/global.php');
require(BASE_CORE_PATH . '/framework/function/core.php');
if(function_exists('spl_autoload_register')) {
	spl_autoload_register('autoload');
} else {
	function __autoload($class) {
		return autoload($class);
	}
}
config::load(BASE_DATA_PATH . '/config/config.ini.php');
config::load(BASE_PATH . '/config/config.ini.php');
//默认平台店铺id
define('DEFAULT_PLATFORM_STORE_ID', config::get('default_store_id'));

define('URL_MODEL', config::get('url_model'));
define('SUBDOMAIN_SUFFIX', config::get('subdomain_suffix'));
define('UPLOAD_SITE_URL', config::get('upload_site_url'));
define('RESOURCE_SITE_URL', config::get('resource_site_url'));

define('NODE_SITE_URL', config::get('node_site_url'));
define('UPLOAD_SITE_URL_HTTPS', config::get('upload_site_url'));

define('RESOURCE_SITE_URL_HTTPS', config::get('resource_site_url'));

define('CHARSET', config::get('db.1.dbcharset'));
define('DBDRIVER', config::get('dbdriver'));
define('SESSION_EXPIRE', config::get('session_expire'));
define('LANG_TYPE', config::get('lang_type'));
define('COOKIE_PRE', config::get('cookie_pre'));
define('MD5_KEY', md5(config::get('md5_key')));
define('DBPRE', config::get('tablepre'));
define('DBNAME', config::get('db.1.dbname'));
define('TPL_NAME', TPL_ADMIN_NAME);


define('BASE_SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('BASE_TPL_PATH', BASE_PATH . DS . 'templates' . DS . TPL_NAME);
define('APP_URL', BASE_SITE_URL . DS . APP_ID);
define('APP_TEMPLATES_URL', APP_URL . DS . 'templates' . DS . TPL_NAME);
define('APP_RESOURCE_SITE_URL', APP_URL . DS . 'resource');

define('WAP_SITE_URL', BASE_SITE_URL . DS . DIR_WAP);
define('API_SITE_URL', BASE_SITE_URL . DS . DIR_API);

$_GET['act'] = !empty($_GET['act']) ? strtolower($_GET['act']) : (!empty($_POST['act']) ? strtolower($_POST['act']) : null);
$_GET['op'] = !empty($_GET['op']) ? strtolower($_GET['op']) : (!empty($_POST['op']) ? strtolower($_POST['op']) : null);
if (empty($_GET['act'])) {
    route::init(config::get());
}
//统一ACTION
$_GET['act'] = preg_match('/^[\w]+$/i',$_GET['act']) ? $_GET['act'] : 'index';
$_GET['op'] = preg_match('/^[\w]+$/i',$_GET['op']) ? $_GET['op'] : 'index';

//对GET POST接收内容进行过滤,$ignore内的下标不被过滤
$ignore = array('article_content','pgoods_body','doc_content','content','sn_content','g_body','store_description','p_content','groupbuy_intro','remind_content','note_content','ref_url','adv_pic_url','adv_word_url','adv_slide_url','appcode','mail_content');
$_GET = !empty($_GET) ? security::getAddslashesForInput($_GET, $ignore) : array();
$_POST = !empty($_POST) ? security::getAddslashesForInput($_POST, $ignore) : array();
$_REQUEST = !empty($_REQUEST) ? security::getAddslashesForInput($_REQUEST, $ignore) : array();
$_SERVER = !empty($_SERVER) ? security::getAddSlashes($_SERVER) : array();

//启用ZIP压缩
if (config::get('gzip') == 1 && function_exists('ob_gzhandler') && !IS_AJAX){
	ob_start('ob_gzhandler');
}else {
	ob_start();
}
require(BASE_CORE_PATH . '/framework/function/extend.php');
require(BASE_CORE_PATH . '/framework/function/goods.php');

//框架扩展
if(file_exists(BASE_PATH . '/framework/function/function.php')){
	require(BASE_PATH . '/framework/function/function.php');
}
if(file_exists(BASE_PATH . '/control/control.php')){
	include(BASE_PATH . '/control/control.php');
}
base::run();
?>