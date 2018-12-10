<?php
/**
 * 限时折扣管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class promotion_booth extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        //检查审核功能是否开启
        if (isset($_GET['promotion_allow']) && intval($_GET['promotion_allow']) !== 1 && intval(core\config::get('promotion_allow')) !== 1) {
            $url = 'index.php?act=promotion_bundling&promotion_allow=1';
            error('商品促销功能尚未开启', $url);
        }
    }
    /**
     * 默认Op
     */
    public function indexOp()
    {
        //自动开启优惠套装
        if (intval($_GET['promotion_allow']) === 1) {
            $model_setting = model('setting');
            $update_array = array();
            $update_array['promotion_allow'] = 1;
            $model_setting->updateSetting($update_array);
        }
        $this->goods_listOp();
    }
    public function goods_listOp()
    {
        /**
         * 处理商品分类
         */
        $choose_gcid = ($t = (isset($_REQUEST['choose_gcid']) ? intval($_REQUEST['choose_gcid']) : 0)) > 0 ? $t : 0;
        $gccache_arr = model('goods_class')->getGoodsclassCache($choose_gcid, 3);
        core\tpl::output('gc_json', json_encode($gccache_arr['showclass']));
        core\tpl::output('gc_choose_json', json_encode($gccache_arr['choose_gcid']));
        $model_booth = model('p_booth');
        $where = array();
        if (isset($_GET['choose_gcid']) && intval($_GET['choose_gcid']) > 0) {
            $where['gc_id'] = intval($_GET['choose_gcid']);
        }
        $goods_list = $model_booth->getBoothGoodsList($where, 'goods_id', 10);
        if (!empty($goods_list)) {
            $goodsid_array = array();
            foreach ($goods_list as $val) {
                $goodsid_array[] = $val['goods_id'];
            }
            $goods_list = model('goods')->getGoodsList(array('goods_id' => array('in', $goodsid_array)));
        }
        core\tpl::output('gc_list', model('goods_class')->getGoodsClassForCacheModel());
        core\tpl::output('goods_list', $goods_list);
        core\tpl::output('show_page', $model_booth->showpage(2));
        // 输出自营店铺IDS
        core\tpl::output('flippedOwnShopIds', array_flip(model('store')->getOwnShopIds()));
        core\tpl::showpage('promotion_booth_goods.list');
    }
    /**
     * 套餐列表
     */
    public function booth_quota_listOp()
    {
        $model_booth = model('p_booth');
        $where = array();
        if (!empty($_GET['store_name'])) {
            $where['store_name'] = array('like', '%' . trim($_GET['store_name']) . '%');
        }
        $booth_list = $model_booth->getBoothQuotaList($where, '*', 10);
        // 状态数组
        $state_array = array(0 => lang('close'), 1 => lang('open'));
        core\tpl::output('state_array', $state_array);
        core\tpl::output('booth_list', $booth_list);
        core\tpl::output('show_page', $model_booth->showpage(2));
        core\tpl::showpage('promotion_booth_quota.list');
    }
    /**
     * 删除推荐商品
     */
    public function del_goodsOp()
    {
        $where = array();
        // 验证id是否正确
        if (is_array($_POST['goods_id'])) {
            foreach ($_POST['goods_id'] as $val) {
                if (!is_numeric($val)) {
                    error(lang('nc_common_del_fail'));
                }
            }
            $where['goods_id'] = array('in', $_POST['goods_id']);
        } elseif (intval($_GET['goods_id']) >= 0) {
            $where['goods_id'] = intval($_GET['goods_id']);
        } else {
            error(lang('nc_common_del_fail'));
        }
        $rs = model('p_booth')->delBoothGoods($where);
        if ($rs) {
            success(lang('nc_common_del_succ'));
        } else {
            error(lang('nc_common_del_fail'));
        }
    }
    /**
     * 设置
     */
    public function booth_settingOp()
    {
        // 实例化模型
        $model_setting = model('setting');
        if (chksubmit()) {
            // 验证
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["promotion_booth_price"], "require" => "true", 'validator' => 'Number', "message" => '请填写展位价格'), array("input" => $_POST["promotion_booth_goods_sum"], "require" => "true", 'validator' => 'Number', "message" => '不能为空，且不小于1的整数'));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            }
            $data['promotion_booth_price'] = intval($_POST['promotion_booth_price']);
            $data['promotion_booth_goods_sum'] = intval($_POST['promotion_booth_goods_sum']);
            $return = $model_setting->updateSetting($data);
            if ($return) {
                $this->log(lang('nc_set') . ' 推荐展位');
                success(lang('nc_common_op_succ'));
            } else {
                error(lang('nc_common_op_fail'));
            }
        }
        // 查询setting列表
        $setting = $model_setting->GetListSetting();
        core\tpl::output('setting', $setting);
        core\tpl::showpage('promotion_booth.setting');
    }
}