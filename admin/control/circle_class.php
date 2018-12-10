<?php
/**
 * 圈子分类管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class circle_class extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('circle');
    }
    /**
     * 圈子分类列表
     */
    public function class_listOp()
    {
        $model = model();
        if (chksubmit()) {
            // 批量删除
            if ($_POST['submit_type'] == 'batchdel') {
                $id_array = $_POST['check_class_id'];
                if (empty($id_array) && !is_array($id_array)) {
                    error(lang('circle_please_choose_class'));
                }
                $where = array('class_id' => array('in', $id_array));
                $model->table('circle_class')->where($where)->delete();
                success(lang('nc_common_op_succ'));
            }
        }
        $where = array();
        if (trim($_GET['searchname']) != '') {
            $where['class_name'] = array('like', '%' . trim($_GET['searchname']) . '%');
        }
        if (trim($_GET['searchstatus']) != '') {
            $where['class_status'] = intval($_GET['searchstatus']);
        }
        $class_list = $model->table('circle_class')->where($where)->order('class_sort asc')->select();
        core\tpl::output('class_list', $class_list);
        core\tpl::showpage('circle_class.list');
    }
    /**
     * 圈子分类添加
     */
    public function class_addOp()
    {
        $model = model();
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["class_name"], "require" => "true", "message" => lang('circle_class_name_not_null')), array("input" => $_POST["class_sort"], "require" => "true", 'validator' => 'Number', "message" => lang('circle_class_sort_is_number')));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $insert = array();
                $insert['class_name'] = trim($_POST['class_name']);
                $insert['class_sort'] = intval($_POST['class_sort']);
                $insert['class_status'] = intval($_POST['status']);
                $insert['is_recommend'] = intval($_POST['recommend']);
                $insert['class_addtime'] = time();
                $result = $model->table('circle_class')->insert($insert);
                if ($result) {
                    $url = array(array('url' => 'index.php?act=circle_class&op=class_add', 'msg' => lang('circle_continue_add')), array('url' => 'index.php?act=circle_class&op=class_list', 'msg' => lang('circle_return_list')));
                    success(lang('nc_common_op_succ'), $url);
                } else {
                    error(lang('nc_common_op_fail'));
                }
            }
        }
        // 商品分类
        $gc_list = model('goods_class')->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        core\tpl::showpage('circle_class.add');
    }
    /**
     * 圈子分类编辑
     */
    public function class_editOp()
    {
        $model = model();
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["class_name"], "require" => "true", "message" => lang('circle_class_name_not_null')), array("input" => $_POST["class_sort"], "require" => "true", 'validator' => 'Number', "message" => lang('circle_class_sort_is_number')));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $update = array();
                $update['class_id'] = intval($_POST['class_id']);
                $update['class_name'] = trim($_POST['class_name']);
                $update['class_sort'] = intval($_POST['class_sort']);
                $update['class_status'] = intval($_POST['status']);
                $update['is_recommend'] = intval($_POST['recommend']);
                $result = $model->table('circle_class')->update($update);
                if ($result) {
                    success(lang('nc_common_op_succ'), 'index.php?act=circle_class&op=class_list');
                } else {
                    error(lang('nc_common_op_fail'));
                }
            }
        }
        $id = intval($_GET['classid']);
        if ($id <= 0) {
            error(lang('param_error'));
        }
        $class_info = $model->table('circle_class')->find($id);
        core\tpl::output('class_info', $class_info);
        // 商品分类
        $gc_list = model('goods_class')->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        core\tpl::showpage('circle_class.edit');
    }
    /**
     * 删除分类
     */
    public function class_delOp()
    {
        $id = intval($_GET['classid']);
        if ($id <= 0) {
            error(lang('param_error'));
        }
        $model = model();
        $model->table('circle_class')->delete($id);
        success(lang('nc_common_op_succ'));
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            case 'recommend':
            case 'status':
            case 'sort':
            case 'name':
                $update = array('class_id' => intval($_GET['id']), $_GET['column'] => $_GET['value']);
                model()->table('circle_class')->update($update);
                echo 'true';
                break;
        }
    }
}