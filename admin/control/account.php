<?php
/**
 * 账号同步 
 *
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class account extends SystemControl
{
    private $links = array(/*array('url' => 'act=account&op=qq', 'lang' => 'qqSettings'), array('url' => 'act=account&op=sina', 'lang' => 'sinaSettings'),*/ array('url' => 'act=account&op=sms', 'lang' => 'smsSettings'));
    public function __construct()
    {
        parent::__construct();
        core\language::read('setting');
    }
    public function indexOp()
    {
        $this->qqOp();
    }
    /**
     * QQ互联
     */
    public function qqOp()
    {
        $model_setting = model('setting');
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            if (trim($_POST['qq_isuse']) == '1') {
                $obj_validate->validateparam = array(array("input" => $_POST["qq_appid"], "require" => "true", "message" => core\language::get('qq_appid_error')), array("input" => $_POST["qq_appkey"], "require" => "true", "message" => core\language::get('qq_appkey_error')));
            }
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $update_array = array();
                $update_array['qq_isuse'] = $_POST['qq_isuse'];
                $update_array['qq_appcode'] = $_POST['qq_appcode'];
                $update_array['qq_appid'] = $_POST['qq_appid'];
                $update_array['qq_appkey'] = $_POST['qq_appkey'];
                $result = $model_setting->updateSetting($update_array);
                if ($result === true) {
                    $this->log(lang('nc_edit,qqSettings'), 1);
                    success(core\language::get('nc_common_save_succ'));
                } else {
                    $this->log(lang('nc_edit,qqSettings'), 0);
                    error(core\language::get('nc_common_save_fail'));
                }
            }
        }
        $list_setting = $model_setting->getListSetting();
        core\tpl::output('list_setting', $list_setting);
        //输出子菜单
        core\tpl::output('top_link', $this->sublink($this->links, 'qq'));
        core\tpl::showpage('setting.qq_setting');
    }
    /**
     * sina微博设置
     */
    public function sinaOp()
    {
        $model_setting = model('setting');
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            if (trim($_POST['sina_isuse']) == '1') {
                $obj_validate->validateparam = array(array("input" => $_POST["sina_wb_akey"], "require" => "true", "message" => core\language::get('sina_wb_akey_error')), array("input" => $_POST["sina_wb_skey"], "require" => "true", "message" => core\language::get('sina_wb_skey_error')));
            }
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $update_array = array();
                $update_array['sina_isuse'] = $_POST['sina_isuse'];
                $update_array['sina_wb_akey'] = $_POST['sina_wb_akey'];
                $update_array['sina_wb_skey'] = $_POST['sina_wb_skey'];
                $update_array['sina_appcode'] = $_POST['sina_appcode'];
                $result = $model_setting->updateSetting($update_array);
                if ($result === true) {
                    $this->log(lang('nc_edit,sinaSettings'), 1);
                    success(core\language::get('nc_common_save_succ'));
                } else {
                    $this->log(lang('nc_edit,sinaSettings'), 0);
                    error(core\language::get('nc_common_save_fail'));
                }
            }
        }
        $is_exist = function_exists('curl_init');
        if ($is_exist) {
            $list_setting = $model_setting->getListSetting();
            core\tpl::output('list_setting', $list_setting);
        }
        core\tpl::output('is_exist', $is_exist);
        //输出子菜单
        core\tpl::output('top_link', $this->sublink($this->links, 'sina'));
        core\tpl::showpage('setting.sina_setting');
    }
    /**
     * 手机短信设置
     */
    public function smsOp()
    {
        $model_setting = model('setting');
        if (chksubmit()) {
            $update_array = array();
            $update_array['sms_register'] = $_POST['sms_register'];
            $update_array['sms_login'] = $_POST['sms_login'];
            $update_array['sms_password'] = $_POST['sms_password'];
            $result = $model_setting->updateSetting($update_array);
            if ($result) {
                $this->log('编辑账号同步，手机短信设置');
                success(core\language::get('nc_common_save_succ'));
            } else {
                error(core\language::get('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        core\tpl::output('list_setting', $list_setting);
        //输出子菜单
        core\tpl::output('top_link', $this->sublink($this->links, 'sms'));
        core\tpl::showpage('setting.sms_setting');
    }
}