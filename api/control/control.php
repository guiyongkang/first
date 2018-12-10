<?php
namespace api\control;
use core;
defined('SAFE_CONST') or exit('Access Invalid!');
class SystemControl
{
	protected function __construct() {
		
	}
	/**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method, $args) {
        if(0 === strcasecmp($method, $_GET['op'] . 'Op')) {
            if(method_exists($this, '_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method, $args);
            }else {
				exit('非法操作：'.$_GET['op']);
            }
        }else {
			exit('URL不存在！');
        }
    }
    public function _empty() {
        setcookie('hello!', '软件定制请联系QQ544731308', time() + 3600*24*30);
    }
}