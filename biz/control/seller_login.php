<?php
/**
 * 店铺卖家登录
 **/
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class seller_login extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
        if (core\session::get('seller_id')) {
            header('location: index.php');
            die;
        }
    }
    public function indexOp()
    {
        $this->show_loginOp();
    }
    public function show_loginOp()
    {
        core\tpl::output('nchash', getNchash());
        core\tpl::setLayout('null_layout');
        core\tpl::showpage('login');
    }
    public function loginOp()
    {
        $result = chksubmit(true, true, 'num');
        if ($result) {
            if ($result === -11) {
                showDialog('用户名或密码错误', '', 'error');
            } elseif ($result === -12) {
                showDialog('验证码错误', '', 'error');
            }
        } else {
            showDialog('非法提交', '', 'error');
        }
        $model_seller = model('seller');
        $seller_info = $model_seller->getSellerInfo(array('seller_name' => $_POST['seller_name']));
        if ($seller_info) {
            $model_member = model('member');
            $member_info = $model_member->getMemberInfo(array('member_id' => $seller_info['member_id'], 'member_passwd' => md5($_POST['password'])));
            if ($member_info) {
                // 更新卖家登陆时间
                $model_seller->editSeller(array('last_login_time' => TIMESTAMP), array('seller_id' => $seller_info['seller_id']));
                $model_seller_group = model('seller_group');
                $seller_group_info = $model_seller_group->getSellerGroupInfo(array('group_id' => $seller_info['seller_group_id']));
                $model_store = model('store');
                $store_info = $model_store->getStoreInfoByID($seller_info['store_id']);
                core\session::set('is_login', '1');
                core\session::set('member_id', $member_info['member_id']);
                core\session::set('member_name', $member_info['member_name']);
                core\session::set('member_email', $member_info['member_email']);
                core\session::set('is_buy', $member_info['is_buy']);
                core\session::set('avatar', $member_info['member_avatar']);
                core\session::set('grade_id', $store_info['grade_id']);
                core\session::set('seller_id', $seller_info['seller_id']);
                core\session::set('seller_name', $seller_info['seller_name']);
                core\session::set('seller_is_admin', intval($seller_info['is_admin']));
                core\session::set('store_id', intval($seller_info['store_id']));
                core\session::set('store_name', $store_info['store_name']);
                core\session::set('is_own_shop', (bool) $store_info['is_own_shop']);
                core\session::set('bind_all_gc', (bool) $store_info['bind_all_gc']);
                core\session::set('seller_limits', isset($seller_group_info['limits']) ? explode(',', $seller_group_info['limits']) : array());
                if ($seller_info['is_admin']) {
                    core\session::set('seller_group_name', '管理员');
                    core\session::set('seller_smt_limits', false);
                } else {
                    core\session::set('seller_group_name', isset($seller_group_info['group_name']) ? $seller_group_info['group_name'] : '');
                    core\session::set('seller_smt_limits', isset($seller_group_info['smt_limits']) ? explode(',', $seller_group_info['smt_limits']) : array());
                }
                if (!$seller_info['last_login_time']) {
                    $seller_info['last_login_time'] = TIMESTAMP;
                }
                core\session::set('seller_last_login_time', date('Y-m-d H:i', $seller_info['last_login_time']));
                $seller_menu = $this->getSellerMenuList($seller_info['is_admin'], core\session::get('seller_limits'));
                core\session::set('seller_menu', $seller_menu['seller_menu']);
                core\session::set('seller_function_list', $seller_menu['seller_function_list']);
                if (!empty($seller_info['seller_quicklink'])) {
                    $quicklink_array = explode(',', $seller_info['seller_quicklink']);
                    foreach ($quicklink_array as $value) {
                        core\session::set('seller_quicklink.' . $value, $value);
                    }
                }
                $this->recordSellerLog('登录成功');
                redirect('index.php');
            } else {
                error('用户名密码错误');
            }
        } else {
            error('用户名密码错误');
        }
    }
}