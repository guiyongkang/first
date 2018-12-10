<?php
/**
 * cms文章分类
 *
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class cms_article_class extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('cms');
    }
    public function indexOp()
    {
        $this->cms_article_class_listOp();
    }
    /**
     * cms文章分类列表
     **/
    public function cms_article_class_listOp()
    {
        $model = model('cms_article_class');
        $list = $model->getList(TRUE);
        $this->show_menu('list');
        core\tpl::output('list', $list);
        core\tpl::showpage("cms_article_class.list");
    }
    /**
     * cms文章分类添加
     **/
    public function cms_article_class_addOp()
    {
        $this->show_menu('add');
        core\tpl::showpage('cms_article_class.add');
    }
    /**
     * cms文章分类保存
     **/
    public function cms_article_class_saveOp()
    {
        $obj_validate = new lib\validate();
        $validate_array = array(array('input' => $_POST['class_name'], 'require' => 'true', "validator" => "Length", "min" => "1", "max" => "10", 'message' => core\language::get('class_name_error')), array('input' => $_POST['class_sort'], 'require' => 'true', 'validator' => 'Range', 'min' => 0, 'max' => 255, 'message' => core\language::get('class_sort_error')));
        $obj_validate->validateparam = $validate_array;
        $error = $obj_validate->validate();
        if ($error != '') {
            error(core\language::get('error') . $error);
        }
        $param = array();
        $param['class_name'] = trim($_POST['class_name']);
        $param['class_sort'] = intval($_POST['class_sort']);
        $model_class = model('cms_article_class');
        $result = $model_class->save($param);
        if ($result) {
            $this->log(core\language::get('cms_log_article_class_save') . $result, 1);
            showMessage(core\language::get('class_add_success'), 'index.php?act=cms_article_class&op=cms_article_class_list');
        } else {
            $this->log(core\language::get('cms_log_article_class_save') . $result, 0);
            error(core\language::get('class_add_fail'), 'index.php?act=cms_article_class&op=cms_article_class_list');
        }
    }
    /**
     * cms文章分类排序修改
     */
    public function update_class_sortOp()
    {
        if (intval($_GET['id']) <= 0) {
            echo json_encode(array('result' => FALSE, 'message' => core\language::get('param_error')));
            die;
        }
        $new_sort = intval($_GET['value']);
        if ($new_sort > 255) {
            echo json_encode(array('result' => FALSE, 'message' => core\language::get('class_sort_error')));
            die;
        } else {
            $model_class = model("cms_article_class");
            $result = $model_class->modify(array('class_sort' => $new_sort), array('class_id' => $_GET['id']));
            if ($result) {
                echo json_encode(array('result' => TRUE, 'message' => 'class_add_success'));
                die;
            } else {
                echo json_encode(array('result' => FALSE, 'message' => core\language::get('class_add_fail')));
                die;
            }
        }
    }
    public function update_class_nameOp()
    {
        $class_id = intval($_GET['id']);
        if ($class_id <= 0) {
            echo json_encode(array('result' => FALSE, 'message' => core\language::get('param_error')));
            die;
        }
        $new_name = trim($_GET['value']);
        $obj_validate = new lib\validate();
        $obj_validate->validateparam = array(array('input' => $new_name, 'require' => 'true', "validator" => "Length", "min" => "1", "max" => "10", 'message' => core\language::get('class_name_error')));
        $error = $obj_validate->validate();
        if ($error != '') {
            echo json_encode(array('result' => FALSE, 'message' => core\language::get('class_name_error')));
            die;
        } else {
            $model_class = model("cms_article_class");
            $result = $model_class->modify(array('class_name' => $new_name), array('class_id' => $class_id));
            if ($result) {
                echo json_encode(array('result' => TRUE, 'message' => 'class_add_success'));
                die;
            } else {
                echo json_encode(array('result' => FALSE, 'message' => core\language::get('class_add_fail')));
                die;
            }
        }
    }
    /**
     * cms文章分类删除
     **/
    public function cms_article_class_dropOp()
    {
        $class_id = trim($_POST['class_id']);
        $model_class = model('cms_article_class');
        $condition = array();
        $condition['class_id'] = array('in', $class_id);
        $result = $model_class->drop($condition);
        if ($result) {
            $this->log(core\language::get('cms_log_article_class_drop') . $_POST['class_id'], 1);
            success(core\language::get('class_drop_success'));
        } else {
            $this->log(core\language::get('cms_log_article_class_drop') . $_POST['class_id'], 0);
            error(core\language::get('class_drop_fail'));
        }
    }
    private function show_menu($menu_key)
    {
        $menu_array = array('list' => array('menu_type' => 'link', 'menu_name' => core\language::get('nc_list'), 'menu_url' => 'index.php?act=cms_article_class&op=cms_article_class_list'), 'add' => array('menu_type' => 'link', 'menu_name' => core\language::get('nc_new'), 'menu_url' => 'index.php?act=cms_article_class&op=cms_article_class_add'));
        $menu_array[$menu_key]['menu_type'] = 'text';
        core\tpl::output('menu', $menu_array);
    }
}