<?php
/**
 * 限时折扣管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class promotion_xianshi extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        //读取语言包
        core\language::read('promotion_xianshi');
        //检查审核功能是否开启
        if (isset($_GET['promotion_allow']) && intval($_GET['promotion_allow']) !== 1 && intval(core\config::get('promotion_allow')) !== 1) {
            $url = 'index.php?act=promotion_xianshi&promotion_allow=1';
            error(core\language::get('promotion_unavailable'), $url);
        }
    }
    /**
     * 默认Op
     */
    public function indexOp()
    {
        //自动开启限时折扣
        if (isset($_GET['promotion_allow']) && intval($_GET['promotion_allow']) === 1) {
            $model_setting = model('setting');
            $update_array = array();
            $update_array['promotion_allow'] = 1;
            $model_setting->updateSetting($update_array);
        }
        $this->xianshi_listOp();
    }
    /**
     * 活动列表
     **/
    public function xianshi_listOp()
    {
        $model_xianshi = model('p_xianshi');
        $condition = array();
        if (!empty($_GET['xianshi_name'])) {
            $condition['xianshi_name'] = array('like', '%' . $_GET['xianshi_name'] . '%');
        }
        if (!empty($_GET['store_name'])) {
            $condition['store_name'] = array('like', '%' . $_GET['store_name'] . '%');
        }
        if (!empty($_GET['state'])) {
            $condition['state'] = intval($_GET['state']);
        }
        $xianshi_list = $model_xianshi->getXianshiList($condition, 10, 'state desc, end_time desc');
        core\tpl::output('list', $xianshi_list);
        core\tpl::output('show_page', $model_xianshi->showpage());
        core\tpl::output('xianshi_state_array', $model_xianshi->getXianshiStateArray());
        $this->show_menu('xianshi_list');
        // 输出自营店铺IDS
        core\tpl::output('flippedOwnShopIds', array_flip(model('store')->getOwnShopIds()));
        core\tpl::showpage('promotion_xianshi.list');
    }
    /**
     * 限时折扣活动取消
     **/
    public function xianshi_cancelOp()
    {
        $xianshi_id = intval($_POST['xianshi_id']);
        $model_xianshi = model('p_xianshi');
        $result = $model_xianshi->cancelXianshi(array('xianshi_id' => $xianshi_id));
        if ($result) {
            $this->log('取消限时折扣活动，活动编号' . $xianshi_id);
            success(core\language::get('nc_common_op_succ'));
        } else {
            error(core\language::get('nc_common_op_fail'));
        }
    }
    /**
     * 限时折扣活动删除
     **/
    public function xianshi_delOp()
    {
        $xianshi_id = intval($_POST['xianshi_id']);
        $model_xianshi = model('p_xianshi');
        $result = $model_xianshi->delXianshi(array('xianshi_id' => $xianshi_id));
        if ($result) {
            $this->log('删除限时折扣活动，活动编号' . $xianshi_id);
            success(core\language::get('nc_common_op_succ'));
        } else {
            error(core\language::get('nc_common_op_fail'));
        }
    }
    /**
     * 活动详细信息
     **/
    public function xianshi_detailOp()
    {
        $xianshi_id = intval($_GET['xianshi_id']);
        $model_xianshi = model('p_xianshi');
        $model_xianshi_goods = model('p_xianshi_goods');
        $xianshi_info = $model_xianshi->getXianshiInfoByID($xianshi_id);
        if (empty($xianshi_info)) {
            error(lang('param_error'));
        }
        core\tpl::output('xianshi_info', $xianshi_info);
        //获取限时折扣商品列表
        $condition = array();
        $condition['xianshi_id'] = $xianshi_id;
        $xianshi_goods_list = $model_xianshi_goods->getXianshiGoodsExtendList($condition);
        core\tpl::output('list', $xianshi_goods_list);
        $this->show_menu('xianshi_detail');
        core\tpl::showpage('promotion_xianshi.detail');
    }
    /**
     * 套餐管理
     **/
    public function xianshi_quotaOp()
    {
        $model_xianshi_quota = model('p_xianshi_quota');
        $condition = array();
		if(!empty($_GET['store_name'])){
			$condition['store_name'] = array('like', '%' . $_GET['store_name'] . '%');
		}
        $list = $model_xianshi_quota->getXianshiQuotaList($condition, 10, 'end_time desc');
        core\tpl::output('list', $list);
        core\tpl::output('show_page', $model_xianshi_quota->showpage());
        $this->show_menu('xianshi_quota');
        core\tpl::showpage('promotion_xianshi_quota.list');
    }
    /**
     * 设置
     **/
    public function xianshi_settingOp()
    {
        $model_setting = model('setting');
        $setting = $model_setting->GetListSetting();
        core\tpl::output('setting', $setting);
        $this->show_menu('xianshi_setting');
        core\tpl::showpage('promotion_xianshi.setting');
    }
    public function xianshi_setting_saveOp()
    {
        $promotion_xianshi_price = intval($_POST['promotion_xianshi_price']);
        if ($promotion_xianshi_price === 0) {
            $promotion_xianshi_price = 20;
        }
        $model_setting = model('setting');
        $update_array = array();
        $update_array['promotion_xianshi_price'] = $promotion_xianshi_price;
        $result = $model_setting->updateSetting($update_array);
        if ($result) {
            $this->log('修改限时折扣价格为' . $promotion_xianshi_price . '元');
            success(core\language::get('setting_save_success'));
        } else {
            error(core\language::get('setting_save_fail'));
        }
    }
    /**
     * ajax修改抢购信息
     */
    public function ajaxOp()
    {
        $result = true;
        $update_array = array();
        $where_array = array();
        switch ($_GET['branch']) {
            case 'recommend':
                $model = model('p_xianshi_goods');
                $update_array['xianshi_recommend'] = $_GET['value'];
                $where_array['xianshi_goods_id'] = $_GET['id'];
                $result = $model->editXianshiGoods($update_array, $where_array);
                break;
        }
        if ($result) {
            echo 'true';
            exit;
        } else {
            echo 'false';
            exit;
        }
    }
    /*
     * 发送消息
     */
    private function send_message($member_id, $member_name, $message)
    {
        $param = array();
        $param['from_member_id'] = 0;
        $param['member_id'] = $member_id;
        $param['to_member_name'] = $member_name;
        $param['message_type'] = '1';
        //表示为系统消息
        $param['msg_content'] = $message;
        $model_message = model('message');
        return $model_message->saveMessage($param);
    }
    /**
     * 页面内导航菜单
     *
     * @param string 	$menu_key	当前导航的menu_key
     * @param array 	$array		附加菜单
     * @return
     */
    private function show_menu($menu_key)
    {
        $menu_array = array('xianshi_list' => array('menu_type' => 'link', 'menu_name' => core\language::get('xianshi_list'), 'menu_url' => 'index.php?act=promotion_xianshi&op=xianshi_list'), 'xianshi_detail' => array('menu_type' => 'link', 'menu_name' => core\language::get('xianshi_detail'), 'menu_url' => 'index.php?act=promotion_xianshi&op=xianshi_detail'), 'xianshi_quota' => array('menu_type' => 'link', 'menu_name' => core\language::get('xianshi_quota'), 'menu_url' => 'index.php?act=promotion_xianshi&op=xianshi_quota'), /*'xianshi_setting' => array('menu_type' => 'link', 'menu_name' => core\language::get('xianshi_setting'), 'menu_url' => 'index.php?act=promotion_xianshi&op=xianshi_setting')*/);
        if ($menu_key != 'xianshi_detail') {
            unset($menu_array['xianshi_detail']);
        }
        $menu_array[$menu_key]['menu_type'] = 'text';
        core\tpl::output('menu', $menu_array);
    }
}