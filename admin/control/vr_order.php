<?php
/**
 * 虚拟订单管理
 *
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class vr_order extends SystemControl
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
        $model_vr_order = model('vr_order');
        $condition = array();
        if (isset($_GET['order_sn'])) {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if (isset($_GET['store_name'])) {
            $condition['store_name'] = $_GET['store_name'];
        }
        if (!empty($_GET['order_state'])) {
            $condition['order_state'] = intval($_GET['order_state']);
        }
        if (isset($_GET['payment_code'])) {
            $condition['payment_code'] = $_GET['payment_code'];
        }
        if (isset($_GET['buyer_name'])) {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
        $if_start_time = preg_match('/^20\\d{2}-\\d{2}-\\d{2}$/', isset($_GET['query_start_time']) ? $_GET['query_start_time'] : '');
        $if_end_time = preg_match('/^20\\d{2}-\\d{2}-\\d{2}$/', isset($_GET['query_end_time']) ? $_GET['query_end_time'] : '');
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time', array($start_unixtime, $end_unixtime));
        }
        $order_list = $model_vr_order->getOrderList($condition, 30);
        foreach ($order_list as $k => $order_info) {
            //显示取消订单
            $order_list[$k]['if_cancel'] = $model_vr_order->getOrderOperateState('system_cancel', $order_info);
            //显示收到货款
            $order_list[$k]['if_system_receive_pay'] = $model_vr_order->getOrderOperateState('system_receive_pay', $order_info);
        }
        //显示支付接口列表(搜索)
        $payment_list = model('payment')->getPaymentOpenList();
        core\tpl::output('payment_list', $payment_list);
        core\tpl::output('order_list', $order_list);
        core\tpl::output('show_page', $model_vr_order->showpage());
        core\tpl::showpage('vr_order.index');
    }
    /**
     * 平台订单状态操作
     *
     */
    public function change_stateOp()
    {
        $model_vr_order = model('vr_order');
        $condition = array();
        $condition['order_id'] = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $order_info = $model_vr_order->getOrderInfo($condition);
        if (isset($_GET['state_type']) && $_GET['state_type'] == 'cancel') {
            $result = $this->_order_cancel($order_info);
        } elseif (isset($_GET['state_type']) && $_GET['state_type'] == 'receive_pay') {
            $result = $this->_order_receive_pay($order_info, $_POST);
        }
        if (empty($result['state'])) {
            error($result['msg'], $_POST['ref_url']);
        } else {
            success($result['msg'], $_POST['ref_url']);
        }
    }
    /**
     * 系统取消订单
     * @param unknown $order_info
     */
    private function _order_cancel($order_info)
    {
        $model_vr_order = model('vr_order');
        $logic_vr_order = logic('vr_order');
        $if_allow = $model_vr_order->getOrderOperateState('system_cancel', $order_info);
        if (!$if_allow) {
            return callback(false, '无权操作');
        }
        $this->log('关闭了虚拟订单,' . lang('order_number') . ':' . $order_info['order_sn'], 1);
        return $logic_vr_order->changeOrderStateCancel($order_info, 'store', '管理员关闭虚拟订单');
    }
    /**
     * 系统收到货款
     * @param unknown $order_info
     * @throws Exception
     */
    private function _order_receive_pay($order_info, $post)
    {
        $model_vr_order = model('vr_order');
        $logic_vr_order = logic('vr_order');
        $if_allow = $model_vr_order->getOrderOperateState('system_receive_pay', $order_info);
        if (!$if_allow) {
            return callback(false, '无权操作');
        }
        if (!chksubmit()) {
            core\tpl::output('order_info', $order_info);
            //显示支付接口
            $payment_list = model('payment')->getPaymentOpenList();
            //去掉预存款和货到付款
            foreach ($payment_list as $key => $value) {
                if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
                    unset($payment_list[$key]);
                }
            }
            core\tpl::output('payment_list', $payment_list);
            core\tpl::showpage('order.receive_pay');
            exit;
        } else {
            $this->log('将虚拟订单改为已收款状态,' . lang('order_number') . ':' . $order_info['order_sn'], 1);
			
			$result = $logic_vr_order->changeOrderStatePay($order_info, 'system', $post);
			if($result){
				logic('distributor')->deal_dis_public(array(),$order_info);
				logic('distributor')->deal_commission_state(array(),$order_info);
				//股东分红处理
				logic('distributor')->deal_team_commission(array(),$order_info);
				$model_wechat = model('wechat');
				$weixin_config = $model_wechat->getInfoOne('weixin_wechat','');
				$disconfig = $model_wechat->getInfoOne('distributor_setting','','dis_bonus_name');
				$access_token = logic('weixin_token')->get_access_token($weixin_config);
				$flag = logic('weixin_message')->addorder($access_token, $disconfig ,$weixin_config, $order_info['buyer_name'], $order_info['order_amount'], $order_info['order_id'], 1);
			}
            return $result;
        }
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
        $model_vr_order = model('vr_order');
        $order_info = $model_vr_order->getOrderInfo(array('order_id' => $order_id));
        if (empty($order_info)) {
            error('订单不存在');
        }
        //取兑换码列表
        $vr_code_list = $model_vr_order->getOrderCodeList(array('order_id' => $order_info['order_id']));
        $order_info['extend_vr_order_code'] = $vr_code_list;
        //显示取消订单
        $order_info['if_cancel'] = $model_vr_order->getOrderOperateState('buyer_cancel', $order_info);
        //显示订单进行步骤
        $order_info['step_list'] = $model_vr_order->getOrderStep($order_info);
        //显示系统自动取消订单日期
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            //$order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_DAY * 24 * 3600;
            $order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_DAY + 3 * 24 * 3600;
        }
        core\tpl::output('order_info', $order_info);
        core\tpl::showpage('vr_order.view');
    }
    /**
     * 导出
     *
     */
    public function export_step1Op()
    {
        $lang = core\language::getLangContent();
        $model_vr_order = model('vr_order');
        $condition = array();
        if (isset($_GET['order_sn'])) {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if (isset($_GET['store_name'])) {
            $condition['store_name'] = $_GET['store_name'];
        }
        if (isset($_GET['order_state']) && in_array($_GET['order_state'], array('0', '10', '20', '30', '40'))) {
            $condition['order_state'] = $_GET['order_state'];
        }
        if (isset($_GET['payment_code'])) {
            $condition['payment_code'] = $_GET['payment_code'];
        }
        if (isset($_GET['buyer_name'])) {
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
            $count = $model_vr_order->getOrderCount($condition);
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
                core\tpl::output('murl', 'index.php?act=vr_order&op=index');
                core\tpl::showpage('export.excel');
            } else {
                //如果数量小，直接下载
                $data = $model_vr_order->getOrderList($condition, '', '*', 'order_id desc', self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        } else {
            //下载
            $limit1 = ((isset($_GET['curpage']) ? $_GET['curpage'] : 1) - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_vr_order->getOrderList($condition, '', '*', 'order_id desc', "{$limit1},{$limit2}");
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
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_paytype'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_state'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_storeid'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_od_buyerid'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '接收手机');
        //data
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => 'NC' . $v['order_sn']);
            $tmp[] = array('data' => $v['store_name']);
            $tmp[] = array('data' => $v['buyer_name']);
            $tmp[] = array('data' => date('Y-m-d H:i:s', $v['add_time']));
            $tmp[] = array('format' => 'Number', 'data' => ncPriceFormat($v['order_amount']));
            $tmp[] = array('data' => orderPaymentName($v['payment_code']));
            $tmp[] = array('data' => $v['state_desc']);
            $tmp[] = array('data' => $v['store_id']);
            $tmp[] = array('data' => $v['buyer_id']);
            $tmp[] = array('data' => $v['buyer_phone']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_od_order'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_od_order'), CHARSET) . (isset($_GET['curpage']) ? $_GET['curpage'] : '1') . '-' . date('Y-m-d-H', time()));
    }
}