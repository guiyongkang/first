<?php
/**
 * 卖家账号日志
 **/
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class seller_log extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function log_listOp()
    {
        $model_seller_log = model('seller_log');
        $condition = array();
        $condition['log_store_id'] = $_SESSION['store_id'];
        if (!empty($_GET['seller_name'])) {
            $condition['log_seller_name'] = array('like', '%' . $_GET['seller_name'] . '%');
        }
        if (!empty($_GET['log_content'])) {
            $condition['log_content'] = array('like', '%' . $_GET['log_content'] . '%');
        }
        $condition['log_time'] = array('time', array((isset($_GET['add_time_from']) ? strtotime($_GET['add_time_from']) : 0), (isset($_GET['add_time_to']) ? strtotime($_GET['add_time_to']) : 0)));
        $log_list = $model_seller_log->getSellerLogList($condition, 10, 'log_id desc');
        core\tpl::output('log_list', $log_list);
        core\tpl::output('show_page', $model_seller_log->showpage(2));
        $this->profile_menu('log_list');
        core\tpl::showpage('seller_log.list');
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string 	$menu_key	当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key = '')
    {
        $menu_array = array();
        $menu_array[] = array('menu_key' => 'log_list', 'menu_name' => '日志列表', 'menu_url' => urlBiz('seller_log', 'log_list'));
        core\tpl::output('member_menu', $menu_array);
        core\tpl::output('menu_key', $menu_key);
    }
}