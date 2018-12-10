<?php
/**
 * 文章分类
 *
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class article_class extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('article_class');
    }
    /**
     * 文章管理
     */
    public function article_classOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('article_class');
        //删除
        if (chksubmit()) {
            if (!empty($_POST['check_ac_id'])) {
                if (is_array($_POST['check_ac_id'])) {
                    $del_array = $model_class->getChildClass($_POST['check_ac_id']);
                    if (is_array($del_array)) {
                        foreach ($del_array as $k => $v) {
                            $model_class->del($v['ac_id']);
                        }
                    }
                }
                $this->log(lang('nc_del,article_class_index_class'), 1);
                success($lang['article_class_index_del_succ']);
            } else {
                error($lang['article_class_index_choose']);
            }
        }
        /**
         * 父ID
         */
        $parent_id = !empty($_GET['ac_parent_id']) ? intval($_GET['ac_parent_id']) : 0;
        /**
         * 列表
         */
        $tmp_list = $model_class->getTreeClassList(2);
        if (is_array($tmp_list)) {
            foreach ($tmp_list as $k => $v) {
                if ($v['ac_parent_id'] == $parent_id) {
                    /**
                     * 判断是否有子类
                     */
                    if (isset($tmp_list[$k + 1]['deep']) && ($tmp_list[$k + 1]['deep'] > $v['deep'])) {
                        $v['have_child'] = 1;
                    }
                    $class_list[] = $v;
                }
            }
        }
        if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
            /**
             * 转码
             */
            if (strtoupper(CHARSET) == 'GBK') {
                $class_list = core\language::getUTF8($class_list);
            }
            $output = json_encode($class_list);
            print_r($output);
            exit;
        } else {
            core\tpl::output('class_list', $class_list);
            core\tpl::showpage('article_class.index');
        }
    }
    /**
     * 文章分类 新增
     */
    public function article_class_addOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('article_class');
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["ac_name"], "require" => "true", "message" => $lang['article_class_add_name_null']), array("input" => $_POST["ac_sort"], "require" => "true", 'validator' => 'Number', "message" => $lang['article_class_add_sort_int']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $insert_array = array();
                $insert_array['ac_name'] = trim($_POST['ac_name']);
                $insert_array['ac_parent_id'] = intval($_POST['ac_parent_id']);
                $insert_array['ac_sort'] = trim($_POST['ac_sort']);
                $result = $model_class->add($insert_array);
                if ($result) {
                    $url = 'index.php?act=article_class&op=article_class';
                    $this->log(lang('nc_add,article_class_index_class') . '[' . $_POST['ac_name'] . ']', 1);
                    success($lang['article_class_add_succ'], $url);
                } else {
                    error($lang['article_class_add_fail']);
                }
            }
        }
        /**
         * 父类列表，只取到第三级
         */
        $parent_list = $model_class->getTreeClassList(1);
        if (is_array($parent_list)) {
            foreach ($parent_list as $k => $v) {
                $parent_list[$k]['ac_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['ac_name'];
            }
        }
        core\tpl::output('ac_parent_id', isset($_GET['ac_parent_id']) ? intval($_GET['ac_parent_id']) : 0);
        core\tpl::output('parent_list', $parent_list);
        core\tpl::showpage('article_class.add');
    }
    /**
     * 文章分类编辑
     */
    public function article_class_editOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('article_class');
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["ac_name"], "require" => "true", "message" => $lang['article_class_add_name_null']), array("input" => $_POST["ac_sort"], "require" => "true", 'validator' => 'Number', "message" => $lang['article_class_add_sort_int']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $update_array = array();
                $update_array['ac_id'] = intval($_POST['ac_id']);
                $update_array['ac_name'] = trim($_POST['ac_name']);
                //				$update_array['ac_parent_id'] = intval($_POST['ac_parent_id']);
                $update_array['ac_sort'] = trim($_POST['ac_sort']);
                $result = $model_class->update($update_array);
                if ($result) {
                    $url = array(array('url' => 'index.php?act=article_class&op=article_class', 'msg' => $lang['article_class_add_back_to_list']), array('url' => 'index.php?act=article_class&op=article_class_edit&ac_id=' . intval($_POST['ac_id']), 'msg' => $lang['article_class_edit_again']));
                    $this->log(lang('nc_edit,article_class_index_class') . '[' . $_POST['ac_name'] . ']', 1);
                    success($lang['article_class_edit_succ'], 'index.php?act=article_class&op=article_class');
                } else {
                    error($lang['article_class_edit_fail']);
                }
            }
        }
        $class_array = $model_class->getOneClass(intval($_GET['ac_id']));
        if (empty($class_array)) {
            error($lang['param_error']);
        }
        core\tpl::output('class_array', $class_array);
        core\tpl::showpage('article_class.edit');
    }
    /**
     * 删除分类
     */
    public function article_class_delOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('article_class');
        if (intval($_GET['ac_id']) > 0) {
            $array = array(intval($_GET['ac_id']));
            $del_array = $model_class->getChildClass($array);
            if (is_array($del_array)) {
                foreach ($del_array as $k => $v) {
                    $model_class->del($v['ac_id']);
                }
            }
            $this->log(lang('nc_add,article_class_index_class') . '[ID:' . intval($_GET['ac_id']) . ']', 1);
            success($lang['article_class_index_del_succ'], 'index.php?act=article_class&op=article_class');
        } else {
            error($lang['article_class_index_choose'], 'index.php?act=article_class&op=article_class');
        }
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            /**
             * 分类：验证是否有重复的名称
             */
            case 'article_class_name':
                $model_class = model('article_class');
                $class_array = $model_class->getOneClass(intval($_GET['id']));
                $condition['ac_name'] = trim($_GET['value']);
                $condition['ac_parent_id'] = $class_array['ac_parent_id'];
                $condition['no_ac_id'] = intval($_GET['id']);
                $class_list = $model_class->getClassList($condition);
                if (empty($class_list)) {
                    $update_array = array();
                    $update_array['ac_id'] = intval($_GET['id']);
                    $update_array['ac_name'] = trim($_GET['value']);
                    $model_class->update($update_array);
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
                /**
                 * 分类： 排序 显示 设置
                 */
            /**
             * 分类： 排序 显示 设置
             */
            case 'article_class_sort':
                $model_class = model('article_class');
                $update_array = array();
                $update_array['ac_id'] = intval($_GET['id']);
                $update_array[$_GET['column']] = trim($_GET['value']);
                $result = $model_class->update($update_array);
                echo 'true';
                exit;
                break;
                /**
                 * 分类：添加、修改操作中 检测类别名称是否有重复
                 */
            /**
             * 分类：添加、修改操作中 检测类别名称是否有重复
             */
            case 'check_class_name':
                $model_class = model('article_class');
                $condition['ac_name'] = trim($_GET['ac_name']);
                $condition['ac_parent_id'] = intval($_GET['ac_parent_id']);
                $condition['no_ac_id'] = intval($_GET['ac_id']);
                $class_list = $model_class->getClassList($condition);
                if (empty($class_list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }
}