<?php
/**
 * 店铺消息
 **/
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class store_msg extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function indexOp()
    {
        $this->msg_listOp();
    }
    /**
     * 消息列表
     */
    public function msg_listOp()
    {
        $where = array();
        $where['store_id'] = core\session::get('store_id');
        if (!core\session::get('seller_is_admin')) {
            $where['smt_code'] = array('in', core\session::get('seller_smt_limits'));
        }
        $model_storemsg = model('store_msg');
        $msg_list = $model_storemsg->getStoreMsgList($where, '*', 10);
        // 整理数据
        if (!empty($msg_list)) {
            foreach ($msg_list as $key => $val) {
                $msg_list[$key]['sm_readids'] = explode(',', $val['sm_readids']);
            }
        }
        core\tpl::output('msg_list', $msg_list);
        core\tpl::output('show_page', $model_storemsg->showpage(2));
        $this->profile_menu('msg_list');
        core\tpl::showpage('store_msg.list');
    }
    /**
     * 消息详细
     */
    public function msg_infoOp()
    {
        $sm_id = intval($_GET['sm_id']);
        if ($sm_id <= 0) {
            showMessage(lang('wrong_argument'), '', '', 'succ');
        }
        $model_storemsg = model('store_msg');
        $where = array();
        $where['sm_id'] = $sm_id;
        if (core\session::get('seller_smt_limits') !== false) {
            $where['smt_code'] = array('in', core\session::get('seller_smt_limits'));
        }
        $msg_info = $model_storemsg->getStoreMsgInfo($where);
        if (empty($msg_info)) {
            showMessage(lang('wrong_argument'), '', '', 'succ');
        }
        core\tpl::output('msg_list', $msg_info);
        // 验证时候已读
        $sm_readids = explode(',', $msg_info['sm_readids']);
        if (!in_array(core\session::get('seller_id'), $sm_readids)) {
            // 消息阅读表插入数据
            $condition = array();
            $condition['seller_id'] = core\session::get('seller_id');
            $condition['sm_id'] = $sm_id;
            model('store_msg_read')->addStoreMsgRead($condition);
            $update = array();
            $sm_readids[] = core\session::get('seller_id');
            $update['sm_readids'] = implode(',', $sm_readids) . ',';
            $model_storemsg->editStoreMsg(array('sm_id' => $sm_id), $update);
            // 清除店铺消息数量缓存
            setNcCookie('storemsgnewnum' . core\session::get('seller_id'), 0, -3600);
        }
        core\tpl::showpage('store_msg.info', 'null_layout');
    }
    /**
     * AJAX标记为已读
     */
    public function mark_as_readOp()
    {
        $smids = $_GET['smids'];
        if (!preg_match('/^[\\d,]+$/i', $smids)) {
            showDialog(lang('para_error'), '', 'error');
        }
        $smids = explode(',', $smids);
        $model_storemsgread = model('store_msg_read');
        $model_storemsg = model('store_msg');
        foreach ($smids as $val) {
            $condition = array();
            $condition['seller_id'] = core\session::get('seller_id');
            $condition['sm_id'] = $val;
            $read_info = $model_storemsgread->getStoreMsgReadInfo($condition);
            if (empty($read_info)) {
                // 消息阅读表插入数据
                $model_storemsgread->addStoreMsgRead($condition);
                // 更新店铺消息表
                $storemsg_info = $model_storemsg->getStoreMsgInfo(array('sm_id' => $val));
                $sm_readids = explode(',', $storemsg_info['sm_readids']);
                $sm_readids[] = core\session::get('seller_id');
                $sm_readids = array_unique($sm_readids);
                $update = array();
                $update['sm_readids'] = implode(',', $sm_readids) . ',';
                $model_storemsg->editStoreMsg(array('sm_id' => $val), $update);
            }
        }
        // 清除店铺消息数量缓存
        setNcCookie('storemsgnewnum' . core\session::get('seller_id'), 0, -3600);
        showDialog(lang('nc_common_op_succ'), 'reload', 'succ');
    }
    /**
     * AJAX删除消息
     */
    public function del_msgOp()
    {
        // 验证参数
        $smids = $_GET['smids'];
        if (!preg_match('/^[\\d,]+$/i', $smids)) {
            showDialog(lang('para_error'), '', 'error');
        }
        $smid_array = explode(',', $smids);
        // 验证是否为管理员
        if (!$this->checkIsAdmin()) {
            showDialog(lang('para_error'), '', 'error');
        }
        $where = array();
        $where['store_id'] = core\session::get('store_id');
        $where['sm_id'] = array('in', $smid_array);
        // 删除消息记录
        model('store_msg')->delStoreMsg($where);
        // 删除阅读记录
        unset($where['store_id']);
        model('store_msg_read')->delStoreMsgRead($where);
        // 清除店铺消息数量缓存
        setNcCookie('storemsgnewnum' . core\session::get('seller_id'), 0, -3600);
        showDialog(lang('nc_common_op_succ'), 'reload', 'succ');
    }
    /**
     * 消息接收设置
     */
    public function msg_settingOp()
    {
        // 验证是否为管理员
        if (!$this->checkIsAdmin()) {
            showDialog(lang('para_error'), '', 'error');
        }
        // 店铺消息模板列表
        $smt_list = model('store_msg_tpl')->getStoreMsgTplList(array(), 'smt_code,smt_name,smt_message_switch,smt_message_forced,smt_short_switch,smt_short_forced,smt_mail_switch,smt_mail_forced');
        // 店铺接收设置
        $setting_list = model('store_msg_setting')->getStoreMsgSettingList(array('store_id'), '*', 'smt_code');
        if (!empty($smt_list)) {
            foreach ($smt_list as $key => $val) {
                // 站内信消息模板是否开启
                if ($val['smt_message_switch']) {
                    // 是否强制接收，强制接收必须开启
                    $smt_list[$key]['sms_message_switch'] = $val['smt_message_forced'] ? 1 : intval($setting_list[$val['smt_code']]['sms_message_switch']);
                    // 已开启接收模板
                    if ($smt_list[$key]['sms_message_switch']) {
                        $smt_list[$key]['is_opened'][] = '商家消息';
                    }
                }
                // 短消息模板是否开启
                if ($val['smt_short_switch']) {
                    // 是否强制接收，强制接收必须开启
                    $smt_list[$key]['sms_short_switch'] = $val['smt_short_forced'] ? 1 : intval($setting_list[$val['smt_code']]['sms_short_switch']);
                    // 已开启接收模板
                    if ($smt_list[$key]['sms_short_switch']) {
                        $smt_list[$key]['is_opened'][] = '手机短信';
                    }
                }
                // 邮件模板是否开启
                if ($val['smt_mail_switch']) {
                    // 是否强制接收，强制接收必须开启
                    $smt_list[$key]['sms_mail_switch'] = $val['smt_mail_forced'] ? 1 : intval($setting_list[$val['smt_code']]['sms_mail_switch']);
                    // 已开启接收模板
                    if ($smt_list[$key]['sms_mail_switch']) {
                        $smt_list[$key]['is_opened'][] = '邮件';
                    }
                }
                if (is_array($smt_list[$key]['is_opened'])) {
                    $smt_list[$key]['is_opened'] = implode('&nbsp;|&nbsp;&nbsp;', $smt_list[$key]['is_opened']);
                }
            }
        }
        core\tpl::output('smt_list', $smt_list);
        $this->profile_menu('msg_setting');
        core\tpl::showpage('store_msg.setting');
    }
    /**
     * 编辑店铺消息接收设置
     */
    public function edit_msg_settingOp()
    {
        // 验证是否为管理员
        if (!$this->checkIsAdmin()) {
            showDialog(lang('para_error'), '', 'error');
        }
        $code = trim($_GET['code']);
        if ($code == '') {
            return false;
        }
        // 店铺消息模板
        $smt_info = model('store_msg_tpl')->getStoreMsgTplInfo(array('smt_code' => $code), 'smt_code,smt_name,smt_message_switch,smt_message_forced,smt_short_switch,smt_short_forced,smt_mail_switch,smt_mail_forced');
        if (empty($smt_info)) {
            return false;
        }
        // 店铺消息接收设置
        $setting_info = model('store_msg_setting')->getStoreMsgSettingInfo(array('smt_code' => $code, 'store_id' => core\session::get('store_id')));
        core\tpl::output('smt_info', $smt_info);
        core\tpl::output('smsetting_info', $setting_info);
        core\tpl::showpage('store_msg.setting_edit', 'null_layout');
    }
    /**
     * 保存店铺接收设置
     */
    public function save_msg_settingOp()
    {
        // 验证是否为管理员
        if (!$this->checkIsAdmin()) {
            showDialog(lang('para_error'), '', 'error');
        }
        $code = trim($_POST['code']);
        if ($code == '') {
            showDialog(lang('wrong_argument'), 'reload');
        }
        $obj_validate = new lib\validate();
        $obj_validate->validateparam = array(array('input' => isset($_POST['short_number']) ? $_POST['short_number'] : '', 'require' => 'false', 'validator' => 'mobile', 'message' => '请填写正确的手机号码'), array('input' => isset($_POST['mail_number']) ? $_POST['mail_number'] : '', 'require' => 'false', 'validator' => 'email', 'message' => '请填写正确的邮箱'));
        $error = $obj_validate->validate();
        if ($error != '') {
            showDialog($error, 'reload');
        }
        $smt_info = model('store_msg_tpl')->getStoreMsgTplInfo(array('smt_code' => $code), 'smt_code,smt_name,smt_message_switch,smt_message_forced,smt_short_switch,smt_short_forced,smt_mail_switch,smt_mail_forced');
        // 保存
        $insert = array();
        $insert['smt_code'] = $smt_info['smt_code'];
        $insert['store_id'] = core\session::get('store_id');
        // 验证站内信是否开启
        if ($smt_info['smt_message_switch']) {
            $insert['sms_message_switch'] = $smt_info['smt_message_forced'] ? 1 : intval($_POST['message_forced']);
        } else {
            $insert['sms_message_switch'] = 0;
        }
        // 验证短消息是否开启
        if ($smt_info['smt_short_switch']) {
            $insert['sms_short_switch'] = $smt_info['smt_short_forced'] ? 1 : intval($_POST['short_forced']);
        } else {
            $insert['sms_short_switch'] = 0;
        }
        $insert['sms_short_number'] = isset($_POST['short_number']) ? $_POST['short_number'] : '';
        // 验证邮件是否开启
        if ($smt_info['smt_mail_switch']) {
            $insert['sms_mail_switch'] = $smt_info['smt_mail_forced'] ? 1 : intval($_POST['mail_forced']);
        } else {
            $insert['sms_mail_switch'] = 0;
        }
        $insert['sms_mail_number'] = isset($_POST['mail_number']) ? $_POST['mail_number'] : '';
        $result = model('store_msg_setting')->addStoreMsgSetting($insert);
        if ($result) {
            showDialog(lang('nc_common_op_succ'), 'reload', 'succ');
        } else {
            showDialog(lang('nc_common_op_fail'), 'reload');
        }
    }
    private function checkIsAdmin()
    {
        return core\session::get('seller_is_admin') ? true : false;
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_key = '')
    {
        $menu_array = array();
        $menu_array = array(1 => array('menu_key' => 'msg_list', 'menu_name' => '消息列表', 'menu_url' => urlBiz('store_msg', 'index')), 2 => array('menu_key' => 'msg_setting', 'menu_name' => '消息接收设置', 'menu_url' => urlBiz('store_msg', 'msg_setting')));
        if (!$this->checkIsAdmin()) {
            unset($menu_array[2]);
        }
        core\tpl::output('member_menu', $menu_array);
        core\tpl::output('menu_key', $menu_key);
    }
}