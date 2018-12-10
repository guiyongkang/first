<?php
/**
 * 购买
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class member_buy extends mobileMemberControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 购物车、直接购买第一步:选择收获地址和配置方式
     */
    public function buy_step1Op()
    {
        $cart_id = explode(',', $_POST['cart_id']);
        $logic_buy = logic('buy');
        // 得到会员等级
        $model_member = model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info) {
            $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
            $member_discount = isset($member_gradeinfo['orderdiscount']) ? $member_gradeinfo['orderdiscount'] : 0;
            $member_level = $member_gradeinfo['level'];
        } else {
            $member_discount = $member_level = 0;
        }
        // 得到购买数据
        $result = $logic_buy->buyStep1($cart_id, isset($_POST['ifcart']) ? $_POST['ifcart'] : 0, $this->member_info['member_id'], $this->member_info['store_id'], null, $member_discount, $member_level);
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }
        if (isset($_POST['address_id']) && intval($_POST['address_id']) > 0) {
            $result['address_info'] = model('address')->getDefaultAddressInfo(array('address_id' => intval($_POST['address_id']), 'member_id' => $this->member_info['member_id']));
        }
		if ($result['address_info']) {
			$data_area = $logic_buy->changeAddr($result['freight_list'], $result['address_info']['city_id'], $result['address_info']['area_id'], $this->member_info['member_id']);
			if (!empty($data_area) && $data_area['state'] == 'success') {
				if (is_array($data_area['content'])) {
					foreach ($data_area['content'] as $store_id => $value) {
						$data_area['content'][$store_id] = ncPriceFormat($value);
					}
				}
			} else {
				output_error('地区请求失败');
			}
		}
        // 整理数据
        $store_cart_list = array();
        $store_total_list = $result['store_goods_total'];
        foreach ($result['store_cart_list'] as $key => $value) {
            $store_cart_list[$key]['goods_list'] = $value;
            $store_cart_list[$key]['store_goods_total'] = $result['store_goods_total'][$key];
            $store_cart_list[$key]['store_mansong_rule_list'] = isset($result['store_mansong_rule_list'][$key]) ? $result['store_mansong_rule_list'][$key] : array();
            if (!empty($result['store_voucher_list'][$key]) && is_array($result['store_voucher_list'][$key])) {
                reset($result['store_voucher_list'][$key]);
                $store_cart_list[$key]['store_voucher_info'] = current($result['store_voucher_list'][$key]);
                $store_cart_list[$key]['store_voucher_info']['voucher_price'] = ncPriceFormat($store_cart_list[$key]['store_voucher_info']['voucher_price']);
                // $store_total_list[$key] = $store_cart_list[$key]['store_voucher_info']['voucher_price'];
            } else {
                $store_cart_list[$key]['store_voucher_info'] = array();
            }
            $store_cart_list[$key]['store_voucher_list'] = $result['store_voucher_list'][$key];
            if (!empty($result['cancel_calc_sid_list'][$key])) {
                $store_cart_list[$key]['freight'] = '0';
                $store_cart_list[$key]['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
            }
            $store_cart_list[$key]['store_name'] = $value[0]['store_name'];
        }
        $buy_list = array();
        $buy_list['store_cart_list'] = $store_cart_list;
        $buy_list['freight_hash'] = $result['freight_list'];
        $buy_list['address_info'] = $result['address_info'];
        $buy_list['ifshow_offpay'] = $result['ifshow_offpay'];
        $buy_list['vat_hash'] = $result['vat_hash'];
        $buy_list['inv_info'] = $result['inv_info'];
        $buy_list['available_predeposit'] = isset($result['available_predeposit']) ? $result['available_predeposit'] : '';
        $buy_list['available_rc_balance'] = isset($result['available_rc_balance']) ? $result['available_rc_balance'] : '';
        if (!empty($result['rpt_list']) && is_array($result['rpt_list'])) {
            foreach ($result['rpt_list'] as $k => $v) {
                unset($result['rpt_list'][$k]['rpacket_id']);
                unset($result['rpt_list'][$k]['rpacket_end_date']);
                unset($result['rpt_list'][$k]['rpacket_owner_id']);
                unset($result['rpt_list'][$k]['rpacket_code']);
            }
        }
        $buy_list['rpt_list'] = isset($result['rpt_list']) ? $result['rpt_list'] : array();
        $buy_list['zk_list'] = isset($result['zk_list']) ? $result['zk_list'] : array();
		$store_total_list = logic('buy_1')->reCalcGoodsTotal($store_total_list, isset($data_area['content']) ? $data_area['content'] : array(), 'freight');
		$buy_list['order_amount'] = ncPriceFormat(array_sum($store_total_list) - (isset($result['rpt_info']['rpacket_price']) ? $result['rpt_info']['rpacket_price'] : 0));
		foreach ($store_total_list as $store_id => $value) {
			$store_total_list[$store_id] = ncPriceFormat($value);
		}
		$buy_list['store_final_total_list'] = $store_total_list;
		
        $buy_list['rpt_info'] = !empty($result['rpt_info']) ? $result['rpt_info'] : array();
        $buy_list['address_api'] = !empty($data_area) ? $data_area : array();
        output_data($buy_list);
    }
    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     */
    public function buy_step2Op()
    {
        $param = array();
        $param['ifcart'] = $_POST['ifcart'];
        $param['cart_id'] = explode(',', $_POST['cart_id']);
        $param['address_id'] = $_POST['address_id'];
        $param['vat_hash'] = $_POST['vat_hash'];
        $param['offpay_hash'] = $_POST['offpay_hash'];
        $param['offpay_hash_batch'] = $_POST['offpay_hash_batch'];
        $param['pay_name'] = $_POST['pay_name'];
        $param['invoice_id'] = $_POST['invoice_id'];
        $param['rpt'] = $_POST['rpt'];
        // 处理代金券
        $voucher = array();
        $post_voucher = explode(',', $_POST['voucher']);
        if (!empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($voucher_t_id, $store_id, $voucher_price) = explode('|', $value);
                $voucher[$store_id] = $value;
            }
        }
        $param['voucher'] = $voucher;
        $_POST['pay_message'] = !empty($_POST['pay_message']) ? trim($_POST['pay_message'], ',') : '';
        $_POST['pay_message'] = !empty($_POST['pay_message']) ? explode(',', $_POST['pay_message']) : array();
        $param['pay_message'] = array();
        if (!empty($_POST['pay_message']) && is_array($_POST['pay_message'])) {
            foreach ($_POST['pay_message'] as $v) {
                if (strpos($v, '|') !== false) {
                    $v = explode('|', $v);
                    $param['pay_message'][$v[0]] = $v[1];
                }
            }
        }
        $param['pd_pay'] = $_POST['pd_pay'];
        $param['rcb_pay'] = $_POST['rcb_pay'];
        $param['password'] = isset($_POST['password']) ? $_POST['password'] : '';
        $param['fcode'] = $_POST['fcode'];
        $param['order_from'] = 2;
        $logic_buy = logic('buy');
        // 得到会员等级
        $model_member = model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info) {
            $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
            $member_discount = isset($member_gradeinfo['orderdiscount']) ? $member_gradeinfo['orderdiscount'] : 0;
            $member_level = isset($member_gradeinfo['level']) ? $member_gradeinfo['level'] : 0;
        } else {
            $member_discount = $member_level = 0;
        }
		
		if($this->member_info['is_distributor']==1){
			$owner_id = $this->member_info['member_id'];
		}else{
			$owner_id = $this->member_info['inviter_id'];
		}
		
        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email'], $owner_id);
		
		if (!$result['state']) {
            output_error($result['msg']);
        }
        $order_info = current($result['data']['order_list']);
        output_data(array('pay_sn' => $result['data']['pay_sn'], 'payment_code' => $order_info['payment_code']));
    }
    /**
     * 验证密码
     */
    public function check_passwordOp()
    {
        if (empty($_POST['password'])) {
            output_error('参数错误');
        }
        $model_member = model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info['member_paypwd'] == md5($_POST['password'])) {
            output_data('1');
        } else {
            output_error('密码错误');
        }
    }
    /**
     * 更换收货地址
     */
    public function change_addressOp()
    {
        $logic_buy = logic('buy');
        if (empty($_POST['city_id'])) {
            $_POST['city_id'] = $_POST['area_id'];
        }
        $data = $logic_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $this->member_info['member_id']);
        if (!empty($data) && $data['state'] == 'success') {
            output_data($data);
        } else {
            output_error('地址修改失败');
        }
    }
    /**
     * 实物订单支付(新接口)
     */
    public function payOp()
    {
        $pay_sn = $_POST['pay_sn'];
        if (!preg_match('/^\\d{18}$/', $pay_sn)) {
            output_error('该订单不存在');
        }
        // 查询支付单信息
        $model_order = model('order');
        $pay_info = $model_order->getOrderPayInfo(array('pay_sn' => $pay_sn, 'buyer_id' => $this->member_info['member_id']), true);
        if (empty($pay_info)) {
            output_error('该订单不存在');
        }
        // 取子订单列表
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_PAY));
        $order_list = $model_order->getOrderList($condition, '', '*', '', '', array(), true);
        if (empty($order_list)) {
            output_error('未找到需要支付的订单');
        }
        // 定义输出数组
        $pay = array();
        // 支付提示主信息
        // 订单总支付金额(不包含货到付款)
        $pay['pay_amount'] = 0;
        // 充值卡支付金额(之前支付中止，余额被锁定)
        $pay['payed_rcb_amount'] = 0;
        // 预存款支付金额(之前支付中止，余额被锁定)
        $pay['payed_pd_amount'] = 0;
        // 还需在线支付金额(之前支付中止，余额被锁定)
        $pay['pay_diff_amount'] = 0;
        // 账户可用金额
        $pay['member_available_pd'] = 0;
        $pay['member_available_rcb'] = 0;
        $logic_order = logic('order');
        // 计算相关支付金额
        foreach ($order_list as $key => $order_info) {
            if (!in_array($order_info['payment_code'], array('offline', 'chain'))) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $pay['payed_rcb_amount'] += $order_info['rcb_amount'];
                    $pay['payed_pd_amount'] += $order_info['pd_amount'];
                    $pay['pay_diff_amount'] += $order_info['order_amount'] - $order_info['rcb_amount'] - $order_info['pd_amount'];
                }
            }
        }
        if (isset($order_info['chain_id']) && $order_info['chain_id'] && $order_info['payment_code'] == 'chain') {
            $order_list[0]['order_remind'] = '下单成功，请在' . CHAIN_ORDER_PAYPUT_DAY . '日内前往门店提货，逾期订单将自动取消。';
            $flag_chain = 1;
        }
        // 如果线上线下支付金额都为0，转到支付成功页
        if (empty($pay['pay_diff_amount'])) {
            output_error('订单重复支付');
        }
        $payment_list = model('mb_payment')->getMbPaymentOpenList();
        if (!empty($payment_list)) {
            foreach ($payment_list as $k => $value) {
                if ($value['payment_code'] == 'wxpay') {
                    unset($payment_list[$k]);
                    continue;
                }
                unset($payment_list[$k]['payment_id']);
                unset($payment_list[$k]['payment_config']);
                unset($payment_list[$k]['payment_state']);
                unset($payment_list[$k]['payment_state_text']);
            }
        }
        // 显示预存款、支付密码、充值卡
        $pay['member_available_pd'] = $this->member_info['available_predeposit'];
        $pay['member_available_rcb'] = $this->member_info['available_rc_balance'];
        $pay['member_paypwd'] = $this->member_info['member_paypwd'] ? true : false;
        $pay['pay_sn'] = $pay_sn;
        $pay['payed_amount'] = ncPriceFormat($pay['payed_rcb_amount'] + $pay['payed_pd_amount']);
        unset($pay['payed_pd_amount']);
        unset($pay['payed_rcb_amount']);
        $pay['pay_amount'] = ncPriceFormat($pay['pay_diff_amount']);
        unset($pay['pay_diff_amount']);
        $pay['member_available_pd'] = ncPriceFormat($pay['member_available_pd']);
        $pay['member_available_rcb'] = ncPriceFormat($pay['member_available_rcb']);
        $pay['payment_list'] = $payment_list ? array_values($payment_list) : array();
        output_data(array('pay_info' => $pay));
    }
    /**
     * AJAX验证支付密码
     */
    public function check_pd_pwdOp()
    {
        if (empty($_POST['password'])) {
            output_error('支付密码格式不正确');
        }
        $buyer_info = model('member')->getMemberInfoByID($this->member_info['member_id'], 'member_paypwd');
        if ($buyer_info['member_paypwd'] != '') {
            if ($buyer_info['member_paypwd'] === md5($_POST['password'])) {
                output_data('1');
            }
        }
        output_error('支付密码验证失败');
    }
    /**
     * F码验证
     */
    public function check_fcodeOp()
    {
        $goods_commonid = intval($_POST['goods_commonid']);
        if ($goods_commonid <= 0) {
            output_error('商品ID格式不正确');
        }
        if ($_POST['fcode'] == '') {
            output_error('F码格式不正确');
        }
        $result = logic('buy')->checkFcode($goods_commonid, $_POST['fcode']);
        if ($result['state']) {
            output_data('1');
        } else {
            output_error('F码验证抢购');
        }
    }
}