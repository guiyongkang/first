<?php
/**
 * 商户中心
 *
 **/
namespace biz\control;
use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class index extends BaseSellerControl
{
    /**
     * 构造方法
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 商户中心首页
     *
     */
    public function indexOp()
    {
        core\language::read('member_home_index');
        // 店铺信息
        $store_info = $this->store_info;
        if (intval($store_info['store_end_time']) > 0) {
            $store_info['store_end_time_text'] = date('Y-m-d', $store_info['store_end_time']);
            $reopen_time = $store_info['store_end_time'] - 3600 * 24 + 1 - TIMESTAMP;
            if (!checkPlatformStore() && $store_info['store_end_time'] - TIMESTAMP >= 0 && $reopen_time < 2592000) {
                //到期续签提醒(<30天)
                $store_info['reopen_tip'] = true;
            }
        } else {
            $store_info['store_end_time_text'] = lang('store_no_limit');
        }
        // 店铺等级信息
        $store_info['grade_name'] = $this->store_grade['sg_name'];
        $store_info['grade_goodslimit'] = $this->store_grade['sg_goods_limit'];
        $store_info['grade_albumlimit'] = $this->store_grade['sg_album_limit'];
        core\tpl::output('store_info', $store_info);
		// 商家帮助
        $model_help = model('help');
        $condition	= array();
        $condition['help_show'] = '1';//是否显示,0为否,1为是
        $help_list = $model_help->getStoreHelpTypeList($condition, '', 6);
        core\tpl::output('help_list', $help_list);
        // 销售情况统计
        $field = ' COUNT(*) as ordernum,SUM(order_amount) as orderamount ';
        $where = array();
        $where['store_id'] = core\session::get('store_id');
        $where['order_isvalid'] = 1;
        //计入统计的有效订单
        // 昨日销量
        $where['order_add_time'] = array('between', array(strtotime(date('Y-m-d', time() - 3600 * 24)), strtotime(date('Y-m-d', time())) - 1));
        $daily_sales = model('stat')->getoneByStatorder($where, $field);
        core\tpl::output('daily_sales', $daily_sales);
        // 月销量
        $where['order_add_time'] = array('gt', strtotime(date('Y-m', time())));
        $monthly_sales = model('stat')->getoneByStatorder($where, $field);
        core\tpl::output('monthly_sales', $monthly_sales);
        unset($field, $where);
        //单品销售排行
        //最近30天
        $stime = strtotime(date('Y-m-d', time() - 3600 * 24)) - 86400 * 29;
        //30天前
        $etime = strtotime(date('Y-m-d', time())) - 1;
        //昨天23:59
        $where = array();
        $where['store_id'] = core\session::get('store_id');
        $where['order_isvalid'] = 1;
        //计入统计的有效订单
        $where['order_add_time'] = array('between', array($stime, $etime));
        $field = ' goods_id,goods_name,SUM(goods_num) as goodsnum,goods_image ';
        $orderby = 'goodsnum desc,goods_id';
        $goods_list = model('stat')->statByStatordergoods($where, $field, 0, 8, $orderby, 'goods_id');
        unset($stime, $etime, $where, $field, $orderby);
        core\tpl::output('goods_list', $goods_list);
        if (!checkPlatformStore()) {
            if (core\config::get('groupbuy_allow') == 1) {
                // 抢购套餐
                $groupquota_info = model('groupbuy_quota')->getGroupbuyQuotaCurrent(core\session::get('store_id'));
                core\tpl::output('groupquota_info', $groupquota_info);
            }
            if (intval(core\config::get('promotion_allow')) == 1) {
                // 限时折扣套餐
                $xianshiquota_info = model('p_xianshi_quota')->getXianshiQuotaCurrent(core\session::get('store_id'));
                core\tpl::output('xianshiquota_info', $xianshiquota_info);
                // 满即送套餐
                $mansongquota_info = model('p_mansong_quota')->getMansongQuotaCurrent(core\session::get('store_id'));
                core\tpl::output('mansongquota_info', $mansongquota_info);
                // 优惠套装套餐
                $binglingquota_info = model('p_bundling')->getBundlingQuotaInfoCurrent(core\session::get('store_id'));
                core\tpl::output('binglingquota_info', $binglingquota_info);
                // 推荐展位套餐
                $boothquota_info = model('p_booth')->getBoothQuotaInfoCurrent(core\session::get('store_id'));
                core\tpl::output('boothquota_info', $boothquota_info);
            }
            if (core\config::get('voucher_allow') == 1) {
                $voucherquota_info = model('voucher')->getCurrentQuota(core\session::get('store_id'));
                core\tpl::output('voucherquota_info', $voucherquota_info);
            }
        } else {
            core\tpl::output('isOwnShop', true);
        }
        $phone_array = explode(',', core\config::get('site_phone'));
        core\tpl::output('phone_array', $phone_array);
        core\tpl::output('menu_sign', 'index');
        core\tpl::showpage('index');
    }
    /**
     * 异步取得卖家统计类信息
     *
     */
    public function statisticsOp()
    {
        $add_time_to = strtotime(date("Y-m-d") + 60 * 60 * 24);
        //当前日期 ,从零点来时
        $add_time_from = strtotime(date("Y-m-d", strtotime(date("Y-m-d")) - 60 * 60 * 24 * 30));
        //30天前
        $goods_online = 0;
        // 出售中商品
        $goods_waitverify = 0;
        // 等待审核
        $goods_verifyfail = 0;
        // 审核失败
        $goods_offline = 0;
        // 仓库待上架商品
        $goods_lockup = 0;
        // 违规下架商品
        $consult = 0;
        // 待回复商品咨询
        $no_payment = 0;
        // 待付款
        $no_delivery = 0;
        // 待发货
        $no_receipt = 0;
        // 待收货
        $refund_lock = 0;
        // 售前退款
        $refund = 0;
        // 售后退款
        $return_lock = 0;
        // 售前退货
        $return = 0;
        // 售后退货
        $complain = 0;
        //进行中投诉
        $model_goods = model('goods');
        // 全部商品数
        $goodscount = $model_goods->getGoodsCommonCount(array('store_id' => core\session::get('store_id')));
        // 出售中的商品
        $goods_online = $model_goods->getGoodsCommonOnlineCount(array('store_id' => core\session::get('store_id')));
        if (core\config::get('goods_verify')) {
            // 等待审核的商品
            $goods_waitverify = $model_goods->getGoodsCommonWaitVerifyCount(array('store_id' => core\session::get('store_id')));
            // 审核失败的商品
            $goods_verifyfail = $model_goods->getGoodsCommonVerifyFailCount(array('store_id' => core\session::get('store_id')));
        }
        // 仓库待上架的商品
        $goods_offline = $model_goods->getGoodsCommonOfflineCount(array('store_id' => core\session::get('store_id')));
        // 违规下架的商品
        $goods_lockup = $model_goods->getGoodsCommonLockUpCount(array('store_id' => core\session::get('store_id')));
        // 等待回复商品咨询
        $consult = model('consult')->getConsultCount(array('store_id' => core\session::get('store_id'), 'consult_reply' => ''));
        // 商品图片数量
        $imagecount = model('album')->getAlbumPicCount(array('store_id' => core\session::get('store_id')));
        $model_order = model('order');
        // 交易中的订单
        $progressing = $model_order->getOrderCountByID('store', core\session::get('store_id'), 'TradeCount');
        // 待付款
        $no_payment = $model_order->getOrderCountByID('store', core\session::get('store_id'), 'NewCount');
        // 待发货
        $no_delivery = $model_order->getOrderCountByID('store', core\session::get('store_id'), 'PayCount');
        $model_refund_return = model('refund_return');
        // 售前退款
        $condition = array();
        $condition['store_id'] = core\session::get('store_id');
        $condition['refund_type'] = 1;
        $condition['order_lock'] = 2;
        $condition['refund_state'] = array('lt', 3);
        $refund_lock = $model_refund_return->getRefundReturnCount($condition);
        // 售后退款
        $condition = array();
        $condition['store_id'] = core\session::get('store_id');
        $condition['refund_type'] = 1;
        $condition['order_lock'] = 1;
        $condition['refund_state'] = array('lt', 3);
        $refund = $model_refund_return->getRefundReturnCount($condition);
        // 售前退货
        $condition = array();
        $condition['store_id'] = core\session::get('store_id');
        $condition['refund_type'] = 2;
        $condition['order_lock'] = 2;
        $condition['refund_state'] = array('lt', 3);
        $return_lock = $model_refund_return->getRefundReturnCount($condition);
        // 售后退货
        $condition = array();
        $condition['store_id'] = core\session::get('store_id');
        $condition['refund_type'] = 2;
        $condition['order_lock'] = 1;
        $condition['refund_state'] = array('lt', 3);
        $return = $model_refund_return->getRefundReturnCount($condition);
        $condition = array();
        $condition['accused_id'] = core\session::get('store_id');
        $condition['complain_state'] = array(array('gt', 10), array('lt', 90), 'and');
        $complain = model()->table('complain')->where($condition)->count();
        //待确认的结算账单
        $model_bill = model('bill');
        $condition = array();
        $condition['ob_store_id'] = core\session::get('store_id');
        $condition['ob_state'] = BILL_STATE_CREATE;
        $bill_confirm_count = $model_bill->getOrderBillCount($condition);
        //统计数组
        $statistics = array('goodscount' => $goodscount, 'online' => $goods_online, 'waitverify' => $goods_waitverify, 'verifyfail' => $goods_verifyfail, 'offline' => $goods_offline, 'lockup' => $goods_lockup, 'imagecount' => $imagecount, 'consult' => $consult, 'progressing' => $progressing, 'payment' => $no_payment, 'delivery' => $no_delivery, 'refund_lock' => $refund_lock, 'refund' => $refund, 'return_lock' => $return_lock, 'return' => $return, 'complain' => $complain, 'bill_confirm' => $bill_confirm_count);
        exit(json_encode($statistics));
    }
    /**
     * 添加快捷操作
     */
    function quicklink_addOp()
    {
        if (!empty($_POST['item'])) {
            core\session::set('seller_quicklink.' . $_POST['item'], $_POST['item']);
        }
        $this->_update_quicklink();
        echo 'true';
    }
    /**
     * 删除快捷操作
     */
    function quicklink_delOp()
    {
        if (!empty($_POST['item'])) {
            core\session::delete('seller_quicklink.' . $_POST['item']);
        }
        $this->_update_quicklink();
        echo 'true';
    }
    private function _update_quicklink()
    {
        $quicklink = implode(',', core\session::get('seller_quicklink'));
        $update_array = array('seller_quicklink' => $quicklink);
        $condition = array('seller_id' => core\session::get('seller_id'));
        $model_seller = model('seller');
        $model_seller->editSeller($update_array, $condition);
    }
	/**
     * json输出地址数组 原data/resource/js/area_array.js
     */
    public function json_areaOp()
    {
        echo $_GET['callback'] . '('.json_encode(model('area')->getAreaArrayForJson()).')';
    }
	/**
     * json输出地址数组
     */
    public function json_area_showOp()
    {
        $area_info['text'] = model('area')->getTopAreaName(intval($_GET['area_id']));
        echo $_GET['callback'] . '(' . json_encode($area_info) . ')';
    }
	//json输出商品分类
    public function josn_classOp()
    {
        /**
         * 实例化商品分类模型
         */
        $model_class = model('goods_class');
        $goods_class = $model_class->getGoodsClassListByParentId(intval($_GET['gc_id']));
        $array = array();
        if (!empty($goods_class) and is_array($goods_class)) {
            foreach ($goods_class as $val) {
                $array[$val['gc_id']] = array('gc_id' => $val['gc_id'], 'gc_name' => htmlspecialchars($val['gc_name']), 'gc_parent_id' => $val['gc_parent_id'], 'commis_rate' => $val['commis_rate'], 'gc_sort' => $val['gc_sort']);
            }
        }
        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK') {
            $array = core\language::getUTF8(array_values($array));
            //网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        } else {
            $array = array_values($array);
        }
        echo $_GET['callback'] . '(' . json_encode($array) . ')';
    }
}