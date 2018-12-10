<?php
/**
 * 品牌管理
 **/
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class store_brand extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('member_store_brand');
    }
    public function indexOp()
    {
        $this->brand_listOp();
    }
    /**
     * 品牌列表
     */
    public function brand_listOp()
    {
        $model_brand = model('brand');
        $condition = array();
        $condition['store_id'] = core\session::get('store_id');
        if (!empty($_GET['brand_name'])) {
            $condition['brand_name'] = array('like', '%' . $_GET['brand_name'] . '%');
        }
        $brand_list = $model_brand->getBrandList($condition, '*', 10);
        core\tpl::output('brand_list', $brand_list);
        core\tpl::output('show_page', $model_brand->showpage());
        self::profile_menu('brand_list', 'brand_list');
        core\tpl::showpage('store_brand.list');
    }
    /**
     * 品牌添加页面
     */
    public function brand_addOp()
    {
        $lang = core\language::getLangContent();
        $model_brand = model('brand');
        if (!empty($_GET['brand_id'])) {
            $brand_array = $model_brand->getBrandInfo(array('brand_id' => $_GET['brand_id'], 'store_id' => core\session::get('store_id')));
            if (empty($brand_array)) {
                error($lang['wrong_argument']);
            }
            core\tpl::output('brand_array', $brand_array);
        }
        // 一级商品分类
        $gc_list = model('goods_class')->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        core\tpl::showpage('store_brand.add', 'null_layout');
    }
    /**
     * 品牌保存
     */
    public function brand_saveOp()
    {
        $lang = core\language::getLangContent();
        $model_brand = model('brand');
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['brand_name'], 'require' => 'true', 'message' => $lang['store_goods_brand_name_null']), array('input' => $_POST['brand_initial'], 'require' => 'true', 'message' => '请填写首字母'));
            $error = $obj_validate->validate();
            if ($error != '') {
                showValidateError($error);
            }
            /**
             * 上传图片
             */
            if (!empty($_FILES['brand_pic']['name'])) {
                $upload = new lib\uploadfile();
                $upload->set('default_dir', ATTACH_BRAND);
                $upload->set('thumb_width', 150);
                $upload->set('thumb_height', 50);
                $upload->set('thumb_ext', '_small');
                $upload->set('ifremove', true);
                $result = $upload->upfile('brand_pic');
                if ($result) {
                    $_POST['brand_pic'] = $upload->thumb_image;
                } else {
                    showDialog($upload->error);
                }
            }
            $insert_array = array();
            $insert_array['brand_name'] = trim($_POST['brand_name']);
            $insert_array['brand_initial'] = strtoupper($_POST['brand_initial']);
            $insert_array['class_id'] = $_POST['class_id'];
            $insert_array['brand_class'] = $_POST['brand_class'];
            $insert_array['brand_pic'] = $_POST['brand_pic'];
            $insert_array['brand_apply'] = 0;
            $insert_array['store_id'] = core\session::get('store_id');
            $result = $model_brand->addBrand($insert_array);
            if ($result) {
                showDialog($lang['store_goods_brand_apply_success'], 'index.php?act=store_brand&op=brand_list', 'succ', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();');
            } else {
                showDialog($lang['nc_common_save_fail']);
            }
        }
    }
    /**
     * 品牌修改
     */
    public function brand_editOp()
    {
        $lang = core\language::getLangContent();
        $model_brand = model('brand');
        if ($_POST['form_submit'] == 'ok' and intval($_POST['brand_id']) != 0) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['brand_name'], 'require' => 'true', 'message' => $lang['store_goods_brand_name_null']), array('input' => $_POST['brand_initial'], 'require' => 'true', 'message' => '请填写首字母'));
            $error = $obj_validate->validate();
            if ($error != '') {
                showValidateError($error);
            } else {
                /**
                 * 上传图片
                 */
                if (!empty($_FILES['brand_pic']['name'])) {
                    $upload = new lib\uploadfile();
                    $upload->set('default_dir', ATTACH_BRAND);
                    $upload->set('thumb_width', 150);
                    $upload->set('thumb_height', 50);
                    $upload->set('thumb_ext', '_small');
                    $upload->set('ifremove', true);
                    $result = $upload->upfile('brand_pic');
                    if ($result) {
                        $_POST['brand_pic'] = $upload->thumb_image;
                    } else {
                        showDialog($upload->error);
                    }
                }
                $where = array();
                $where['brand_id'] = intval($_POST['brand_id']);
                $update_array = array();
                $update_array['brand_initial'] = strtoupper($_POST['brand_initial']);
                $update_array['brand_name'] = trim($_POST['brand_name']);
                $update_array['class_id'] = $_POST['class_id'];
                $update_array['brand_class'] = $_POST['brand_class'];
                if (!empty($_POST['brand_pic'])) {
                    $update_array['brand_pic'] = $_POST['brand_pic'];
                }
                //查出原图片路径，后面会删除图片
                $brand_info = $model_brand->getBrandInfo($where);
                $result = $model_brand->editBrand($where, $update_array);
                if ($result) {
                    //删除老图片
                    if (!empty($brand_info['brand_pic']) && !empty($_POST['brand_pic'])) {
                        unlink(BASE_UPLOAD_PATH . DS . ATTACH_BRAND . DS . $brand_info['brand_pic']);
                    }
                    showDialog($lang['nc_common_save_succ'], 'index.php?act=store_brand&op=brand_list', 'succ', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();');
                } else {
                    showDialog($lang['nc_common_save_fail']);
                }
            }
        } else {
            showDialog($lang['nc_common_save_fail']);
        }
    }
    /**
     * 品牌删除
     */
    public function drop_brandOp()
    {
        $model_brand = model('brand');
        $brand_id = intval($_GET['brand_id']);
        if ($brand_id > 0) {
            $model_brand->delBrand(array('brand_id' => $brand_id, 'brand_apply' => 0, 'store_id' => core\session::get('store_id')));
            showDialog(core\language::get('nc_common_del_succ'), 'index.php?act=store_brand&op=brand_list', 'succ');
        } else {
            showDialog(core\language::get('nc_common_del_fail'));
        }
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @param array 	$array		附加菜单
     * @return
     */
    private function profile_menu($menu_type, $menu_key = '', $array = array())
    {
        core\language::read('member_layout');
        $lang = core\language::getLangContent();
        $menu_array = array();
        switch ($menu_type) {
            case 'brand_list':
                $menu_array = array(1 => array('menu_key' => 'brand_list', 'menu_name' => $lang['nc_member_path_brand_list'], 'menu_url' => 'index.php?act=store_brand&op=brand_list'));
                break;
        }
        if (!empty($array)) {
            $menu_array[] = $array;
        }
        core\tpl::output('member_menu', $menu_array);
        core\tpl::output('menu_key', $menu_key);
    }
}