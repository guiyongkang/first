<?php
/**
 * 类型管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class type extends SystemControl
{
    const EXPORT_SIZE = 5000;
    public function __construct()
    {
        parent::__construct();
        core\language::read('type');
    }
    /**
     * 类型管理
     */
    public function typeOp()
    {
        $model_type = model('type');
        $page = new lib\page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        $type_list = $model_type->typeList(array('order' => 'type_sort asc'), $page);
        core\tpl::output('type_list', $type_list);
        core\tpl::output('page', $page->show());
        core\tpl::showpage('type.index');
    }
    /**
     * 添加类型
     */
    public function type_addOp()
    {
        $lang = core\language::getLangContent();
        $model_type = model('type');
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['t_mane'], 'require' => 'true', 'message' => $lang['type_add_name_no_null']), array('input' => $_POST['t_sort'], 'require' => 'true', 'validator' => 'Number', 'message' => $lang['type_add_sort_no_null']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            }
            $type_array = array();
            $type_array['type_name'] = trim($_POST['t_mane']);
            $type_array['type_sort'] = trim($_POST['t_sort']);
            $type_array['class_id'] = $_POST['class_id'];
            $type_array['class_name'] = $_POST['class_name'];
            $type_id = $model_type->typeAdd('type', $type_array);
            if (!$type_id) {
                error($lang['nc_common_save_fail']);
            }
            //添加类型与品牌对应
            if (!empty($_POST['brand_id'])) {
                $brand_array = $_POST['brand_id'];
                $return = $model_type->typeRelatedAdd('type_brand', $brand_array, $type_id, 'type_id,brand_id');
                if (!$return) {
                    error($lang['type_index_related_fail']);
                }
            }
            //添加类型与规格对应
            if (!empty($_POST['spec_id'])) {
                $spec_array = $_POST['spec_id'];
                $return = $model_type->typeRelatedAdd('type_spec', $spec_array, $type_id, 'type_id,sp_id');
                if (!$return) {
                    error($lang['type_index_related_fail']);
                }
            }
            //添加类型属性
            if (!empty($_POST['at_value'])) {
                $attribute_array = $_POST['at_value'];
                foreach ($attribute_array as $v) {
                    if ($v['value'] != '') {
                        // 转码  防止GBK下用中文逗号截取不正确
                        $comma = '，';
                        if (strtoupper(CHARSET) == 'GBK') {
                            $comma = core\language::getGBK($comma);
                        }
                        //属性值
                        //添加属性
                        $attr_array = array();
                        $attr_array['attr_name'] = $v['name'];
                        $attr_array['attr_value'] = $v['value'];
                        $attr_array['type_id'] = $type_id;
                        $attr_array['attr_sort'] = $v['sort'];
                        $attr_array['attr_show'] = $v['show'];
                        $attr_id = $model_type->typeAdd('attribute', $attr_array);
                        if (!$attr_id) {
                            error($lang['type_index_related_fail']);
                        }
                        //添加属性值
                        $attr_value = explode(',', $v['value']);
                        if (!empty($attr_value)) {
                            $attr_array = array();
                            foreach ($attr_value as $val) {
                                $tpl_array = array();
                                $tpl_array['attr_value_name'] = $val;
                                $tpl_array['attr_id'] = $attr_id;
                                $tpl_array['type_id'] = $type_id;
                                $tpl_array['attr_value_sort'] = 0;
                                $attr_array[] = $tpl_array;
                            }
                            $return = model('attribute')->addAttributeValueAll($attr_array);
                            if (!$return) {
                                error($lang['type_index_related_fail']);
                            }
                        }
                    }
                }
            }
            $url = 'index.php?act=type&op=type';
            $this->log(lang('nc_add,type_index_type_name') . '[' . $_POST['t_mane'] . ']', 1);
            success($lang['nc_common_save_succ'], $url);
        }
        // 品牌列表
        $model_brand = model('brand');
        $brand_list = $model_brand->getBrandPassedList(array());
        $b_list = array();
        if (is_array($brand_list) && !empty($brand_list)) {
            foreach ($brand_list as $k => $val) {
                $b_list[$val['class_id']]['brand'][$k] = $val;
                $b_list[$val['class_id']]['name'] = $val['brand_class'] == '' ? lang('nc_default') : $val['brand_class'];
            }
        }
        ksort($b_list);
        //规格列表
        $model_spec = model('spec');
        $spec_list = $model_spec->specList(array('order' => 'sp_sort asc'), '', 'sp_id,sp_name,class_id,class_name');
        $s_list = array();
        if (is_array($spec_list) && !empty($spec_list)) {
            foreach ($spec_list as $k => $val) {
                $s_list[$val['class_id']]['spec'][$k] = $val;
                $s_list[$val['class_id']]['name'] = $val['class_name'] == '' ? lang('nc_default') : $val['class_name'];
            }
        }
        ksort($s_list);
        // 一级分类列表
        $gc_list = Model('goods_class')->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        core\tpl::output('spec_list', $s_list);
        core\tpl::output('brand_list', $b_list);
        core\tpl::showpage('type.add');
    }
    /**
     * 编辑类型
     */
    public function type_editOp()
    {
        $lang = core\language::getLangContent();
        if (empty($_GET['t_id'])) {
            error($lang['param_error']);
        }
        //属性模型
        $model_type = model('type');
        //编辑保存
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['t_mane'], 'require' => 'true', 'message' => $lang['type_add_name_no_null']), array('input' => $_POST['t_sort'], 'require' => 'true', 'validator' => 'Number', 'message' => $lang['type_add_sort_no_null']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            }
            //更新属性关联表信息
            $type_id = intval($_POST['t_id']);
            //品牌
            if (isset($_POST['brand']['form_submit']) && $_POST['brand']['form_submit'] == 'ok') {
                $model_type->delType('type_brand', array('type_id' => $type_id));
                if (!empty($_POST['brand_id'])) {
                    $brand_array = $_POST['brand_id'];
                    $return = $model_type->typeRelatedAdd('type_brand', $brand_array, $type_id, 'type_id,brand_id');
                    if (!$return) {
                        error($lang['type_index_related_fail']);
                    }
                }
            }
            //规格
            if (isset($_POST['spec']['form_submit']) && $_POST['spec']['form_submit'] == 'ok') {
                $model_type->delType('type_spec', array('type_id' => $type_id));
                if (!empty($_POST['spec_id'])) {
                    $spec_array = $_POST['spec_id'];
                    $return = $model_type->typeRelatedAdd('type_spec', $spec_array, $type_id, 'type_id,sp_id');
                    if (!$return) {
                        error($lang['type_index_related_fail']);
                    }
                }
            }
            //属性
            // 转码  防止GBK下用中文逗号截取不正确
            $comma = '，';
            if (strtoupper(CHARSET) == 'GBK') {
                $comma = core\language::getGBK($comma);
            }
            if (!empty($_POST['at_value']) && is_array($_POST['at_value'])) {
                $attribute_array = $_POST['at_value'];
                foreach ($attribute_array as $v) {
                    // 要删除的属性id
                    $del_array = array();
                    if (!empty($_POST['a_del'])) {
                        $del_array = $_POST['a_del'];
                    }
                    $v['value'] = str_replace($comma, ',', isset($v['value']) ? $v['value'] : '');
                    //把属性值中的中文逗号替换成英文逗号
                    if (isset($v['form_submit']) && $v['form_submit'] == 'ok' && !in_array($v['a_id'], $del_array)) {
                        //原属性已修改
                        /**
                         * 属性
                         */
                        $attr_array = array();
                        $attr_array['attr_name'] = $v['name'];
                        $attr_array['type_id'] = $type_id;
                        $attr_array['attr_sort'] = $v['sort'];
                        $attr_array['attr_show'] = $v['show'];
                        $return = $model_type->typeUpdate($attr_array, array('type_id' => $type_id, 'attr_id' => intval($v['a_id'])), 'attribute');
                        if (!$return) {
                            error($lang['type_index_related_fail']);
                        }
                    } else {
                        if (!isset($v['form_submit'])) {
                            //新增属性
                            // 属性
                            $attr_array = array();
                            $attr_array['attr_name'] = $v['name'];
                            $attr_array['attr_value'] = $v['value'];
                            $attr_array['type_id'] = $type_id;
                            $attr_array['attr_sort'] = $v['sort'];
                            $attr_array['attr_show'] = $v['show'];
                            $attr_id = $model_type->typeAdd('attribute', $attr_array);
                            if (!$attr_id) {
                                error($lang['type_index_related_fail']);
                            }
                            //添加属性值
                            $attr_value = explode(',', $v['value']);
                            if (!empty($attr_value)) {
                                $attr_array = array();
                                foreach ($attr_value as $val) {
                                    $tpl_array = array();
                                    $tpl_array['attr_value_name'] = $val;
                                    $tpl_array['attr_id'] = $attr_id;
                                    $tpl_array['type_id'] = $type_id;
                                    $tpl_array['attr_value_sort'] = 0;
                                    $attr_array[] = $tpl_array;
                                }
                                $return = model('attribute')->addAttributeValueAll($attr_array);
                                if (!$return) {
                                    error($lang['type_index_related_fail']);
                                }
                            }
                        }
                    }
                }
                // 删除属性
                if (!empty($_POST['a_del'])) {
                    $del_id = '"' . implode('","', $_POST['a_del']) . '"';
                    $model_type->delType('attribute_value', array('in_attr_id' => $del_id));
                    //删除属性值
                    $model_type->delType('attribute', array('in_attr_id' => $del_id));
                    //删除属性
                }
            }
            //更新属性信息
            $type_array = array();
            $type_array['type_name'] = trim($_POST['t_mane']);
            $type_array['type_sort'] = trim($_POST['t_sort']);
            $type_array['class_id'] = $_POST['class_id'];
            $type_array['class_name'] = $_POST['class_name'];
            $return = $model_type->typeUpdate($type_array, array('type_id' => $type_id), 'type');
            if ($return) {
                $url = 'index.php?act=type&op=type';
                $this->log(lang('nc_edit,type_index_type_name') . '[' . $_POST['t_mane'] . ']', 1);
                success($lang['nc_common_save_succ'], $url);
            } else {
                $this->log(lang('nc_edit,type_index_type_name') . '[' . $_POST['t_mane'] . ']', 0);
                error($lang['nc_common_save_fail']);
            }
        }
        //属性列表
        $type_info = $model_type->typeList(array('type_id' => intval($_GET['t_id'])));
        if (!$type_info) {
            error($lang['param_error']);
        }
        core\tpl::output('type_info', $type_info['0']);
        //品牌
        $model_brand = model('brand');
        $brand_list = $model_brand->getBrandPassedList(array());
        $b_list = array();
        if (is_array($brand_list) && !empty($brand_list)) {
            foreach ($brand_list as $k => $val) {
                $b_list[$val['class_id']]['brand'][$k] = $val;
                $b_list[$val['class_id']]['name'] = $val['brand_class'] == '' ? lang('nc_default') : $val['brand_class'];
            }
        }
        ksort($b_list);
        unset($brand_list);
        //类型与品牌关联列表
        $brand_related = $model_type->typeRelatedList('type_brand', array('type_id' => intval($_GET['t_id'])), 'brand_id');
        $b_related = array();
        if (is_array($brand_related) && !empty($brand_related)) {
            foreach ($brand_related as $val) {
                $b_related[] = $val['brand_id'];
            }
        }
        unset($brand_related);
        core\tpl::output('brang_related', $b_related);
        core\tpl::output('brand_list', $b_list);
        //规格表
        $model_spec = model('spec');
        $spec_list = $model_spec->specList(array('order' => 'sp_sort asc'), '', 'sp_id,sp_name,class_id,class_name');
        $s_list = array();
        if (is_array($spec_list) && !empty($spec_list)) {
            foreach ($spec_list as $k => $val) {
                $s_list[$val['class_id']]['spec'][$k] = $val;
                $s_list[$val['class_id']]['name'] = $val['class_name'] == '' ? lang('nc_default') : $val['class_name'];
            }
        }
        ksort($s_list);
        //规格关联列表
        $spec_related = $model_type->typeRelatedList('type_spec', array('type_id' => intval($_GET['t_id'])), 'sp_id');
        $sp_related = array();
        if (!empty($spec_related) && is_array($spec_related)) {
            foreach ($spec_related as $val) {
                $sp_related[] = $val['sp_id'];
            }
        }
        unset($spec_related);
        core\tpl::output('spec_related', $sp_related);
        core\tpl::output('spec_list', $s_list);
        // 一级分类列表
        $gc_list = model('goods_class')->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        //属性
        $attr_list = $model_type->typeRelatedList('attribute', array('type_id' => intval($_GET['t_id']), 'order' => 'attr_sort asc'));
        core\tpl::output('attr_list', $attr_list);
        core\tpl::showpage('type.edit');
    }
    /**
     * 编辑属性
     */
    public function attr_editOp()
    {
        $lang = core\language::getLangContent();
        $model = model();
        if (!empty($_POST['form_submit'])) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["attr_name"], "require" => "true", "message" => $lang['type_edit_type_attr_name_no_null']), array("input" => $_POST["attr_sort"], "require" => "true", 'validator' => 'Number', "message" => $lang['type_edit_type_attr_sort_no_null']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                //更新属性值表
                $attr_value = $_POST['attr_value'];
                $attr_array = array();
                // 要删除的规格值id
                $del_array = array();
                if (!empty($_POST['attr_del'])) {
                    $del_array = $_POST['attr_del'];
                }
                $model_attribute = model('attribute');
                if (!empty($attr_value) && is_array($attr_value)) {
                    foreach ($attr_value as $key => $val) {
                        if (isset($val['form_submit']) && $val['form_submit'] == 'ok' && !in_array(intval($key), $del_array)) {
                            // 属性已修改
                            $where = array();
                            $where['attr_value_id'] = intval($key);
                            $update = array();
                            $update['attr_value_name'] = $val['name'];
                            $update['attr_value_sort'] = intval($val['sort']);
                            $model_attribute->editAttributeValue($update, $where);
                            $attr_array[] = $val['name'];
                        } else {
                            if (isset($val['form_submit']) && $val['form_submit'] == '' && !in_array(intval($key), $del_array)) {
                                // 属性未修改
                                $attr_array[] = $val['name'];
                            } else {
                                if (!isset($val['form_submit'])) {
                                    $insert = array();
                                    $insert['attr_value_name'] = $val['name'];
                                    $insert['attr_id'] = intval($_POST['attr_id']);
                                    $insert['type_id'] = intval($_POST['type_id']);
                                    $insert['attr_value_sort'] = intval($val['sort']);
                                    $model_attribute->addAttributeValue($insert);
                                    $attr_array[] = $val['name'];
                                }
                            }
                        }
                    }
                    // 删除属性
                    $model->table('attribute_value')->delete(implode(',', $del_array));
                }
                /**
                 * 更新属性
                 */
                $data = array();
                $data['attr_id'] = intval($_POST['attr_id']);
                $data['attr_name'] = $_POST['attr_name'];
                $data['attr_value'] = implode(',', $attr_array);
                $data['attr_show'] = intval($_POST['attr_show']);
                $data['attr_sort'] = intval($_POST['attr_sort']);
                $return = $model->table('attribute')->update($data);
                if ($return) {
                    $this->log(lang('type_edit_type_attr_edit') . '[' . $_POST['attr_name'] . ']', 1);
                    success($lang['type_edit_type_attr_edit_succ'], 'index.php?act=type&op=type');
                } else {
                    $this->log(lang('type_edit_type_attr_edit') . '[' . $_POST['attr_name'] . ']', 0);
                    error($lang['type_edit_type_attr_edit_fail']);
                }
            }
        }
        $attr_id = intval($_GET['attr_id']);
        if ($attr_id == 0) {
            error($lang['param_error']);
        }
        $attr_info = $model->table('attribute')->where('attr_id=' . $attr_id)->find();
        core\tpl::output('attr_info', $attr_info);
        $attr_value_list = $model->table('attribute_value')->where('attr_id=' . $attr_id)->order('attr_value_sort asc, attr_value_id asc')->select();
        core\tpl::output('attr_value_list', $attr_value_list);
        core\tpl::showpage('type_attr.edit');
    }
    /**
     * 删除类型
     */
    public function type_delOp()
    {
        $lang = core\language::getLangContent();
        if (empty($_GET['del_id'])) {
            error($lang['param_error']);
        }
        //属性模型
        $model_type = model('type');
        if (is_array($_GET['del_id'])) {
            $id = "'" . implode("','", $_GET['del_id']) . "'";
        } else {
            $id = intval($_GET['del_id']);
        }
        //属性列表
        $type_list = $model_type->typeList(array('in_type_id' => $id));
        if (is_array($type_list) && !empty($type_list)) {
            //删除属性值表
            $attr_list = $model_type->typeRelatedList('attribute', array('in_type_id' => $id), 'attr_id');
            if (is_array($attr_list) && !empty($attr_list)) {
                $attrs_id = '';
                foreach ($attr_list as $val) {
                    $attrs_id .= '"' . $val['attr_id'] . '",';
                }
                $attrs_id = trim($attrs_id, ',');
                $return1 = $model_type->delType('attribute_value', array('in_attr_id' => $attrs_id));
                //删除属性值
                $return2 = $model_type->delType('attribute', array('in_attr_id' => $attrs_id));
                //删除属性
                if (!$return1 || !$return2) {
                    error($lang['type_index_del_related_attr_fail']);
                }
            }
            //删除对应品牌
            $return = $model_type->delType('type_brand', array('in_type_id' => $id));
            if (!$return) {
                error($lang['type_index_del_related_brand_fail']);
            }
            //删除对应规格
            $return = $model_type->delType('type_spec', array('in_type_id' => $id));
            if (!$return) {
                error($lang['type_index_del_related_type_fail']);
            }
            //删除类型
            $return = $model_type->delType('type', array('in_type_id' => $id));
            if (!$return) {
                error($lang['type_index_del_fail']);
            }
            $this->log(lang('nc_delete,type_index_type_name') . '[ID:' . $id . ']', 1);
            success($lang['type_index_del_succ']);
        } else {
            $this->log(lang('nc_delete,type_index_type_name') . '[ID:' . $id . ']', 0);
            error($lang['param_error']);
        }
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        $model_type = model('type');
        switch ($_GET['branch']) {
            case 'sort':
                //			case 'name':
                $return = $model_type->typeUpdate(array($_GET['column'] => trim($_GET['value'])), array('type_id' => intval($_GET['id'])), 'type');
                if ($return) {
                    $this->log(lang('type_index_type_name,nc_sort') . '[ID:' . intval($_GET['id']) . ']', 1);
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
     * 类型导出
     */
    public function export_step1Op()
    {
        $model_type = model('type');
        $page = new lib\page();
        $page->setEachNum(self::EXPORT_SIZE);
        $type_list = $model_type->typeList(array('order' => 'type_sort asc'), $page);
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
                core\tpl::output('murl', 'index.php?act=type&op=type');
                core\tpl::showpage('export.excel');
            } else {
                //如果数量小，直接下载
                $this->createExcel($type_list);
            }
        } else {
            //下载
            $this->createExcel($type_list);
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
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_type_name'));
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['type_name']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_type_name'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_type_name'), CHARSET) . $_GET['curpage'] . '-' . date('Y-m-d-H', time()));
    }
}