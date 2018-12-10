<?php
/**
 * 店铺卖家注销
 **/
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class seller_logout extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function indexOp()
    {
        $this->logoutOp();
    }
    public function logoutOp()
    {
        $this->recordSellerLog('注销成功');
        // 清除店铺消息数量缓存
        setNcCookie('storemsgnewnum' . core\session::get('seller_id'), 0, -3600);
        session_destroy();
        redirect('index.php?act=seller_login');
    }
}