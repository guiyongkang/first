<?php
/**
 * 规格栏目管理
 *
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class spec extends SystemControl
{
    const EXPORT_SIZE = 5000;
    public function __construct()
    {
        parent::__construct();
        core\language::read('spec');
    }
    /**
     * 规格管理
     */
    public function specOp()
    {
        $lang = core\language::getLangContent();
        $model_spec = model('spec');
        $page = new lib\page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        $spec_list = $model_spec->specList(array('order' => 'sp_sort asc'), $page);
        core\tpl::output('spec_list', $spec_list);
        core\tpl::output('page', $page->show());
        core\tpl::showpage('spec.index');
    }
    /**
     * 添加规格
     */
    public function spec_addOp()
    {
        $lang = core\language::getLangContent();
        $model_spec = model('spec');
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["s_name"], "require" => "true", "message" => $lang['spec_add_name_no_null']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $spec = array();
                $spec['sp_name'] = $_POST['s_name'];
                $spec['sp_sort'] = intval($_POST['s_sort']);
                $spec['class_id'] = $_POST['class_id'];
                $spec['class_name'] = $_POST['class_name'];
                $return = $model_spec->addSpec($spec);
                if ($return) {
                    $url = 'index.php?act=spec&op=spec';
                    $this->log(lang('nc_add,spec_index_spec_name') . '[' . $_POST['s_name'] . ']', 1);
                    success($lang['nc_common_save_succ'], $url);
                } else {
                    $this->log(lang('nc_add,spec_index_spec_name') . '[' . $_POST['s_name'] . ']', 0);
                    error($lang['nc_common_save_fail']);
                }
            }
        }
        // 一级商品分类
        $gc_list = model('goods_class')->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        core\tpl::showpage('spec.add');
    }
    /**
     * 编辑规格
     */
    public function spec_editOp()
    {
        $lang = core\language::getLangContent();
        if (empty($_GET['sp_id'])) {
            error($lang['param_error']);
        }
        /**
         * 规格模型
         */
        $model_spec = model('spec');
        /**
         * 编辑保存
         */
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["s_name"], "require" => "true", "message" => $lang['spec_add_name_no_null']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                //更新规格表
                $param = array();
                $param['sp_name'] = trim($_POST['s_name']);
                $param['sp_sort'] = isset($_POST['s_sort']) ? intval($_POST['s_sort']) : 0;
                $param['class_id'] = $_POST['class_id'];
                $param['class_name'] = $_POST['class_name'];
                $return = $model_spec->specUpdate($param, array('sp_id' => intval($_POST['s_id'])), 'spec');
                if ($return) {
                    $url = 'index.php?act=spec&op=spec';
                    $this->log(lang('nc_edit,spec_index_spec_name') . '[' . $_POST['s_name'] . ']', 1);
                    success($lang['nc_common_save_succ'], $url);
                } else {
                    $this->log(lang('nc_edit,spec_index_spec_name') . '[' . $_POST['s_name'] . ']', 0);
                    error($lang['nc_common_save_fail']);
                }
            }
        }
        //规格列表
        $spec_list = $model_spec->getSpecInfo(intval($_GET['sp_id']));
        if (!$spec_list) {
            error($lang['param_error']);
        }
        // 一级商品分类
        $gc_list = model('goods_class')->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        core\tpl::output('sp_list', $spec_list);
        core\tpl::showpage('spec.edit');
    }
    /**
     * 删除规格
     */
    public function spec_delOp()
    {
        $lang = core\language::getLangContent();
        if (empty($_GET['del_id'])) {
            error($lang['param_error']);
        }
        //规格模型
        $model_spec = model('spec');
        if (is_array($_GET['del_id'])) {
            $id = "'" . implode("','", $_GET['del_id']) . "'";
        } else {
            $id = intval($_GET['del_id']);
        }
        //规格列表
        $spec_list = $model_spec->specList(array('in_sp_id' => $id));
        if (is_array($spec_list) && !empty($spec_list)) {
            // 删除类型与规格关联表
            $return = $model_spec->delSpec('type_spec', array('in_sp_id' => $id));
            if (!$return) {
                error($lang['nc_common_save_fail']);
            }
            //删除规格值表
            $return = $model_spec->delSpec('spec_value', array('in_sp_id' => $id));
            if (!$return) {
                error($lang['nc_common_save_fail']);
            }
            //删除规格表
            $return = $model_spec->delSpec('spec', array('in_sp_id' => $id));
            if (!$return) {
                error($lang['nc_common_save_fail']);
            }
            $this->log(lang('nc_delete,spec_index_spec_name') . '[ID:' . $id . ']', 1);
            success($lang['nc_common_del_succ']);
        } else {
            $this->log(lang('nc_delete,spec_index_spec_name') . '[ID:' . $id . ']', 0);
            error($lang['param_error']);
        }
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        //规格模型
        $model_spec = model('spec');
        switch ($_GET['branch']) {
            case 'sort':
                //			case 'name':
                $return = $model_spec->specUpdate(array($_GET['column'] => trim($_GET['value'])), array('sp_id' => intval($_GET['id'])), 'spec');
                if ($return) {
                    $this->log(lang('spec_index_spec_name,nc_sort') . '[ID:' . intval($_GET['id']) . ']', 1);
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }
    /**
     * 规格导出
     */
    public function export_step1Op()
    {
        $model_spec = model('spec');
        $page = new lib\page();
        $page->setEachNum(self::EXPORT_SIZE);
        $spec_list = $model_spec->specList(array('order' => 'sp_sort asc'), $page);
        if (!is_numeric($_GET['curpage'])) {
            $count = $page->getTotalNum();
            $array = array();
            if ($count > self::EXPORT_SIZE) {
                //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                core\tpl::output('list', $array);
                core\tpl::output('murl', 'index.php?act=spec&op=spec');
                core\tpl::showpage('export.excel');
            } else {
                //如果数量小，直接下载
                $this->createExcel($spec_list);
            }
        } else {
            //下载
            $this->createExcel($spec_list);
        }
    }
    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array())
    {
        core\language::read('export');
        $excel_obj = new lib\excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_spec'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_sp_content'));
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['sp_name']);
            $tmp[] = array('data' => $v['sp_value']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_spec'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_spec'), CHARSET) . $_GET['curpage'] . '-' . date('Y-m-d-H', time()));
    }
}