<?php
/**
 * 消息通知
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class message extends SystemControl
{
    private $links = array(array('url' => 'act=message&op=email', 'lang' => 'email_set'), array('url' => 'act=message&op=mobile', 'lang' => 'mobile_set'), array('url' => 'act=message&op=seller_tpl', 'lang' => 'seller_tpl'), array('url' => 'act=message&op=member_tpl', 'lang' => 'member_tpl'), array('url' => 'act=message&op=email_tpl', 'lang' => 'email_tpl'));
    public function __construct()
    {
        parent::__construct();
        core\language::read('setting,message');
    }
    /**
     * 邮件设置
     */
    public function emailOp()
    {
        $model_setting = model('setting');
        if (chksubmit()) {
            $update_array = array();
            $update_array['email_host'] = $_POST['email_host'];
            $update_array['email_port'] = $_POST['email_port'];
            $update_array['email_addr'] = $_POST['email_addr'];
            $update_array['email_id'] = $_POST['email_id'];
            $update_array['email_pass'] = $_POST['email_pass'];
            $result = $model_setting->updateSetting($update_array);
            if ($result === true) {
                $this->log(lang('nc_edit,email_set'), 1);
                success(lang('nc_common_save_succ'));
            } else {
                $this->log(lang('nc_edit,email_set'), 0);
                error(lang('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        core\tpl::output('list_setting', $list_setting);
        core\tpl::output('top_link', $this->sublink($this->links, 'email'));
        core\tpl::showpage('message.email');
    }
    /**
     * 短信平台设置
     */
    public function mobileOp()
    {
        $model_setting = model('setting');
        if (chksubmit()) {
            $update_array = array();
            $update_array['mobile_host_type'] = $_POST['mobile_host_type'];
            $update_array['mobile_host'] = $_POST['mobile_host'];
            $update_array['mobile_username'] = $_POST['mobile_username'];
            $update_array['mobile_pwd'] = $_POST['mobile_pwd'];
            $update_array['mobile_key'] = $_POST['mobile_key'];
            $update_array['mobile_signature'] = $_POST['mobile_signature'];
            $update_array['mobile_memo'] = $_POST['mobile_memo'];
            $result = $model_setting->updateSetting($update_array);
            if ($result === true) {
                $this->log(lang('nc_edit,mobile_set'), 1);
                success(lang('nc_common_save_succ'));
            } else {
                $this->log(lang('nc_edit,mobile_set'), 0);
                error(lang('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        core\tpl::output('list_setting', $list_setting);
        core\tpl::output('top_link', $this->sublink($this->links, 'mobile'));
        core\tpl::showpage('message.mobile');
    }
    /**
     * 邮件模板列表
     */
    public function email_tplOp()
    {
        $model_templates = model('mail_templates');
        $templates_list = $model_templates->getTplList();
        core\tpl::output('templates_list', $templates_list);
        core\tpl::output('top_link', $this->sublink($this->links, 'email_tpl'));
        core\tpl::showpage('message.email_tpl');
    }
    /**
     * 编辑邮件模板
     */
    public function email_tpl_editOp()
    {
        $model_templates = model('mail_templates');
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['code'], 'require' => 'true', 'message' => lang('mailtemplates_edit_no_null')), array('input' => $_POST['title'], 'require' => 'true', 'message' => lang('mailtemplates_edit_title_null')), array('input' => $_POST['content'], 'require' => 'true', 'message' => lang('mailtemplates_edit_content_null')));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $update_array = array();
                $update_array['code'] = $_POST['code'];
                $update_array['title'] = $_POST['title'];
                $update_array['content'] = $_POST['content'];
                $result = $model_templates->editTpl($update_array, array('code' => $_POST['code']));
                if ($result === true) {
                    $this->log(lang('nc_edit,email_tpl'), 1);
                    success(lang('mailtemplates_edit_succ'), 'index.php?act=message&op=email_tpl');
                } else {
                    $this->log(lang('nc_edit,email_tpl'), 0);
                    error(lang('mailtemplates_edit_fail'));
                }
            }
        }
        if (empty($_GET['code'])) {
            error(lang('mailtemplates_edit_code_null'));
        }
        $templates_array = $model_templates->getTplInfo(array('code' => $_GET['code']));
        core\tpl::output('templates_array', $templates_array);
        core\tpl::output('top_link', $this->sublink($this->links, 'email_tpl'));
        core\tpl::showpage('message.email_tpl.edit');
    }
    /**
     * 测试邮件发送
     *
     * @param
     * @return
     */
    public function email_testingOp()
    {
		$lang = core\language::getLangContent();
		core\config::set('email_host', trim($_POST['email_host']));
		core\config::set('email_port', trim($_POST['email_port']));
		core\config::set('email_addr', trim($_POST['email_addr']));
		core\config::set('email_id', trim($_POST['email_id']));
		core\config::set('email_pass', trim($_POST['email_pass']));
        $email_test = trim($_POST['email_test']);
        $subject = $lang['test_email'];
        $site_url = BASE_SITE_URL;
        $site_title = core\config::get('site_name');
        $message = '<p>' . $lang['this_is_to'] . '<a href=\'' . $site_url . '\' target=\'_blank\'>' . $site_title . '</a>' . $lang['test_email_send_ok'] . '</p>';
		$result = send_email($email_test, $subject, $message);
		if ($result !== true) {
            $message = $result;
            if (strtoupper(CHARSET) == 'GBK') {
                $message = core\language::getUTF8($message);
            }
			$Data = array(
			    'msg' => $message,
			);
            ajaxReturn($Data);
        } else {
            $message = $lang['test_email_send_ok'];
            if (strtoupper(CHARSET) == 'GBK') {
                $message = core\language::getUTF8($message);
            }
            $Data = array(
			    'msg' => $message,
			);
            ajaxReturn($Data);
        }
    }
    /**
     * 商家消息模板
     */
    public function seller_tplOp()
    {
        $mstpl_list = model('store_msg_tpl')->getStoreMsgTplList(array());
        core\tpl::output('mstpl_list', $mstpl_list);
        core\tpl::output('top_link', $this->sublink($this->links, 'seller_tpl'));
        core\tpl::showpage('message.seller_tpl');
    }
    /**
     * 商家消息模板编辑
     */
    public function seller_tpl_editOp()
    {
        if (chksubmit()) {
            $code = trim($_POST['code']);
            $type = trim($_POST['type']);
            if (empty($code) || empty($type)) {
                error(lang('param_error'));
            }
            switch ($type) {
                case 'message':
                    $this->seller_tpl_update_message();
                    break;
                case 'short':
                    $this->seller_tpl_update_short();
                    break;
                case 'mail':
                    $this->seller_tpl_update_mail();
                    break;
            }
        }
        $code = trim($_GET['code']);
        if (empty($code)) {
            error(lang('param_error'));
        }
        $where = array();
        $where['smt_code'] = $code;
        $smtpl_info = model('store_msg_tpl')->getStoreMsgTplInfo($where);
        core\tpl::output('smtpl_info', $smtpl_info);
        $this->links[] = array('url' => 'act=message&op=seller_tpl_edit', 'lang' => 'seller_tpl_edit');
        core\tpl::output('top_link', $this->sublink($this->links, 'seller_tpl_edit'));
        core\tpl::showpage('message.seller_tpl.edit');
    }
    /**
     * 商家消息模板更新站内信
     */
    private function seller_tpl_update_message()
    {
        $message_content = trim($_POST['message_content']);
        if (empty($message_content)) {
            error('请填写站内信模板内容。');
        }
        // 条件
        $where = array();
        $where['smt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['smt_message_switch'] = intval($_POST['message_switch']);
        $update['smt_message_content'] = $message_content;
        $update['smt_message_forced'] = intval($_POST['message_forced']);
        $result = model('store_msg_tpl')->editStoreMsgTpl($where, $update);
        $this->seller_tpl_update_showmessage($result);
    }
    /**
     * 商家消息模板更新短消息
     */
    private function seller_tpl_update_short()
    {
        $short_content = trim($_POST['short_content']);
        if (empty($short_content)) {
            error('请填写短消息模板内容。');
        }
        // 条件
        $where = array();
        $where['smt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['smt_short_switch'] = intval($_POST['short_switch']);
        $update['smt_short_content'] = $short_content;
        $update['smt_short_forced'] = intval($_POST['short_forced']);
        $result = model('store_msg_tpl')->editStoreMsgTpl($where, $update);
        $this->seller_tpl_update_showmessage($result);
    }
    /**
     * 商家消息模板更新邮件
     */
    private function seller_tpl_update_mail()
    {
        $mail_subject = trim($_POST['mail_subject']);
        $mail_content = trim($_POST['mail_content']);
        if (empty($mail_subject) || empty($mail_content)) {
            error('请填写邮件模板内容。');
        }
        // 条件
        $where = array();
        $where['smt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['smt_mail_switch'] = intval($_POST['mail_switch']);
        $update['smt_mail_subject'] = $mail_subject;
        $update['smt_mail_content'] = $mail_content;
        $update['smt_mail_forced'] = intval($_POST['mail_forced']);
        $result = model('store_msg_tpl')->editStoreMsgTpl($where, $update);
        $this->seller_tpl_update_showmessage($result);
    }
    private function seller_tpl_update_showmessage($result)
    {
        if ($result) {
            success(lang('nc_common_op_succ'), urlAdmin('message', 'seller_tpl'));
        } else {
            error(lang('nc_common_op_fail'));
        }
    }
    /**
     * 用户消息模板
     */
    public function member_tplOp()
    {
        $mmtpl_list = model('member_msg_tpl')->getMemberMsgTplList(array());
        core\tpl::output('mmtpl_list', $mmtpl_list);
        core\tpl::output('top_link', $this->sublink($this->links, 'member_tpl'));
        core\tpl::showpage('message.member_tpl');
    }
    /**
     * 用户消息模板编辑
     */
    public function member_tpl_editOp()
    {
        if (chksubmit()) {
            $code = trim($_POST['code']);
            $type = trim($_POST['type']);
            if (empty($code) || empty($type)) {
                error(lang('param_error'));
            }
            switch ($type) {
                case 'message':
                    $this->member_tpl_update_message();
                    break;
                case 'short':
                    $this->member_tpl_update_short();
                    break;
                case 'mail':
                    $this->member_tpl_update_mail();
                    break;
            }
        }
        $code = trim($_GET['code']);
        if (empty($code)) {
            error(lang('param_error'));
        }
        $where = array();
        $where['mmt_code'] = $code;
        $mmtpl_info = model('member_msg_tpl')->getMemberMsgTplInfo($where);
        core\tpl::output('mmtpl_info', $mmtpl_info);
        $this->links[] = array('url' => 'act=message&op=member_tpl_edit', 'lang' => 'member_tpl_edit');
        core\tpl::output('top_link', $this->sublink($this->links, 'member_tpl_edit'));
        core\tpl::showpage('message.member_tpl.edit');
    }
    /**
     * 商家消息模板更新站内信
     */
    private function member_tpl_update_message()
    {
        $message_content = trim($_POST['message_content']);
        if (empty($message_content)) {
            error('请填写站内信模板内容。');
        }
        // 条件
        $where = array();
        $where['mmt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['mmt_message_switch'] = intval($_POST['message_switch']);
        $update['mmt_message_content'] = $message_content;
        $result = model('member_msg_tpl')->editMemberMsgTpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }
    /**
     * 商家消息模板更新短消息
     */
    private function member_tpl_update_short()
    {
        $short_content = trim($_POST['short_content']);
        if (empty($short_content)) {
            error('请填写短消息模板内容。');
        }
        // 条件
        $where = array();
        $where['mmt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['mmt_short_switch'] = intval($_POST['short_switch']);
        $update['mmt_short_content'] = $short_content;
        $result = model('member_msg_tpl')->editMemberMsgTpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }
    /**
     * 商家消息模板更新邮件
     */
    private function member_tpl_update_mail()
    {
        $mail_subject = trim($_POST['mail_subject']);
        $mail_content = trim($_POST['mail_content']);
        if (empty($mail_subject) || empty($mail_content)) {
            error('请填写邮件模板内容。');
        }
        // 条件
        $where = array();
        $where['mmt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['mmt_mail_switch'] = intval($_POST['mail_switch']);
        $update['mmt_mail_subject'] = $mail_subject;
        $update['mmt_mail_content'] = $mail_content;
        $result = model('member_msg_tpl')->editMemberMsgTpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }
    private function member_tpl_update_showmessage($result)
    {
        if ($result) {
            success(lang('nc_common_op_succ'), urlAdmin('message', 'member_tpl'));
        } else {
            error(lang('nc_common_op_fail'));
        }
    }
}