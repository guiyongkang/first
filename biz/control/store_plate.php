<?php
/**
 * 关联版式
 **/
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class store_plate extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function indexOp()
    {
        $this->plate_listOp();
    }
    /**
     * 关联版式列表
     */
    public function plate_listOp()
    {
        // 版式列表
        $where = array();
        $where['store_id'] = core\session::get('store_id');
        if (!empty($_GET['p_name'])) {
            $where['plate_name'] = array('like', '%' . trim($_GET['p_name']) . '%');
        }
        if (isset($_GET['p_position']) && in_array($_GET['p_position'], array('0', '1'))) {
            $where['plate_position'] = $_GET['p_position'];
        }
        $store_plate = model('store_plate');
        $plate_list = $store_plate->getStorePlateList($where, '*', 10);
        core\tpl::output('show_page', $store_plate->showpage(2));
        core\tpl::output('plate_list', $plate_list);
        core\tpl::output('position', array(0 => '底部', 1 => '顶部'));
        $this->profile_menu('plate_list', 'plate_list');
        core\tpl::showpage('store_plate.list');
    }
    /**
     * 关联版式添加
     */
    public function plate_addOp()
    {
        if (chksubmit()) {
            // 验证表单
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['p_name'], 'require' => 'true', 'message' => '请填写版式名称'), array('input' => $_POST['p_content'], 'require' => 'true', 'message' => '请填写版式内容'));
            $error = $obj_validate->validate();
            if ($error != '') {
                showDialog(lang('error') . $error, urlBiz('store_plate', 'index'));
            }
            $insert = array();
            $insert['plate_name'] = $_POST['p_name'];
            $insert['plate_position'] = $_POST['p_position'];
            $insert['plate_content'] = $_POST['p_content'];
            $insert['store_id'] = $_SESSION['store_id'];
            $result = model('store_plate')->addStorePlate($insert);
            if ($result) {
                showDialog(lang('nc_common_op_succ'), urlBiz('store_plate', 'index'), 'succ');
            } else {
                showDialog(lang('nc_common_op_fail'), urlBiz('store_plate', 'index'));
            }
        }
        // 是否能使用编辑器
        if (checkPlatformStore()) {
            // 平台店铺可以使用编辑器
            $editor_multimedia = true;
        } else {
            // 三方店铺需要
            $editor_multimedia = false;
            if ($this->store_grade['sg_function'] == 'editor_multimedia') {
                $editor_multimedia = true;
            }
        }
        core\tpl::output('editor_multimedia', $editor_multimedia);
        $this->profile_menu('plate_add', 'plate_add');
        core\tpl::showpage('store_plate.add');
    }
    /**
     * 关联版式编辑
     */
    public function plate_editOp()
    {
        if (chksubmit()) {
            $plate_id = intval($_POST['p_id']);
            if ($plate_id <= 0) {
                error(lang('wrong_argument'));
            }
            // 验证表单
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(array('input' => $_POST['p_name'], 'require' => 'true', 'message' => '请填写版式名称'), array('input' => $_POST['p_content'], 'require' => 'true', 'message' => '请填写版式内容'));
            $error = $obj_validate->validate();
            if ($error != '') {
                showDialog(lang('error') . $error, urlBiz('store_plate', 'index'));
            }
            $update = array();
            $update['plate_name'] = $_POST['p_name'];
            $update['plate_position'] = $_POST['p_position'];
            $update['plate_content'] = $_POST['p_content'];
            $where = array();
            $where['plate_id'] = $plate_id;
            $where['store_id'] = $_SESSION['store_id'];
            $result = model('store_plate')->editStorePlate($update, $where);
            if ($result) {
                showDialog(lang('nc_common_op_succ'), urlBiz('store_plate', 'index'), 'succ');
            } else {
                showDialog(lang('nc_common_op_fail'), urlBiz('store_plate', 'index'));
            }
        }
        $plate_id = intval($_GET['p_id']);
        if ($plate_id <= 0) {
            error(lang('wrong_argument'));
        }
        $plate_info = model('store_plate')->getStorePlateInfo(array('plate_id' => $plate_id, 'store_id' => $_SESSION['store_id']));
        core\tpl::output('plate_info', $plate_info);
        $this->profile_menu('plate_edit', 'plate_edit');
        core\tpl::showpage('store_plate.add');
    }
    /**
     * 删除关联版式
     */
    public function drop_plateOp()
    {
        $plate_id = $_GET['p_id'];
        if (!preg_match('/^[\\d,]+$/i', $plate_id)) {
            showDialog(lang('wrong_argument'), '', 'error');
        }
        $plateid_array = explode(',', $plate_id);
        $return = model('store_plate')->delStorePlate(array('plate_id' => array('in', $plateid_array), 'store_id' => $_SESSION['store_id']));
        if ($return) {
            showDialog(lang('nc_common_del_succ'), 'reload', 'succ');
        } else {
            showDialog(lang('nc_common_del_fail'), '', 'error');
        }
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_type, $menu_key = '', $array = array())
    {
        $menu_array = array();
        switch ($menu_type) {
            case 'plate_list':
                $menu_array = array(array('menu_key' => 'plate_list', 'menu_name' => '版式列表', 'menu_url' => urlBiz('store_plate', 'plate_list')));
                break;
            case 'plate_add':
                $menu_array = array(array('menu_key' => 'plate_list', 'menu_name' => '版式列表', 'menu_url' => urlBiz('store_plate', 'plate_list')), array('menu_key' => 'plate_add', 'menu_name' => '添加版式', 'menu_url' => urlBiz('store_plate', 'plate_add')));
                break;
            case 'plate_edit':
                $menu_array = array(array('menu_key' => 'plate_list', 'menu_name' => '版式列表', 'menu_url' => urlBiz('store_plate', 'plate_list')), array('menu_key' => 'plate_add', 'menu_name' => '添加版式', 'menu_url' => urlBiz('store_plate', 'plate_add')), array('menu_key' => 'plate_edit', 'menu_name' => '编辑版式', 'menu_url' => urlBiz('store_plate', 'plate_edit')));
                break;
        }
        if (!empty($array)) {
            $menu_array[] = $array;
        }
        core\tpl::output('member_menu', $menu_array);
        core\tpl::output('menu_key', $menu_key);
    }
}