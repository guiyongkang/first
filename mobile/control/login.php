<?php
/**
 * 前台登录 退出操作  
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class login extends mobileHomeControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 登录
     */
    public function indexOp()
    {
        if (empty($_POST['username']) || empty($_POST['password']) || !in_array($_POST['client'], $this->client_type_array)) {
            output_error('登录失败');
        }
        $model_member = model('member');
        $array = array();
        $array['member_name'] = $_POST['username'];
        $array['member_passwd'] = md5($_POST['password']);
        $member_info = $model_member->getMemberInfo($array);
        if (empty($member_info) && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $_POST['username'])) {
            //根据会员名没找到时查手机号
            $array = array();
            $array['member_mobile'] = $_POST['username'];
            $array['member_passwd'] = md5($_POST['password']);
            $member_info = $model_member->getMemberInfo($array);
        }
        if (empty($member_info) && strpos($_POST['username'], '@') > 0) {
            //按邮箱和密码查询会员
            $array = array();
            $array['member_email'] = $_POST['username'];
            $array['member_passwd'] = md5($_POST['password']);
            $member_info = $model_member->getMemberInfo($array);
        }
        if (!empty($member_info) && is_array($member_info)) {
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if ($token) {
                $logindata = array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token);
				core\session::set('wap_member_info', $logindata);
                output_data($logindata);
            } else {
                output_error('登录失败');
            }
        } else {
            output_error('用户名密码错误');
        }
    }
    /**
     * 登录生成token
     */
    private function _get_token($member_id, $member_name, $client)
    {
        $model_mb_user_token = model('mb_user_token');
        //重新登录后以前的令牌失效
        //暂时停用
        //$condition = array();
        //$condition['member_id'] = $member_id;
        //$condition['client_type'] = $client;
        //$model_mb_user_token->delMbUserToken($condition);
        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0, 999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;
        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);
        if ($result) {
            return $token;
        } else {
            return null;
        }
    }
    /**
     * 注册 重复注册验证
     */
    public function registerOp()
    {
        if (lib\process::islock('reg')) {
            output_error('您的操作过于频繁，请稍后再试');
        }
		
        $model_member = model('member');
        $register_info = array();
        $register_info['username'] = $_POST['username'];
        $register_info['password'] = $_POST['password'];
        $register_info['password_confirm'] = $_POST['password_confirm'];
        $register_info['email'] = $_POST['email'];
        //添加奖励积分
        //$register_info['inviter_id'] = (isset($_COOKIE['uid']) ? intval(base64_decode($_COOKIE['uid'])) : 0) / 1;
		$register_info['inviter_id'] = core\session::get('uid') ?: 0;
        $member_info = $model_member->register($register_info);
        if (!isset($member_info['error'])) {
            lib\process::addprocess('reg');
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if ($token) {
				$update = $model_member->editMember(array('member_id' => $member_info['member_id']), array('member_paypwd' => md5($_POST['password'])));
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
            } else {
                output_error('注册失败');
            }
        } else {
            output_error($member_info['error']);
        }
    }
}