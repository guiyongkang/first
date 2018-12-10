<?php
/**
 * 店铺分类管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class store_class extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('store_class');
    }
    /**
     * 店铺分类
     */
    public function store_classOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('store_class');
        //删除
        if (chksubmit()) {
            if (!empty($_POST['check_sc_id']) && is_array($_POST['check_sc_id'])) {
                $result = $model_class->delStoreClass(array('sc_id' => array('in', $_POST['check_sc_id'])));
                if ($result) {
                    $this->log(lang('nc_del,store_class') . '[ID:' . implode(',', $_POST['check_sc_id']) . ']', 1);
                    success($lang['nc_common_del_succ']);
                }
            }
            error($lang['nc_common_del_fail']);
        }
        $store_class_list = $model_class->getStoreClassList(array(), 20);
        core\tpl::output('class_list', $store_class_list);
        core\tpl::output('page', $model_class->showpage());
        core\tpl::showpage('store_class.index');
    }
    /**
     * 商品分类添加
     */
    public function store_class_addOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('store_class');
        if (chksubmit()) {
            //验证
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['sc_name'], 'require' => 'true', 'message' => $lang['store_class_name_no_null']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $insert_array = array();
                $insert_array['sc_name'] = $_POST['sc_name'];
                $insert_array['sc_bail'] = intval($_POST['sc_bail']);
                $insert_array['sc_sort'] = intval($_POST['sc_sort']);
                $result = $model_class->addStoreClass($insert_array);
                if ($result) {
                    $url = 'index.php?act=store_class&op=store_class';
                    $this->log(lang('nc_add,store_class') . '[' . $_POST['sc_name'] . ']', 1);
                    success($lang['nc_common_save_succ'], $url);
                } else {
                    error($lang['nc_common_save_fail']);
                }
            }
        }
        core\tpl::showpage('store_class.add');
    }
    /**
     * 编辑
     */
    public function store_class_editOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('store_class');
        if (chksubmit()) {
            //验证
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['sc_name'], 'require' => 'true', 'message' => $lang['store_class_name_no_null']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $update_array = array();
                $update_array['sc_name'] = $_POST['sc_name'];
                $update_array['sc_bail'] = intval($_POST['sc_bail']);
                $update_array['sc_sort'] = intval($_POST['sc_sort']);
                $result = $model_class->editStoreClass($update_array, array('sc_id' => intval($_POST['sc_id'])));
                if ($result) {
                    $this->log(lang('nc_edit,store_class') . '[' . $_POST['sc_name'] . ']', 1);
                    success($lang['nc_common_save_succ'], 'index.php?act=store_class&op=store_class');
                } else {
                    error($lang['nc_common_save_fail']);
                }
            }
        }
        $class_array = $model_class->getStoreClassInfo(array('sc_id' => intval($_GET['sc_id'])));
        if (empty($class_array)) {
            error($lang['illegal_parameter']);
        }
        core\tpl::output('class_array', $class_array);
        core\tpl::showpage('store_class.edit');
    }
    /**
     * 删除分类
     */
    public function store_class_delOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('store_class');
        if (intval($_GET['sc_id']) > 0) {
            $array = array(intval($_GET['sc_id']));
            $result = $model_class->delStoreClass(array('sc_id' => intval($_GET['sc_id'])));
            if ($result) {
                $this->log(lang('nc_del,store_class') . '[ID:' . $_GET['sc_id'] . ']', 1);
                success($lang['nc_common_del_succ'], getReferer());
            }
        }
        error($lang['nc_common_del_fail'], 'index.php?act=store_class&op=store_class');
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        $model_class = model('store_class');
        $update_array = array();
        switch ($_GET['branch']) {
            //分类：验证是否有重复的名称
            case 'store_class_name':
                $condition = array();
                $condition['sc_name'] = $_GET['value'];
                $condition['sc_id'] = array('sc_id' => array('neq', isset($_GET['sc_id']) ? intval($_GET['sc_id']) : 0));
                $class_list = $model_class->getStoreClassList($condition);
                if (empty($class_list)) {
                    $update_array['sc_name'] = $_GET['value'];
                    $update = $model_class->editStoreClass($update_array, array('sc_id' => isset($_GET['id']) ? intval($_GET['id']) : 0));
                    $return = $update ? 'true' : 'false';
                } else {
                    $return = 'false';
                }
                break;
                //分类： 排序 显示 设置
            //分类： 排序 显示 设置
            case 'store_class_sort':
                $update_array['sc_sort'] = intval($_GET['value']);
                $result = $model_class->editStoreClass($update_array, array('sc_id' => intval($_GET['id'])));
                $return = $result ? 'true' : 'false';
                break;
                //分类：添加、修改操作中 检测类别名称是否有重复
            //分类：添加、修改操作中 检测类别名称是否有重复
            case 'check_class_name':
                $condition['sc_name'] = isset($_GET['sc_name']) ? $_GET['sc_name'] : '';
                $condition['sc_id'] = array('sc_id' => array('neq', isset($_GET['sc_id']) ? intval($_GET['sc_id']) : ''));
                $class_list = $model_class->getStoreClassList($condition);
                $return = empty($class_list) ? 'true' : 'false';
                break;
        }
        exit($return);
    }
}