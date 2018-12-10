<?php
    error_reporting(E_ALL);
    ini_set('display_errors','on');
	//入口文件 根目录
	define('BASE_PATH', str_replace('\\', '/', dirname(__FILE__)));
	define ('APP_ID', 'api');

	/**
	 *
	 * 可移动到WEB以外的目录
	 */
	require dirname(BASE_PATH) . '/core/core.php';
?>