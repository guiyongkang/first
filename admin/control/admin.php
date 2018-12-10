<?php
/**
 * 权限管理
 *
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class admin extends SystemControl
{
    private $links = array(array('url' => 'act=admin&op=admin', 'lang' => 'limit_admin'), array('url' => 'act=admin&op=admin_add', 'lang' => 'admin_add_limit_admin'), array('url' => 'act=admin&op=gadmin', 'lang' => 'limit_gadmin'), array('url' => 'act=admin&op=gadmin_add', 'lang' => 'admin_add_limit_gadmin'));
    public function __construct()
    {
        parent::__construct();
        core\language::read('admin');
    }
    /**
     * 管理员列表
     */
    public function adminOp()
    {
        $model = model();
        if (chksubmit()) {
            //ID为1的会员不允许删除
            if (isset($_POST['del_id']) && in_array(1, $_POST['del_id'])) {
                error(lang('admin_index_not_allow_del'));
            }
            if (!empty($_POST['del_id'])) {
                if (is_array($_POST['del_id'])) {
                    foreach ($_POST['del_id'] as $k => $v) {
                        $model->table('admin')->where(array('admin_id' => intval($v)))->delete();
                    }
                }
                $this->log(lang('nc_delete,limit_admin'), 1);
                success(lang('nc_common_del_succ'));
            } else {
                success(lang('nc_common_del_succ'));
            }
        }
        $admin_list = $model->table('admin,gadmin')->join('left join')->on('gadmin.gid=admin.admin_gid')->page(10)->select();
        core\tpl::output('admin_list', $admin_list);
        core\tpl::output('page', $model->showpage());
        core\tpl::output('top_link', $this->sublink($this->links, 'admin'));
        core\tpl::showpage('admin.index');
    }
    /**
     * 管理员删除
     */
    public function admin_delOp()
    {
        if (!empty($_GET['admin_id'])) {
            if ($_GET['admin_id'] == 1) {
                error(lang('nc_common_save_fail'));
            }
            model()->table('admin')->where(array('admin_id' => intval($_GET['admin_id'])))->delete();
            $this->log(lang('nc_delete,limit_admin') . '[ID:' . intval($_GET['admin_id']) . ']', 1);
            success(lang('nc_common_del_succ'));
        } else {
            error(lang('nc_common_del_fail'));
        }
    }
    /**
     * 管理员添加
     */
    public function admin_addOp()
    {
        if (chksubmit()) {
            $limit_str = '';
            $model_admin = model('admin');
            $param['admin_name'] = $_POST['admin_name'];
            $param['admin_gid'] = $_POST['gid'];
            $param['admin_password'] = md5($_POST['admin_password']);
            $rs = $model_admin->addAdmin($param);
            if ($rs) {
                $this->log(lang('nc_add,limit_admin') . '[' . $_POST['admin_name'] . ']', 1);
                success(lang('nc_common_save_succ'), 'index.php?act=admin&op=admin');
            } else {
                error(lang('nc_common_save_fail'));
            }
        }
        //得到权限组
        $gadmin = model('gadmin')->field('gname,gid')->select();
        core\tpl::output('gadmin', $gadmin);
        core\tpl::output('top_link', $this->sublink($this->links, 'admin_add'));
        core\tpl::output('limit', $this->permission());
        core\tpl::showpage('admin.add');
    }
    /**
     * 设置权限组权限
     */
    public function gadmin_setOp()
    {
        $model = model('gadmin');
        $gid = intval($_GET['gid']);
        $ginfo = $model->getby_gid($gid);
        if (empty($ginfo)) {
            error(lang('admin_set_admin_not_exists'));
        }
        if (chksubmit()) {
            $limit_str = '';
            if (is_array($_POST['permission'])) {
                $limit_str = implode('|', $_POST['permission']);
            }
            $limit_str = encrypt($limit_str, MD5_KEY . md5($_POST['gname']));
            $data['limits'] = $limit_str;
            $data['gname'] = $_POST['gname'];
            $update = $model->where(array('gid' => $gid))->update($data);
            if ($update) {
                $this->log(lang('nc_edit,limit_gadmin') . '[' . $_POST['gname'] . ']', 1);
                success(lang('nc_common_save_succ'), 'index.php?act=admin&op=gadmin');
            } else {
                success(lang('nc_common_save_succ'));
            }
        }
        //解析已有权限
        $hlimit = decrypt($ginfo['limits'], MD5_KEY . md5($ginfo['gname']));
        $ginfo['limits'] = explode('|', $hlimit);
        core\tpl::output('ginfo', $ginfo);
        core\tpl::output('limit', $this->permission());
        core\tpl::output('top_link', $this->sublink($this->links, 'gadmin'));
        core\tpl::showpage('gadmin.set');
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            //管理人员名称验证
            case 'check_admin_name':
                $model_admin = model('admin');
                $condition['admin_name'] = $_GET['admin_name'];
                $list = $model_admin->infoAdmin($condition);
                if (!empty($list)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
                //权限组名称验证
            //权限组名称验证
            case 'check_gadmin_name':
                $condition = array();
                if (isset($_GET['gid']) && is_numeric($_GET['gid'])) {
                    $condition['gid'] = array('neq', intval($_GET['gid']));
                }
                $condition['gname'] = isset($_GET['gname']) ? $_GET['gname'] : '';
                $info = model('gadmin')->where($condition)->find();
                if (!empty($info)) {
                    exit('false');
                } else {
                    exit('true');
                }
                break;
        }
    }
    /**
     * 设置管理员权限
     */
    public function admin_editOp()
    {
        if (chksubmit()) {
            //没有更改密码
            if (!empty($_POST['new_pw'])) {
                $data['admin_password'] = md5($_POST['new_pw']);
            }
            $data['admin_id'] = isset($_GET['admin_id']) ? intval($_GET['admin_id']) : 0;
            $data['admin_gid'] = isset($_POST['gid']) ? intval($_POST['gid']) : 0;
            //查询管理员信息
            $admin_model = model('admin');
            $result = $admin_model->updateAdmin($data);
            if ($result) {
                $this->log(lang('nc_edit,limit_admin') . '[ID:' . intval($_GET['admin_id']) . ']', 1);
                success(core\language::get('admin_edit_success'), 'index.php?act=admin&op=admin');
            } else {
                error(core\language::get('admin_edit_fail'), 'index.php?act=admin&op=admin');
            }
        } else {
            //查询用户信息
            $admin_model = model('admin');
            $admininfo = $admin_model->getOneAdmin(intval($_GET['admin_id']));
            if (!is_array($admininfo) || count($admininfo) <= 0) {
                error(core\language::get('admin_edit_admin_error'), 'index.php?act=admin&op=admin');
            }
            core\tpl::output('admininfo', $admininfo);
            core\tpl::output('top_link', $this->sublink($this->links, 'admin'));
            //得到权限组
            $gadmin = model('gadmin')->field('gname,gid')->select();
            core\tpl::output('gadmin', $gadmin);
            core\tpl::showpage('admin.edit');
        }
    }
    /**
     * 取得所有权限项
     *
     * @return array
     */
    private function permission()
    {
        core\language::read('common');
        $lang = core\language::getLangContent();
        $limit = (require BASE_PATH . '/include/limit.php');
        if (is_array($limit)) {
            foreach ($limit as $k => $v) {
                if (is_array($v['child'])) {
                    $tmp = array();
                    foreach ($v['child'] as $key => $value) {
                        $act = !empty($value['act']) ? $value['act'] : $v['act'];
                        if (strpos($act, '|') == false) {
                            //act参数不带|
                            $limit[$k]['child'][$key]['op'] = rtrim($act . '.' . str_replace('|', '|' . $act . '.', $value['op']), '.');
                        } else {
                            //act参数带|
                            $tmp_str = '';
                            if (empty($value['op'])) {
                                $limit[$k]['child'][$key]['op'] = $act;
                            } elseif (strpos($value['op'], '|') == false) {
                                //op参数不带|
                                foreach (explode('|', $act) as $v1) {
                                    $tmp_str .= "{$v1}.{$value['op']}|";
                                }
                                $limit[$k]['child'][$key]['op'] = rtrim($tmp_str, '|');
                            } elseif (strpos($value['op'], '|') != false && strpos($act, '|') != false) {
                                //op,act都带|，交差权限
                                foreach (explode('|', $act) as $v1) {
                                    foreach (explode('|', $value['op']) as $v2) {
                                        $tmp_str .= "{$v1}.{$v2}|";
                                    }
                                }
                                $limit[$k]['child'][$key]['op'] = rtrim($tmp_str, '|');
                            }
                        }
                    }
                }
            }
            return $limit;
        } else {
            return array();
        }
    }
    /**
     * 权限组
     */
    public function gadminOp()
    {
        $model = model('gadmin');
        if (chksubmit()) {
            if (isset($_POST['del_id']) && in_array(1, $_POST['del_id'])) {
                error(lang('admin_index_not_allow_del'));
            }
            if (!empty($_POST['del_id'])) {
                if (is_array($_POST['del_id'])) {
                    foreach ($_POST['del_id'] as $k => $v) {
                        $model->where(array('gid' => intval($v)))->delete();
                    }
                }
                $this->log(lang('nc_delete,limit_gadmin') . '[ID:' . implode(',', $_POST['del_id']) . ']', 1);
                success(lang('nc_common_del_succ'));
            } else {
                error(lang('nc_common_del_fail'));
            }
        }
        $list = $model->page(10)->select();
        core\tpl::output('list', $list);
        core\tpl::output('page', $model->showpage());
        core\tpl::output('top_link', $this->sublink($this->links, 'gadmin'));
        core\tpl::showpage('gadmin.index');
    }
    /**
     * 添加权限组
     */
    public function gadmin_addOp()
    {
        if (chksubmit()) {
            $limit_str = '';
            $model = model('gadmin');
            if (!empty($_POST['permission']) && is_array($_POST['permission'])) {
                $limit_str = implode('|', $_POST['permission']);
            }
            $data['limits'] = encrypt($limit_str, MD5_KEY . md5($_POST['gname']));
            $data['gname'] = $_POST['gname'];
            if ($model->insert($data)) {
                $this->log(lang('nc_add,limit_gadmin') . '[' . $_POST['gname'] . ']', 1);
                success(lang('nc_common_save_succ'), 'index.php?act=admin&op=gadmin');
            } else {
                error(lang('nc_common_save_fail'));
            }
        }
        core\tpl::output('top_link', $this->sublink($this->links, 'gadmin_add'));
        core\tpl::output('limit', $this->permission());
        core\tpl::showpage('gadmin.add');
    }
    /**
     * 组删除
     */
    public function gadmin_delOp()
    {
        if (isset($_GET['gid']) && is_numeric($_GET['gid'])) {
            model('gadmin')->where(array('gid' => intval($_GET['gid'])))->delete();
            $this->log(lang('nc_delete,limit_gadmin') . '[ID' . intval($_GET['gid']) . ']', 1);
            redirect();
        } else {
            error(lang('nc_common_op_fail'));
        }
    }
}