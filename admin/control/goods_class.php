<?php
/**
 * 商品分类管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class goods_class extends SystemControl
{
    //private $links = array(array('url' => 'act=goods_class&op=goods_class', 'lang' => 'nc_manage'), array('url' => 'act=goods_class&op=goods_class_add', 'lang' => 'nc_new'), array('url' => 'act=goods_class&op=goods_class_export', 'lang' => 'goods_class_index_export'), array('url' => 'act=goods_class&op=goods_class_import', 'lang' => 'goods_class_index_import'), array('url' => 'act=goods_class&op=tag', 'lang' => 'goods_class_index_tag'));
    private $links = array(array('url' => 'act=goods_class&op=goods_class', 'lang' => 'nc_manage'), array('url' => 'act=goods_class&op=goods_class_add', 'lang' => 'nc_new'), array('url' => 'act=goods_class&op=goods_class_export', 'lang' => 'goods_class_index_export'), array('url' => 'act=goods_class&op=goods_class_import', 'lang' => 'goods_class_index_import'));
	public function __construct()
    {
        parent::__construct();
        core\language::read('goods_class');
    }
    /**
     * 分类管理
     */
    public function goods_classOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('goods_class');
        if (chksubmit()) {
            //删除
            if ($_POST['submit_type'] == 'del') {
                $gcids = implode(',', $_POST['check_gc_id']);
                if (!empty($_POST['check_gc_id'])) {
                    if (!is_array($_POST['check_gc_id'])) {
                        $this->log(lang('nc_delete,goods_class_index_class') . '[ID:' . $gcids . ']', 0);
                        error($lang['nc_common_del_fail']);
                    }
                    $del_array = $model_class->delGoodsClassByGcIdString($gcids);
                    $this->log(lang('nc_delete,goods_class_index_class') . '[ID:' . $gcids . ']', 1);
                    success($lang['nc_common_del_succ']);
                } else {
                    $this->log(lang('nc_delete,goods_class_index_class') . '[ID:' . $gcids . ']', 0);
                    error($lang['nc_common_del_fail']);
                }
            }
        }
        //父ID
        $parent_id = !empty($_GET['gc_parent_id']) ? intval($_GET['gc_parent_id']) : 0;
        //列表
		$class_list = array();
        $tmp_list = $model_class->getTreeClassList(3);
        if (is_array($tmp_list)) {
            foreach ($tmp_list as $k => $v) {
                if ($v['gc_parent_id'] == $parent_id) {
                    //判断是否有子类
                    if (isset($tmp_list[$k + 1]['deep']) && $tmp_list[$k + 1]['deep'] > $v['deep']) {
                        $v['have_child'] = 1;
                    }
                    $class_list[] = $v;
                }
            }
        }
        if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
            //转码
            if (strtoupper(CHARSET) == 'GBK') {
                $class_list = core\language::getUTF8($class_list);
            }
            $output = json_encode($class_list);
            print_r($output);
            exit;
        } else {
            core\tpl::output('class_list', $class_list);
            core\tpl::output('top_link', $this->sublink($this->links, 'goods_class'));
            core\tpl::showpage('goods_class.index');
        }
    }
    /**
     * 商品分类添加
     */
    public function goods_class_addOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('goods_class');
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["gc_name"], "require" => "true", "message" => $lang['goods_class_add_name_null']), array("input" => $_POST["gc_sort"], "require" => "true", 'validator' => 'Number', "message" => $lang['goods_class_add_sort_int']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $insert_array = array();
                $insert_array['gc_name'] = $_POST['gc_name'];
                $insert_array['type_id'] = intval($_POST['t_id']);
                $insert_array['type_name'] = trim($_POST['t_name']);
                $insert_array['gc_parent_id'] = intval($_POST['gc_parent_id']);
                $insert_array['commis_rate'] = intval($_POST['commis_rate']);
                $insert_array['gc_sort'] = intval($_POST['gc_sort']);
                $insert_array['gc_virtual'] = isset($_POST['gc_virtual']) ? intval($_POST['gc_virtual']) : 0;
                $result = $model_class->addGoodsClass($insert_array);
                if ($result) {
                    if ($insert_array['gc_parent_id'] == 0) {
                        if (!empty($_FILES['pic']['name'])) {
                            //上传图片
                            $upload = new lib\uploadfile();
                            $upload->set('default_dir', ATTACH_COMMON);
                            $upload->set('file_name', 'category-pic-' . $result . '.jpg');
                            $upload->upfile('pic');
                        }
                    }
                    $url = 'index.php?act=goods_class&op=goods_class';
                    $this->log(lang('nc_add,goods_class_index_class') . '[' . $_POST['gc_name'] . ']', 1);
                    success($lang['nc_common_save_succ'], $url);
                } else {
                    $this->log(lang('nc_add,goods_class_index_class') . '[' . $_POST['gc_name'] . ']', 0);
                    error($lang['nc_common_save_fail']);
                }
            }
        }
        //父类列表，只取到第二级
        $parent_list = $model_class->getTreeClassList(2);
        $gc_list = array();
        if (is_array($parent_list)) {
            foreach ($parent_list as $k => $v) {
                $parent_list[$k]['gc_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['gc_name'];
                if ($v['deep'] == 1) {
                    $gc_list[$k] = $v;
                }
            }
        }
        core\tpl::output('gc_list', $gc_list);
        //类型列表
        $model_type = model('type');
        $type_list = $model_type->typeList(array('order' => 'type_sort asc'), '', 'type_id,type_name,class_id,class_name');
        $t_list = array();
        if (!empty($type_list) && is_array($type_list)) {
            foreach ($type_list as $k => $val) {
                $t_list[$val['class_id']]['type'][$k] = $val;
                $t_list[$val['class_id']]['name'] = $val['class_name'] == '' ? lang('nc_default') : $val['class_name'];
            }
        }
        ksort($t_list);
        core\tpl::output('type_list', $t_list);
        core\tpl::output('gc_parent_id', isset($_GET['gc_parent_id']) ? $_GET['gc_parent_id'] : 0);
        core\tpl::output('parent_list', $parent_list);
        core\tpl::output('top_link', $this->sublink($this->links, 'goods_class_add'));
        core\tpl::showpage('goods_class.add');
    }
    /**
     * 编辑
     */
    public function goods_class_editOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('goods_class');
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['gc_name'], 'require' => 'true', 'message' => $lang['goods_class_add_name_null']), /*array('input' => $_POST['commis_rate'], 'require' => 'true', 'validator' => 'range', 'max' => 100, 'min' => 0, 'message' => $lang['goods_class_add_commis_rate_error']),*/ array('input' => $_POST['gc_sort'], 'require' => 'true', 'validator' => 'Number', 'message' => $lang['goods_class_add_sort_int']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            }
            // 更新分类信息
            $where = array('gc_id' => intval($_POST['gc_id']));
            $update_array = array();
            $update_array['gc_name'] = $_POST['gc_name'];
            $update_array['type_id'] = isset($_POST['t_id']) ? intval($_POST['t_id']) : 0;
            $update_array['type_name'] = isset($_POST['t_name']) ? trim($_POST['t_name']) : 0;
            $update_array['commis_rate'] = isset($_POST['commis_rate']) ? intval($_POST['commis_rate']) : 0;
            $update_array['gc_sort'] = intval($_POST['gc_sort']);
            $update_array['gc_virtual'] = isset($_POST['gc_virtual']) ? intval($_POST['gc_virtual']) : '';
            $update_array['gc_parent_id'] = intval($_POST['gc_parent_id']);
            $result = $model_class->editGoodsClass($update_array, $where);
            if (!$result) {
                $this->log(lang('nc_edit,goods_class_index_class') . '[' . $_POST['gc_name'] . ']', 0);
                error($lang['goods_class_batch_edit_fail']);
            }
            if (!empty($_FILES['pic']['name'])) {
                //上传图片
                $upload = new lib\uploadfile();
                $upload->set('default_dir', ATTACH_COMMON);
                $upload->set('file_name', 'category-pic-' . intval($_POST['gc_id']) . '.jpg');
                $upload->upfile('pic');
            }
            // 检测是否需要关联自己操作，统一查询子分类
            if ($_POST['t_commis_rate'] == '1' || $_POST['t_associated'] == '1' || $_POST['t_gc_virtual'] == '1') {
                $gc_id_list = $model_class->getChildClass($_POST['gc_id']);
                $gc_ids = array();
                if (is_array($gc_id_list) && !empty($gc_id_list)) {
                    foreach ($gc_id_list as $val) {
                        $gc_ids[] = $val['gc_id'];
                    }
                }
            }
            // 更新该分类下子分类的所有分佣比例
            if ($_POST['t_commis_rate'] == '1' && !empty($gc_ids)) {
                $model_class->editGoodsClass(array('commis_rate' => $update_array['commis_rate']), array('gc_id' => array('in', $gc_ids)));
            }
            // 更新该分类下子分类的所有类型
            if ((isset($_POST['t_associated']) && $_POST['t_associated'] == '1') && !empty($gc_ids)) {
                $where = array();
                $where['gc_id'] = array('in', $gc_ids);
                $update = array();
                $update['type_id'] = intval($_POST['t_id']);
                $update['type_name'] = trim($_POST['t_name']);
                $model_class->editGoodsClass($update, $where);
            }
            // 虚拟商品
            if ($_POST['t_gc_virtual'] == '1' && !empty($gc_ids)) {
                $model_class->editGoodsClass(array('gc_virtual' => $update_array['gc_virtual']), array('gc_id' => array('in', $gc_ids)));
            }
            $url = 'index.php?act=goods_class&op=goods_class_edit&gc_id=' . intval($_POST['gc_id']);
            $this->log(lang('nc_edit,goods_class_index_class') . '[' . $_POST['gc_name'] . ']', 1);
            success($lang['goods_class_batch_edit_ok'], $url);
        }
        $class_array = $model_class->getGoodsClassInfoById(intval($_GET['gc_id']));
        if (empty($class_array)) {
            error($lang['goods_class_batch_edit_paramerror']);
        }
        //类型列表
        $model_type = model('type');
        $type_list = $model_type->typeList(array('order' => 'type_sort asc'), '', 'type_id,type_name,class_id,class_name');
        $t_list = array();
        if (is_array($type_list) && !empty($type_list)) {
            foreach ($type_list as $k => $val) {
                $t_list[$val['class_id']]['type'][$k] = $val;
                $t_list[$val['class_id']]['name'] = $val['class_name'] == '' ? lang('nc_default') : $val['class_name'];
            }
        }
        ksort($t_list);
        //父类列表，只取到第二级
        $parent_list = $model_class->getTreeClassList(2);
        if (is_array($parent_list)) {
            foreach ($parent_list as $k => $v) {
                $parent_list[$k]['gc_name'] = str_repeat('&nbsp;', $v['deep'] * 2) . $v['gc_name'];
            }
        }
        core\tpl::output('parent_list', $parent_list);
        // 一级分类列表
        $gc_list = model('goods_class')->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        $pic_name = BASE_UPLOAD_PATH . '/' . ATTACH_COMMON . '/category-pic-' . $class_array['gc_id'] . '.jpg';
        if (file_exists($pic_name)) {
            $class_array['pic'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/category-pic-' . $class_array['gc_id'] . '.jpg';
        }
        core\tpl::output('type_list', $t_list);
        core\tpl::output('class_array', $class_array);
        $this->links[] = array('url' => 'act=goods_class&op=goods_class_edit', 'lang' => 'nc_edit');
        core\tpl::output('top_link', $this->sublink($this->links, 'goods_class_edit'));
        core\tpl::showpage('goods_class.edit');
    }
    /**
     * 分类导入
     */
    public function goods_class_importOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('goods_class');
        //导入
        if (chksubmit()) {
            //得到导入文件后缀名
            $csv_array = explode('.', $_FILES['csv']['name']);
            $file_type = end($csv_array);
            if (!empty($_FILES['csv']) && !empty($_FILES['csv']['name']) && $file_type == 'csv') {
                $fp = @fopen($_FILES['csv']['tmp_name'], 'rb');
                // 父ID
                $parent_id_1 = 0;
                while (!feof($fp)) {
                    $data = fgets($fp, 4096);
                    switch (strtoupper($_POST['charset'])) {
                        case 'UTF-8':
                            if (strtoupper(CHARSET) !== 'UTF-8') {
                                $data = iconv('UTF-8', strtoupper(CHARSET), $data);
                            }
                            break;
                        case 'GBK':
                            if (strtoupper(CHARSET) !== 'GBK') {
                                $data = iconv('GBK', strtoupper(CHARSET), $data);
                            }
                            break;
                    }
                    if (!empty($data)) {
                        $data = str_replace('"', '', $data);
                        //逗号去除
                        $tmp_array = array();
                        $tmp_array = explode(',', $data);
                        if ($tmp_array[0] == 'sort_order') {
                            continue;
                        }
                        //第一位是序号，后面的是内容，最后一位名称
                        $tmp_deep = 'parent_id_' . (count($tmp_array) - 1);
                        $insert_array = array();
                        $insert_array['gc_sort'] = $tmp_array[0];
                        $insert_array['gc_parent_id'] = isset(${$tmp_deep}) ? ${$tmp_deep} : 0;
                        $insert_array['gc_name'] = $tmp_array[count($tmp_array) - 1];
                        $gc_id = $model_class->addGoodsClass($insert_array);
                        //赋值这个深度父ID
                        $tmp = 'parent_id_' . count($tmp_array);
                        ${$tmp} = $gc_id;
                    }
                }
                $this->log(lang('goods_class_index_import,goods_class_index_class'), 1);
                success($lang['nc_common_op_succ'], 'index.php?act=goods_class&op=goods_class');
            } else {
                $this->log(lang('goods_class_index_import,goods_class_index_class'), 0);
                error($lang['goods_class_import_csv_null']);
            }
        }
        core\tpl::output('top_link', $this->sublink($this->links, 'goods_class_import'));
        core\tpl::showpage('goods_class.import');
    }
    /**
     * 分类导出
     */
    public function goods_class_exportOp()
    {
        if (chksubmit()) {
            $model_class = model('goods_class');
            $class_list = $model_class->getTreeClassList();
            @header("Content-type: application/unknown");
            @header("Content-Disposition: attachment; filename=goods_class.csv");
            if (is_array($class_list)) {
                foreach ($class_list as $k => $v) {
                    $tmp = array();
                    //序号
                    $tmp['gc_sort'] = $v['gc_sort'];
                    //深度
                    for ($i = 1; $i <= $v['deep'] - 1; $i++) {
                        $tmp[] = '';
                    }
                    //分类名称
                    $tmp['gc_name'] = $v['gc_name'];
                    //转码 utf-gbk
                    if (strtoupper(CHARSET) == 'UTF-8') {
                        switch ($_POST['if_convert']) {
                            case '1':
                                $tmp_line = iconv('UTF-8', 'GB2312//IGNORE', join(',', $tmp));
                                break;
                            case '0':
                                $tmp_line = join(',', $tmp);
                                break;
                        }
                    } else {
                        $tmp_line = join(',', $tmp);
                    }
                    $tmp_line = str_replace("\r\n", '', $tmp_line);
                    echo $tmp_line . "\r\n";
                }
            }
            $this->log(lang('goods_class_index_export,goods_class_index_class'), 1);
            exit;
        }
        core\tpl::output('top_link', $this->sublink($this->links, 'goods_class_export'));
        core\tpl::showpage('goods_class.export');
    }
    /**
     * 删除分类
     */
    public function goods_class_delOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('goods_class');
        if (intval($_GET['gc_id']) > 0) {
            //删除分类
            $model_class->delGoodsClassByGcIdString(intval($_GET['gc_id']));
            $this->log(lang('nc_delete,goods_class_index_class') . '[ID:' . intval($_GET['gc_id']) . ']', 1);
            success($lang['nc_common_del_succ'], 'index.php?act=goods_class&op=goods_class');
        } else {
            $this->log(lang('nc_delete,goods_class_index_class') . '[ID:' . intval($_GET['gc_id']) . ']', 0);
            error($lang['nc_common_del_fail'], 'index.php?act=goods_class&op=goods_class');
        }
    }
    /**
     * tag列表
     */
    public function tagOp()
    {
        $lang = core\language::getLangContent();
        /**
         * 处理商品分类
         */
        $choose_gcid = ($t = (isset($_REQUEST['choose_gcid']) ? intval($_REQUEST['choose_gcid']) : 0)) > 0 ? $t : 0;
        $gccache_arr = model('goods_class')->getGoodsclassCache($choose_gcid, 3);
        core\tpl::output('gc_json', json_encode($gccache_arr['showclass']));
        core\tpl::output('gc_choose_json', json_encode($gccache_arr['choose_gcid']));
        $model_class_tag = model('goods_class_tag');
        if (chksubmit()) {
            //删除
            if ($_POST['submit_type'] == 'del') {
                if (is_array($_POST['tag_id']) && !empty($_POST['tag_id'])) {
                    //删除TAG
                    $model_class_tag->delTagByIds(implode(',', $_POST['tag_id']));
                    $this->log(lang('nc_delete') . 'tag[ID:' . implode(',', $_POST['tag_id']) . ']', 1);
                    success($lang['nc_common_del_succ']);
                } else {
                    $this->log(lang('nc_delete') . 'tag', 0);
                    error($lang['nc_common_del_fail']);
                }
            }
        }
        $page = new lib\page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        $where = array();
        if ($choose_gcid > 0) {
            $where['gc_id_' . $gccache_arr['showclass'][$choose_gcid]['depth']] = $choose_gcid;
        }
        $tag_list = $model_class_tag->getTagList($where, $page);
        core\tpl::output('tag_list', $tag_list);
        core\tpl::output('page', $page->show());
        core\tpl::output('top_link', $this->sublink($this->links, 'tag'));
        core\tpl::showpage('goods_class_tag.index');
    }
    /**
     * 重置TAG
     */
    public function tag_resetOp()
    {
        $lang = core\language::getLangContent();
        //实例化模型
        $model_class = model('goods_class');
        $model_class_tag = model('goods_class_tag');
        //清空TAG
        $return = $model_class_tag->clearTag();
        if (!$return) {
            error($lang['goods_class_reset_tag_fail'], 'index.php?act=goods_class&op=tag');
        }
        //商品分类
        $goods_class = $model_class->getTreeClassList(3);
        //格式化分类。组成三维数组
        if (is_array($goods_class) and !empty($goods_class)) {
            $goods_class_array = array();
            foreach ($goods_class as $val) {
                //一级分类
                if ($val['gc_parent_id'] == 0) {
                    $goods_class_array[$val['gc_id']]['gc_name'] = $val['gc_name'];
                    $goods_class_array[$val['gc_id']]['gc_id'] = $val['gc_id'];
                    $goods_class_array[$val['gc_id']]['type_id'] = $val['type_id'];
                } else {
                    //二级分类
                    if (isset($goods_class_array[$val['gc_parent_id']])) {
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_name'] = $val['gc_name'];
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_id'] = $val['gc_id'];
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_parent_id'] = $val['gc_parent_id'];
                        $goods_class_array[$val['gc_parent_id']]['sub_class'][$val['gc_id']]['type_id'] = $val['type_id'];
                    } else {
                        foreach ($goods_class_array as $v) {
                            //三级分类
                            if (isset($v['sub_class'][$val['gc_parent_id']])) {
                                $goods_class_array[$v['sub_class'][$val['gc_parent_id']]['gc_parent_id']]['sub_class'][$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_name'] = $val['gc_name'];
                                $goods_class_array[$v['sub_class'][$val['gc_parent_id']]['gc_parent_id']]['sub_class'][$val['gc_parent_id']]['sub_class'][$val['gc_id']]['gc_id'] = $val['gc_id'];
                                $goods_class_array[$v['sub_class'][$val['gc_parent_id']]['gc_parent_id']]['sub_class'][$val['gc_parent_id']]['sub_class'][$val['gc_id']]['type_id'] = $val['type_id'];
                            }
                        }
                    }
                }
            }
            $return = $model_class_tag->tagAdd($goods_class_array);
            if ($return) {
                $this->log(lang('nc_reset') . 'tag', 1);
                success($lang['nc_common_op_succ'], 'index.php?act=goods_class&op=tag');
            } else {
                $this->log(lang('nc_reset') . 'tag', 0);
                error($lang['nc_common_op_fail'], 'index.php?act=goods_class&op=tag');
            }
        } else {
            $this->log(lang('nc_reset') . 'tag', 0);
            error($lang['goods_class_reset_tag_fail_no_class'], 'index.php?act=goods_class&op=tag');
        }
    }
    /**
     * 更新TAG名称
     */
    public function tag_updateOp()
    {
        $lang = core\language::getLangContent();
        $model_class = model('goods_class');
        $model_class_tag = model('goods_class_tag');
        //需要更新的TAG列表
        $tag_list = $model_class_tag->getTagList(array(), '', 'gc_tag_id,gc_id_1,gc_id_2,gc_id_3');
        if (is_array($tag_list) && !empty($tag_list)) {
            foreach ($tag_list as $val) {
                //查询分类信息
                $in_gc_id = array();
                if ($val['gc_id_1'] != '0') {
                    $in_gc_id[] = $val['gc_id_1'];
                }
                if ($val['gc_id_2'] != '0') {
                    $in_gc_id[] = $val['gc_id_2'];
                }
                if ($val['gc_id_3'] != '0') {
                    $in_gc_id[] = $val['gc_id_3'];
                }
                $gc_list = $model_class->getGoodsClassListByIds($in_gc_id);
                //更新TAG信息
                $update_tag = array();
                if (isset($gc_list['0']['gc_id']) && $gc_list['0']['gc_id'] != '0') {
                    $update_tag['gc_id_1'] = $gc_list['0']['gc_id'];
					if(!isset($update_tag['gc_tag_name'])){
						$update_tag['gc_tag_name'] = '';
					}
                    $update_tag['gc_tag_name'] .= $gc_list['0']['gc_name'];
                }
                if (isset($gc_list['1']['gc_id']) && $gc_list['1']['gc_id'] != '0') {
                    $update_tag['gc_id_2'] = $gc_list['1']['gc_id'];
					if(!isset($update_tag['gc_tag_name'])){
						$update_tag['gc_tag_name'] = '';
					}
                    $update_tag['gc_tag_name'] .= "&nbsp;&gt;&nbsp;" . $gc_list['1']['gc_name'];
                }
                if (isset($gc_list['2']['gc_id']) && $gc_list['2']['gc_id'] != '0') {
                    $update_tag['gc_id_3'] = $gc_list['2']['gc_id'];
					if(!isset($update_tag['gc_tag_name'])){
						$update_tag['gc_tag_name'] = '';
					}
                    $update_tag['gc_tag_name'] .= "&nbsp;&gt;&nbsp;" . $gc_list['2']['gc_name'];
                }
                unset($gc_list);
                $update_tag['gc_tag_id'] = $val['gc_tag_id'];
                $return = $model_class_tag->updateTag($update_tag);
                if (!$return) {
                    $this->log(lang('nc_update') . 'tag', 0);
                    error($lang['nc_common_op_fail'], 'index.php?act=goods_class&op=tag');
                }
            }
            $this->log(lang('nc_update') . 'tag', 1);
            success($lang['nc_common_op_succ'], 'index.php?act=goods_class&op=tag');
        } else {
            $this->log(lang('nc_update') . 'tag', 0);
            error($lang['goods_class_update_tag_fail_no_class'], 'index.php?act=goods_class&op=tag');
        }
    }
    /**
     * 删除TAG
     */
    public function tag_delOp()
    {
        $id = intval($_GET['tag_id']);
        $lang = core\language::getLangContent();
        $model_class_tag = model('goods_class_tag');
        if ($id > 0) {
            /**
             * 删除TAG
             */
            $model_class_tag->delTagByIds($id);
            $this->log(lang('nc_delete') . 'tag[ID:' . $id . ']', 1);
            success($lang['nc_common_op_succ']);
        } else {
            $this->log(lang('nc_delete') . 'tag[ID:' . $id . ']', 0);
            error($lang['nc_common_op_fail']);
        }
    }
    /**
     * 分类导航
     */
    public function nav_editOp()
    {
        $gc_id = $_REQUEST['gc_id'];
        $model_goods = model('goods_class');
        $class_info = $model_goods->getGoodsClassInfoById($gc_id);
        $model_class_nav = model('goods_class_nav');
        $nav_info = $model_class_nav->getGoodsClassNavInfoByGcId($gc_id);
        if (chksubmit()) {
            $update = array();
            $update['gc_id'] = $gc_id;
            $update['cn_alias'] = $_POST['cn_alias'];
            if (!empty($_POST['class_id']) && is_array($_POST['class_id'])) {
                $update['cn_classids'] = implode(',', $_POST['class_id']);
            } else {
                $update['cn_classids'] = isset($_POST['class_id']) ? $_POST['class_id'] : '';
            }
            if (!empty($_POST['brand_id']) && is_array($_POST['brand_id'])) {
                $update['cn_brandids'] = implode(',', $_POST['brand_id']);
            } else {
                $update['cn_brandids'] = isset($_POST['brand_id']) ? $_POST['brand_id'] : '';
            }
            $update['cn_adv1_link'] = isset($_POST['cn_adv1_link']) ? $_POST['cn_adv1_link'] : '';
            $update['cn_adv2_link'] = isset($_POST['cn_adv2_link']) ? $_POST['cn_adv2_link'] : '';
            if (!empty($_FILES['pic']['name'])) {
                //上传图片
                $upload = new lib\uploadfile();
				$cn_pic = BASE_UPLOAD_PATH . DS . ATTACH_GOODS_CLASS . DS . (isset($nav_info['cn_pic']) ? $nav_info['cn_pic'] : '');
				if(file_exists($cn_pic) && is_file($cn_pic)){
					unlink($cn_pic);
				}
                $upload->set('default_dir', ATTACH_GOODS_CLASS);
                $upload->upfile('pic');
                $update['cn_pic'] = $upload->file_name;
            }
            if (!empty($_FILES['adv1']['name'])) {
                //上传广告图片
                $upload = new lib\uploadfile();
				$cn_adv1 = BASE_UPLOAD_PATH . DS . ATTACH_GOODS_CLASS . DS . (isset($nav_info['cn_adv1']) ? $nav_info['cn_adv1'] : '');
				if(file_exists($cn_adv1) && is_file($cn_adv1)){
					unlink($cn_adv1);
				}
                $upload->set('default_dir', ATTACH_GOODS_CLASS);
                $upload->upfile('adv1');
                $update['cn_adv1'] = $upload->file_name;
            }
            if (!empty($_FILES['adv2']['name'])) {
                //上传广告图片
                $upload = new lib\uploadfile();
				$cn_adv2 = BASE_UPLOAD_PATH . DS . ATTACH_GOODS_CLASS . DS . (isset($nav_info['cn_adv2']) ? $nav_info['cn_adv2'] : '');
				if(file_exists($cn_adv2) && is_file($cn_adv2)){
					unlink($cn_adv2);
				}
                $upload->set('default_dir', ATTACH_GOODS_CLASS);
                $upload->upfile('adv2');
                $update['cn_adv2'] = $upload->file_name;
            }
            if (empty($nav_info)) {
                $result = $model_class_nav->addGoodsClassNav($update);
            } else {
                $result = $model_class_nav->editGoodsClassNav($update, $gc_id);
            }
            if ($result) {
                $this->log('编辑分类导航，' . $class_info['gc_name'], 1);
                success('编辑成功');
            } else {
                $this->log('编辑分类导航，' . $class_info['gc_name'], 0);
                error('编辑失败');
            }
        }
        $pic_name = BASE_UPLOAD_PATH . DS . ATTACH_GOODS_CLASS . DS . (isset($nav_info['cn_pic']) ? $nav_info['cn_pic'] : '');
        if (file_exists($pic_name)) {
            $nav_info['cn_pic'] = UPLOAD_SITE_URL . DS . ATTACH_GOODS_CLASS . DS . (isset($nav_info['cn_pic']) ? $nav_info['cn_pic'] : '');
        }
        $pic_name = BASE_UPLOAD_PATH . DS . ATTACH_GOODS_CLASS . DS . (isset($nav_info['cn_adv1']) ? $nav_info['cn_adv1'] : '');
        if (file_exists($pic_name)) {
            $nav_info['cn_adv1'] = UPLOAD_SITE_URL . DS . ATTACH_GOODS_CLASS . DS . (isset($nav_info['cn_adv1']) ? $nav_info['cn_adv1'] : '');
        }
        $pic_name = BASE_UPLOAD_PATH . DS . ATTACH_GOODS_CLASS . DS . (isset($nav_info['cn_adv2']) ? $nav_info['cn_adv2'] : '');
        if (file_exists($pic_name)) {
            $nav_info['cn_adv2'] = UPLOAD_SITE_URL . DS . ATTACH_GOODS_CLASS . DS . (isset($nav_info['cn_adv2']) ? $nav_info['cn_adv2'] : '');
        }
		if(!empty($nav_info['cn_classids'])){
			$nav_info['cn_classids'] = explode(',', $nav_info['cn_classids']);
		}else{
			$nav_info['cn_classids'] = array();
		}
        if(!empty($nav_info['cn_brandids'])){
			$nav_info['cn_brandids'] = explode(',', $nav_info['cn_brandids']);
		}else{
			$nav_info['cn_brandids'] = array();
		}
        core\tpl::output('nav_info', $nav_info);
        core\tpl::output('class_info', $class_info);
        // 一级分类列表
        $gc_list = $model_goods->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        // 全部三级分类
        $third_class = $model_goods->getChildClassByFirstId($gc_id);
        core\tpl::output('third_class', $third_class);
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
        core\tpl::output('brand_list', $b_list);
        core\tpl::showpage('goods_class.nav_edit');
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            /**
             * 更新分类
             */
            case 'goods_class_name':
                $model_class = model('goods_class');
                $class_array = $model_class->getGoodsClassInfoById(intval($_GET['id']));
                $condition['gc_name'] = trim($_GET['value']);
                $condition['gc_parent_id'] = $class_array['gc_parent_id'];
                $condition['gc_id'] = array('neq' => intval($_GET['id']));
                $class_list = $model_class->getGoodsClassList($condition);
                if (empty($class_list)) {
                    $where = array('gc_id' => intval($_GET['id']));
                    $update_array = array();
                    $update_array['gc_name'] = trim($_GET['value']);
                    $model_class->editGoodsClass($update_array, $where);
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
                /**
                 * 分类 排序 显示 设置
                 */
            /**
             * 分类 排序 显示 设置
             */
            case 'goods_class_sort':
            case 'goods_class_show':
            case 'goods_class_index_show':
                $model_class = model('goods_class');
                $where = array('gc_id' => intval($_GET['id']));
                $update_array = array();
                $update_array[$_GET['column']] = $_GET['value'];
                $model_class->editGoodsClass($update_array, $where);
                echo 'true';
                exit;
                break;
                /**
                 * 添加、修改操作中 检测类别名称是否有重复
                 */
            /**
             * 添加、修改操作中 检测类别名称是否有重复
             */
            case 'check_class_name':
                $model_class = model('goods_class');
                $condition['gc_name'] = trim($_GET['gc_name']);
                $condition['gc_parent_id'] = intval($_GET['gc_parent_id']);
                $condition['gc_id'] = array('neq', intval($_GET['gc_id']));
                $class_list = $model_class->getGoodsClassList($condition);
                if (empty($class_list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
                /**
                 * TAG值编辑
                 */
            /**
             * TAG值编辑
             */
            case 'goods_class_tag_value':
                $model_class_tag = model('goods_class_tag');
                $update_array = array();
                $update_array['gc_tag_id'] = intval($_GET['id']);
                /**
                 * 转码  防止GBK下用中文逗号截取不正确
                 */
                $comma = '，';
                if (strtoupper(CHARSET) == 'GBK') {
                    $comma = core\language::getGBK($comma);
                }
                $update_array[$_GET['column']] = trim(str_replace($comma, ',', $_GET['value']));
                $model_class_tag->updateTag($update_array);
                echo 'true';
                exit;
                break;
        }
    }
}