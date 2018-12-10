<?php
/**
 * 交易管理
 *
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class order extends SystemControl
{
    /**
     * 每次导出订单数量
     * @var int
     */
    const EXPORT_SIZE = 1000;
    public function __construct()
    {
        parent::__construct();
        core\language::read('trade');
    }
    public function indexOp()
    {
        $model_order = model('order');
        $condition = array();
        if (!empty($_GET['order_sn'])) {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if (!empty($_GET['store_name'])) {
            $condition['store_name'] = $_GET['store_name'];
        }
        if (isset($_GET['order_state']) && in_array($_GET['order_state'], array('0', '10', '20', '30', '40'))) {
            $condition['order_state'] = $_GET['order_state'];
        }
        if (!empty($_GET['payment_code'])) {
            $condition['payment_code'] = $_GET['payment_code'];
        }
        if (!empty($_GET['buyer_name'])) {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
        $if_start_time = preg_match('/^20\\d{2}-\\d{2}-\\d{2}$/', isset($_GET['query_start_time']) ? $_GET['query_start_time'] : '');
        $if_end_time = preg_match('/^20\\d{2}-\\d{2}-\\d{2}$/', isset($_GET['query_end_time']) ? $_GET['query_end_time'] : '');
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time', array($start_unixtime, $end_unixtime));
        }
        $order_list = $model_order->getOrderList($condition, 30);
        foreach ($order_list as $order_id => $order_info) {
            //显示取消订单
            $order_list[$order_id]['if_cancel'] = $model_order->getOrderOperateState('system_cancel', $order_info);
            //显示收到货款
            $order_list[$order_id]['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay', $order_info);
        }
        //显示支付接口列表(搜索)
        $payment_list = model('payment')->getPaymentOpenList();
        core\tpl::output('payment_list', $payment_list);
        core\tpl::output('order_list', $order_list);
        core\tpl::output('show_page', $model_order->showpage());
        core\tpl::showpage('order.index');
    }
    /**
     * 平台订单状态操作
     *
     */
    public function change_stateOp()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if ($order_id <= 0) {
            error(lang('miss_order_number'), $_POST['ref_url']);
        }
        $model_order = model('order');
        //获取订单详细
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getOrderInfo($condition);
        if (isset($_GET['state_type']) && $_GET['state_type'] == 'cancel') {
            $result = $this->_order_cancel($order_info);
        } elseif (isset($_GET['state_type']) && $_GET['state_type'] == 'receive_pay') {
            $result = $this->_order_receive_pay($order_info, $_POST);
        }
        if (!$result['state']) {
            error($result['msg'], isset($_POST['ref_url']) ? $_POST['ref_url'] : '');
        } else {
            success($result['msg'], isset($_POST['ref_url']) ? $_POST['ref_url'] : '');
        }
    }
    /**
     * 系统取消订单
     */
    private function _order_cancel($order_info)
    {
        $order_id = $order_info['order_id'];
        $model_order = model('order');
        $logic_order = logic('order');
        $if_allow = $model_order->getOrderOperateState('system_cancel', $order_info);
        if (!$if_allow) {
            return callback(false, '无权操作');
        }
        $result = $logic_order->changeOrderStateCancel($order_info, 'system', $this->admin_info['name']);
        if ($result['state']) {
            $this->log(lang('order_log_cancel') . ',' . lang('order_number') . ':' . $order_info['order_sn'], 1);
        }
        return $result;
    }
    /**
     * 系统收到货款
     * @throws Exception
     */
    private function _order_receive_pay($order_info, $post)
    {
        $order_id = $order_info['order_id'];
        $model_order = model('order');
        $logic_order = logic('order');
        $if_allow = $model_order->getOrderOperateState('system_receive_pay', $order_info);
        if (!$if_allow) {
            return callback(false, '无权操作');
        }
        if (!chksubmit()) {
            core\tpl::output('order_info', $order_info);
            //显示支付接口列表
            $payment_list = model('payment')->getMbPaymentOpenList();
            //去掉预存款和货到付款
            foreach ($payment_list as $key => $value) {
                if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
                    unset($payment_list[$key]);
                }
            }
            core\tpl::output('payment_list', $payment_list);
            core\tpl::showpage('order.receive_pay');
            exit;
        }
        $order_list = $model_order->getOrderList(array('pay_sn' => $order_info['pay_sn'], 'order_state' => ORDER_STATE_NEW));
        $result = $logic_order->changeOrderReceivePay($order_list, 'system', $this->admin_info['name'], $post);
        if ($result['state']) {
			
			/*分销处理*/
			logic('distributor')->deal_dis_public($order_list);
			logic('distributor')->deal_commission_state($order_list);
			//股东分红处理
			logic('distributor')->deal_team_commission($order_list);			
			$model_wechat = model('wechat');
			$weixin_config = $model_wechat->getInfoOne('weixin_wechat','');
			$disconfig = $model_wechat->getInfoOne('distributor_setting','','dis_bonus_name');
			$access_token = logic('weixin_token')->get_access_token($weixin_config);
			$flag = logic('weixin_message')->addorder($access_token, $disconfig ,$weixin_config, $order_info['buyer_name'], $order_info['order_amount'], $order_info['order_id'], 1);
			
            $this->log('将订单改为已收款状态,' . lang('order_number') . ':' . $order_info['order_sn'], 1);
        }
        return $result;
    }
    /**
     * 查看订单
     *
     */
    public function show_orderOp()
    {
        $order_id = intval($_GET['order_id']);
        if ($order_id <= 0) {
            error(lang('miss_order_number'));
        }
        $model_order = model('order');
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id), array('order_goods', 'order_common', 'store'));
        //订单变更日志
        $log_list = $model_order->getOrderLogList(array('order_id' => $order_info['order_id']));
        core\tpl::output('order_log', $log_list);
        //退款退货信息
        $model_refund = model('refund_return');
        $condition = array();
        $condition['order_id'] = $order_info['order_id'];
        $condition['seller_state'] = 2;
        $condition['admin_time'] = array('gt', 0);
        $return_list = $model_refund->getReturnList($condition);
        core\tpl::output('return_list', $return_list);
        //退款信息
        $refund_list = $model_refund->getRefundList($condition);
        core\tpl::output('refund_list', $refund_list);
        //卖家发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_common']['daddress_id']));
            core\tpl::output('daddress_info', $daddress_info);
        }
        core\tpl::output('order_info', $order_info);
        core\tpl::showpage('order.view');
    }
    /**
     * 导出
     *
     */
    public function export_step1Op()
    {
        $lang = core\language::getLangContent();
        $model_order = model('order');
        $condition = array();
        if (!empty($_GET['order_sn'])) {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if (!empty($_GET['store_name'])) {
            $condition['store_name'] = $_GET['store_name'];
        }
        if (isset($_GET['order_state']) && in_array($_GET['order_state'], array('0', '10', '20', '30', '40'))) {
            $condition['order_state'] = $_GET['order_state'];
        }
        if (!empty($_GET['payment_code'])) {
            $condition['payment_code'] = $_GET['payment_code'];
        }
        if (!empty($_GET['buyer_name'])) {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
        $if_start_time = preg_match('/^20\\d{2}-\\d{2}-\\d{2}$/', isset($_GET['query_start_time']) ? $_GET['query_start_time'] : '');
        $if_end_time = preg_match('/^20\\d{2}-\\d{2}-\\d{2}$/', isset($_GET['query_end_time']) ? $_GET['query_end_time'] : '');
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time', array($start_unixtime, $end_unixtime));
        }
        if (isset($_GET['curpage']) && !is_numeric($_GET['curpage'])) {
            $count = $model_order->getOrderCount($condition);
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
                core\tpl::output('murl', 'index.php?act=order&op=index');
                core\tpl::showpage('export.excel');
            } else {
                //如果数量小，直接下载
                $data = $model_order->getOrderList($condition, '', '*', 'order_id desc', self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        } else {
            //下载
            $limit1 = ((isset($_GET['curpage']) ? $_GET['curpage'] : 1) - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList($condition, '', '*', 'order_id desc', "{$limit1},{$limit2}");
            $this->createExcel($data);
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
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_no'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_store'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_buyer'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_xtimd'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_count'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_yfei'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_paytype'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_state'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_storeid'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_buyerid'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_bemail'));
        //data
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => 'NC' . $v['order_sn']);
            $tmp[] = array('data' => $v['store_name']);
            $tmp[] = array('data' => $v['buyer_name']);
            $tmp[] = array('data' => date('Y-m-d H:i:s', $v['add_time']));
            $tmp[] = array('format' => 'Number', 'data' => ncPriceFormat($v['order_amount']));
            $tmp[] = array('format' => 'Number', 'data' => ncPriceFormat($v['shipping_fee']));
            $tmp[] = array('data' => orderPaymentName($v['payment_code']));
            $tmp[] = array('data' => orderState($v));
            $tmp[] = array('data' => $v['store_id']);
            $tmp[] = array('data' => $v['buyer_id']);
            $tmp[] = array('data' => $v['buyer_email']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_od_order'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_od_order'), CHARSET) . (isset($_GET['curpage']) ? $_GET['curpage'] : 1) . '-' . date('Y-m-d-H', time()));
    }
}