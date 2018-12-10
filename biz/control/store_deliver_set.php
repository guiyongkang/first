<?php
/**
 * 发货设置
 *
 */
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class store_deliver_set extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('member_store_index,deliver');
    }
    /**
     * 发货地址列表
     */
    public function daddress_listOp()
    {
        core\language::read('member_member_index');
        $model_daddress = model('daddress');
        $condition = array();
        $condition['store_id'] = core\session::get('store_id');
        $address_list = $model_daddress->getAddressList($condition, '*', '', 20);
        core\tpl::output('address_list', $address_list);
        self::profile_menu('daddress', 'daddress');
        core\tpl::showpage('store_deliver_set.daddress_list');
    }
    /**
     * 新增/编辑发货地址
     */
    public function daddress_addOp()
    {
        core\language::read('member_member_index');
        $lang = core\language::getLangContent();
        $model_daddress = model('daddress');
        if (chksubmit()) {
            //保存 新增/编辑 表单
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['seller_name'], 'require' => 'true', 'message' => $lang['store_daddress_receiver_null']), array('input' => $_POST['area_id'], 'require' => 'true', 'validator' => 'Number', 'message' => $lang['store_daddress_wrong_area']), array('input' => $_POST['city_id'], 'require' => 'true', 'validator' => 'Number', 'message' => $lang['store_daddress_wrong_area']), array('input' => $_POST['region'], 'require' => 'true', 'message' => $lang['store_daddress_area_null']), array('input' => $_POST['address'], 'require' => 'true', 'message' => $lang['store_daddress_address_null']), array('input' => $_POST['telphone'], 'require' => 'true', 'message' => isset($lang['store_daddress_phone_and_mobile']) ? $lang['store_daddress_phone_and_mobile'] : ''));
            $error = $obj_validate->validate();
            if ($error != '') {
                showValidateError($error);
            }
            $data = array('store_id' => core\session::get('store_id'), 'seller_name' => $_POST['seller_name'], 'area_id' => $_POST['area_id'], 'city_id' => $_POST['city_id'], 'area_info' => $_POST['region'], 'address' => $_POST['address'], 'telphone' => $_POST['telphone'], 'company' => $_POST['company']);
            $address_id = intval($_POST['address_id']);
            if ($address_id > 0) {
                $condition = array();
                $condition['address_id'] = $address_id;
                $condition['store_id'] = core\session::get('store_id');
                $update = $model_daddress->editAddress($data, $condition);
                if (!$update) {
                    showDialog($lang['store_daddress_modify_fail'], '', 'error');
                }
            } else {
                $insert = $model_daddress->addAddress($data);
                if (!$insert) {
                    showDialog($lang['store_daddress_add_fail'], '', 'error');
                }
            }
            showDialog($lang['nc_common_op_succ'], 'reload', 'succ', 'CUR_DIALOG.close()');
        } elseif (isset($_GET['address_id']) && is_numeric($_GET['address_id']) > 0) {
            //编辑
            $condition = array();
            $condition['address_id'] = intval($_GET['address_id']);
            $condition['store_id'] = core\session::get('store_id');
            $address_info = $model_daddress->getAddressInfo($condition);
            if (empty($address_info) && !is_array($address_info)) {
                error($lang['store_daddress_wrong_argument'], 'index.php?act=store_deliver_set&op=daddress_list');
            }
            core\tpl::output('address_info', $address_info);
        }
        core\tpl::showpage('store_deliver_set.daddress_add', 'null_layout');
    }
    /**
     * 删除发货地址
     */
    public function daddress_delOp()
    {
        $address_id = intval($_GET['address_id']);
        if ($address_id <= 0) {
            showDialog(core\language::get('store_daddress_del_fail'), '', 'error');
        }
        $condition = array();
        $condition['address_id'] = $address_id;
        $condition['store_id'] = core\session::get('store_id');
        $delete = model('daddress')->delAddress($condition);
        if ($delete) {
            showDialog(core\language::get('store_daddress_del_succ'), 'index.php?act=store_deliver_set&op=daddress_list', 'succ');
        } else {
            showDialog(core\language::get('store_daddress_del_fail'), '', 'error');
        }
    }
    /**
     * 设置默认发货地址
     */
    public function daddress_default_setOp()
    {
        $address_id = intval($_GET['address_id']);
        if ($address_id <= 0) {
            return false;
        }
        $condition = array();
        $condition['store_id'] = core\session::get('store_id');
        $update = model('daddress')->editAddress(array('is_default' => 0), $condition);
        $condition['address_id'] = $address_id;
        $update = model('daddress')->editAddress(array('is_default' => 1), $condition);
    }
    public function expressOp()
    {
        $model = model('store_extend');
        if (chksubmit()) {
            $data['store_id'] = core\session::get('store_id');
            if (is_array($_POST['cexpress']) && !empty($_POST['cexpress'])) {
                $data['express'] = implode(',', $_POST['cexpress']);
            } else {
                $data['express'] = '';
            }
            if (!$model->getby_store_id(core\session::get('store_id'))) {
                $result = $model->insert($data);
            } else {
                $result = $model->where(array('store_id' => core\session::get('store_id')))->update($data);
            }
            if ($result) {
                showDialog(core\language::get('nc_common_save_succ'), 'reload', 'succ');
            } else {
                showDialog(core\language::get('nc_common_save_fail'), 'reload', 'error');
            }
        }
        $express_list = rkcache('express', true);
        //取得店铺启用的快递公司ID
        $express_select = $model->getfby_store_id(core\session::get('store_id'), 'express');
        if (!is_null($express_select)) {
            $express_select = explode(',', $express_select);
        } else {
            $express_select = array();
        }
        core\tpl::output('express_select', $express_select);
        //页面输出
        self::profile_menu('daddress', 'express');
        core\tpl::output('express_list', $express_list);
        core\tpl::showpage('store_deliver_express');
    }
    /**
     * 免运费额度设置
     */
    public function free_freightOp()
    {
        $model_store = model('store');
        if (chksubmit()) {
            $store_free_price = floatval(abs($_POST['store_free_price']));
            $store_free_time = $_POST['store_free_time'];
            $model_store->editStore(array('store_free_price' => $store_free_price, 'store_free_time' => $store_free_time), array('store_id' => core\session::get('store_id')));
            showDialog(lang('nc_common_save_succ'), '', 'succ');
        }
        core\tpl::output('store_free_price', $this->store_info['store_free_price']);
        core\tpl::output('store_free_time', $this->store_info['store_free_time']);
        self::profile_menu('daddress', 'free_freight');
        core\tpl::showpage('store_free_freight.index');
    }
    /**
     * 默认配送区域设置
     */
    public function deliver_regionOp()
    {
        if (chksubmit()) {
            model('store')->editStore(array('deliver_region' => $_POST['area_ids'] . '|' . $_POST['region']), array('store_id' => core\session::get('store_id')));
            showDialog(lang('nc_common_save_succ'), '', 'succ');
        }
        $deliver_region = array('', '');
        if (strpos($this->store_info['deliver_region'], '|')) {
            $deliver_region = explode('|', $this->store_info['deliver_region']);
        }
        core\tpl::output('deliver_region', $deliver_region);
        self::profile_menu('daddress', 'deliver_region');
        core\tpl::showpage('store_default_deliver_region.index');
    }
    /**
     * 发货单打印设置
     */
    public function print_setOp()
    {
        $model = model();
        $store_info = $model->table('store')->where(array('store_id' => core\session::get('store_id')))->find();
        if (empty($store_info)) {
            showDialog(core\language::get('store_storeinfo_error'), 'index.php?act=store_printsetup', 'error');
        }
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array('input' => $_POST['store_printdesc'], 'require' => 'true', 'validator' => 'Length', 'min' => 1, 'max' => 200, 'message' => core\language::get('store_printsetup_desc_error')));
            $error = $obj_validate->validate();
            if ($error != '') {
                showDialog($error);
            }
            $update_arr = array();
            //上传认证文件
            if ($_FILES['store_stamp']['name'] != '') {
                $upload = new lib\uploadfile();
                $upload->set('default_dir', ATTACH_STORE);
                if ($_FILES['store_stamp']['name'] != '') {
                    $result = $upload->upfile('store_stamp');
                    if ($result) {
                        $update_arr['store_stamp'] = $upload->file_name;
                        //删除旧认证图片
                        if (!empty($store_info['store_stamp'])) {
                            unlink(BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $store_info['store_stamp']);
                        }
                    }
                }
            }
            $update_arr['store_printdesc'] = $_POST['store_printdesc'];
            $rs = $model->table('store')->where(array('store_id' => core\session::get('store_id')))->update($update_arr);
            if ($rs) {
                showDialog(core\language::get('nc_common_save_succ'), '', 'succ');
            } else {
                showDialog(core\language::get('nc_common_save_fail'), '', 'error');
            }
        } else {
            core\tpl::output('store_info', $store_info);
            self::profile_menu('daddress', 'print_set');
            core\tpl::showpage('store_printsetup');
        }
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type, $menu_key = '')
    {
        core\language::read('member_layout');
        switch ($menu_type) {
            case 'daddress':
                $menu_array = array(array('menu_key' => 'daddress', 'menu_name' => core\language::get('store_deliver_daddress_list'), 'menu_url' => 'index.php?act=store_deliver_set&op=daddress_list'), array('menu_key' => 'express', 'menu_name' => core\language::get('store_deliver_default_express'), 'menu_url' => 'index.php?act=store_deliver_set&op=express'), array('menu_key' => 'free_freight', 'menu_name' => '免运费额度', 'menu_url' => 'index.php?act=store_deliver_set&op=free_freight'), array('menu_key' => 'deliver_region', 'menu_name' => '默认配送地区', 'menu_url' => 'index.php?act=store_deliver_set&op=deliver_region'), array('menu_key' => 'print_set', 'menu_name' => '发货单打印设置', 'menu_url' => 'index.php?act=store_deliver_set&op=print_set'));
                break;
        }
        core\tpl::output('member_menu', $menu_array);
        core\tpl::output('menu_key', $menu_key);
    }
}