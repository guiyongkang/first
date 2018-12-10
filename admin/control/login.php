<?php
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class login extends SystemControl
{
    /**
     * 不进行父类的登录验证，所以增加构造方法重写了父类的构造方法
     */
    public function __construct()
    {
        core\language::read('common,layout,login');
        $result = chksubmit(true, true, 'num');
        if ($result) {
            if ($result === -11) {
                error('非法请求');
            } elseif ($result === -12) {
                error(lang('login_index_checkcode_wrong'));
            }
            if (lib\process::islock('admin')) {
                error('您的操作过于频繁，请稍后再试');
            }
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['user_name'], 'require' => 'true', 'message' => lang('login_index_username_null')), array('input' => $_POST['password'], 'require' => 'true', 'message' => lang('login_index_password_null')), array('input' => $_POST['captcha'], 'require' => 'true', 'message' => lang('login_index_checkcode_null')));
            $error = $obj_validate->validate();
            if ($error != '') {
                error(lang('error') . $error);
            } else {
                $model_admin = model('admin');
                $array = array();
                $array['admin_name'] = $_POST['user_name'];
                $array['admin_password'] = md5(trim($_POST['password']));
                $admin_info = $model_admin->infoAdmin($array);
                if (is_array($admin_info) and !empty($admin_info)) {
                    $this->systemSetKey(array('name' => $admin_info['admin_name'], 'id' => $admin_info['admin_id'], 'gid' => $admin_info['admin_gid'], 'sp' => $admin_info['admin_is_super']));
                    $update_info = array('admin_id' => $admin_info['admin_id'], 'admin_login_num' => $admin_info['admin_login_num'] + 1, 'admin_login_time' => TIMESTAMP);
                    $model_admin->updateAdmin($update_info);
                    $this->log(lang('nc_login'), 1);
                    lib\process::clear('admin');
                    header('Location: index.php');
                    exit;
                } else {
                    lib\process::addprocess('admin');
                    error(lang('login_index_username_password_wrong'), 'index.php?act=login&op=login');
                }
            }
        }
        core\tpl::output('html_title', lang('login_index_need_login'));
        core\tpl::showpage('login', 'login_layout');
    }
    public function loginOp()
    {
    }
    public function indexOp()
    {
    }
}