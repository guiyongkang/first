<?php
/**
 * 会员中心——我是卖家
 **/
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class store_goods_class extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('member_store_index');
    }
    /**
     * 卖家商品分类
     *
     * @param
     * @return
     */
    public function indexOp()
    {
        $model_class = model('store_goods_class');
        if (isset($_GET['type']) && $_GET['type'] == 'ok') {
            if (isset($_GET['class_id']) && intval($_GET['class_id']) != 0) {
                $class_info = $model_class->getStoreGoodsClassInfo(array('stc_id' => intval($_GET['class_id'])));
                core\tpl::output('class_info', $class_info);
            }
            if (isset($_GET['top_class_id']) && intval($_GET['top_class_id']) != 0) {
                core\tpl::output('class_info', array('stc_parent_id' => intval($_GET['top_class_id'])));
            }
            $goods_class = $model_class->getStoreGoodsClassList(array('store_id' => core\session::get('store_id'), 'stc_parent_id' => 0));
            core\tpl::output('goods_class', $goods_class);
            core\tpl::showpage('store_goods_class.add', 'null_layout');
        } else {
            $goods_class = $model_class->getTreeClassList(array('store_id' => core\session::get('store_id')), 2);
            $str = '';
            if (is_array($goods_class) and count($goods_class) > 0) {
                foreach ($goods_class as $key => $val) {
                    $row[$val['stc_id']] = $key + 1;
                    $str .= (isset($row[$val['stc_parent_id']]) ? intval($row[$val['stc_parent_id']]) : 0) . ',';
                }
                $str = substr($str, 0, -1);
            } else {
                $str = '0';
            }
            core\tpl::output('map', $str);
            core\tpl::output('class_num', count($goods_class) - 1);
            core\tpl::output('goods_class', $goods_class);
            self::profile_menu('store_goods_class', 'store_goods_class');
            core\tpl::showpage('store_goods_class.list');
        }
    }
    /**
     * 卖家商品分类保存
     *
     * @param
     * @return
     */
    public function goods_class_saveOp()
    {
        $model_class = model('store_goods_class');
        if (isset($_POST['stc_id'])) {
            $stc_id = intval($_POST['stc_id']);
            if ($stc_id <= 0) {
                showDialog(lang('wrong_argument'));
            }
            $class_array = array();
            if (!empty($_POST['stc_name'])) {
                $class_array['stc_name'] = $_POST['stc_name'];
            }
            if (!empty($_POST['stc_parent_id'])) {
                $class_array['stc_parent_id'] = $_POST['stc_parent_id'];
            }
            if (!empty($_POST['stc_state'])) {
                $class_array['stc_state'] = $_POST['stc_state'];
            }
            if (!empty($_POST['stc_sort'])) {
                $class_array['stc_sort'] = $_POST['stc_sort'];
            }
            $where = array();
            $where['store_id'] = core\session::get('store_id');
            $where['stc_id'] = intval($_POST['stc_id']);
            $state = $model_class->editStoreGoodsClass($class_array, $where);
            if ($state) {
                showDialog(core\language::get('nc_common_save_succ'), 'index.php?act=store_goods_class&op=index', 'succ', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();');
            } else {
                showDialog(core\language::get('nc_common_save_fail'));
            }
        } else {
            $class_array = array();
            $class_array['stc_name'] = $_POST['stc_name'];
            $class_array['stc_parent_id'] = isset($_POST['stc_parent_id']) ? intval($_POST['stc_parent_id']) : 0;
            $class_array['stc_state'] = $_POST['stc_state'];
            $class_array['store_id'] = core\session::get('store_id');
            $class_array['stc_sort'] = $_POST['stc_sort'];
            $state = $model_class->addStoreGoodsClass($class_array);
            if ($state) {
                showDialog(core\language::get('nc_common_save_succ'), 'index.php?act=store_goods_class&op=index', 'succ', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();');
            } else {
                showDialog(core\language::get('nc_common_save_fail'));
            }
        }
    }
    /**
     * 卖家商品分类删除
     *
     * @param
     * @return
     */
    public function drop_goods_classOp()
    {
        $model_class = model('store_goods_class');
        $stcid_array = explode(',', $_GET['class_id']);
        foreach ($stcid_array as $key => $val) {
            if (!is_numeric($val)) {
                unset($stcid_array[$key]);
            }
        }
        $where = array();
        $where['stc_id'] = array('in', $stcid_array);
        $where['store_id'] = core\session::get('store_id');
        $drop_state = $model_class->delStoreGoodsClass($where);
        if ($drop_state) {
            showDialog(core\language::get('nc_common_del_succ'), 'index.php?act=store_goods_class&op=index', 'succ');
        } else {
            showDialog(core\language::get('nc_common_del_fail'));
        }
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type, $menu_key = '')
    {
        core\language::read('member_layout');
        $menu_array = array();
        switch ($menu_type) {
            case 'store_goods_class':
                $menu_array = array(1 => array('menu_key' => 'store_goods_class', 'menu_name' => '店铺分类', 'menu_url' => 'index.php?act=store_goods_class&op=index'));
                break;
        }
        core\tpl::output('member_menu', $menu_array);
        core\tpl::output('menu_key', $menu_key);
    }
}