<?php
/**
 * 客服中心
 *
 **/
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class store_callcenter extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('member_store_index');
    }
    public function indexOp()
    {
        $model_store = model('store');
        $store_info = $model_store->getStoreInfo(array('store_id' => core\session::get('store_id')));
        core\tpl::output('storeinfo', $store_info);
        $this->profile_menu('store_callcenter');
        $model_seller = model('seller');
        $seller_list = $model_seller->getSellerList(array('store_id' => $store_info['store_id']), '', 'seller_id asc');
        //账号列表
        core\tpl::output('seller_list', $seller_list);
        core\tpl::showpage('store_callcenter');
    }
    /**
     * 保存
     */
    public function saveOp()
    {
        if (chksubmit()) {
            $update = array();
            $i = 0;
            if (!empty($_POST['pre']) && is_array($_POST['pre'])) {
                foreach ($_POST['pre'] as $val) {
                    if (empty($val['name']) || empty($val['type']) || empty($val['num'])) {
                        continue;
                    }
                    $update['store_presales'][$i]['name'] = $val['name'];
                    $update['store_presales'][$i]['type'] = intval($val['type']);
                    $update['store_presales'][$i]['num'] = $val['num'];
                    $i++;
                }
                $update['store_presales'] = serialize($update['store_presales']);
            } else {
                $update['store_presales'] = serialize(null);
            }
            $i = 0;
            if (!empty($_POST['after']) && is_array($_POST['after'])) {
                foreach ($_POST['after'] as $val) {
                    if (empty($val['name']) || empty($val['type']) || empty($val['num'])) {
                        continue;
                    }
                    $update['store_aftersales'][$i]['name'] = $val['name'];
                    $update['store_aftersales'][$i]['type'] = intval($val['type']);
                    $update['store_aftersales'][$i]['num'] = $val['num'];
                    $i++;
                }
                $update['store_aftersales'] = isset($update['store_aftersales']) ? serialize($update['store_aftersales']) : serialize(null);
            } else {
                $update['store_aftersales'] = serialize(null);
            }
            $update['store_workingtime'] = $_POST['working_time'];
            $where = array();
            $where['store_id'] = core\session::get('store_id');
            model('store')->editStore($update, $where);
            showDialog(core\language::get('nc_common_save_succ'), 'index.php?act=store_callcenter', 'succ');
        }
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key)
    {
        $menu_array = array(1 => array('menu_key' => 'store_callcenter', 'menu_name' => core\language::get('nc_member_path_store_callcenter'), 'menu_url' => 'index.php?act=store_callcenter'));
        core\tpl::output('member_menu', $menu_array);
        core\tpl::output('menu_key', $menu_key);
    }
}