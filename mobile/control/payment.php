<?php
/**
 * 支付回调
 *
 */
namespace mobile\control;
use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class payment extends mobileHomeControl
{
    private $payment_code;
    public function __construct()
    {
        parent::__construct();
        $this->payment_code = $_GET['payment_code'];
    }
    /**
     * 支付回调
     */
    public function returnOp()
    {
        unset($_GET['act']);
        unset($_GET['op']);
        unset($_GET['payment_code']);
        $payment_api = $this->_get_payment_api();
        $payment_config = $this->_get_payment_config();
        $callback_info = $payment_api->getReturnInfo($payment_config);
        if ($callback_info) {
            //验证成功
            $result = $this->_update_order($callback_info['out_trade_no'], $callback_info['trade_no']);
            if ($result['state']) {
                core\tpl::output('result', 'success');
                core\tpl::output('message', '支付成功');
            } else {
                core\tpl::output('result', 'fail');
                core\tpl::output('message', '支付失败');
            }
        } else {
            //验证失败
            core\tpl::output('result', 'fail');
            core\tpl::output('message', '支付失败');
        }
        core\tpl::showpage('payment_message');
    }
    /**
     * 支付提醒
     */
    public function notifyOp()
    {
        // wxpay_jsapi
        if ($this->payment_code == 'wxpay_jsapi') {
            $api = $this->_get_payment_api();
            $params = $this->_get_payment_config();
            $api->setConfigs($params);
            list($result, $output) = $api->notify();
            if ($result) {
                $internalSn = $result['out_trade_no'] . '_' . $result['attach'];
                $externalSn = $result['transaction_id'];
                $updateSuccess = $this->_update_order($internalSn, $externalSn);
                if (!$updateSuccess) {
                    // @todo
                    // 直接退出 等待下次通知
                    exit;
                }
            }
            echo $output;
            exit;
        }
        // 恢复框架编码的post值
        $_POST['notify_data'] = html_entity_decode($_POST['notify_data']);
        $payment_api = $this->_get_payment_api();
        $payment_config = $this->_get_payment_config();
        $callback_info = $payment_api->getNotifyInfo($payment_config);
        if ($callback_info) {
            //验证成功
            $result = $this->_update_order($callback_info['out_trade_no'], $callback_info['trade_no']);
            if ($result['state']) {
                echo 'success';
                die;
            }
        }
        //验证失败
        echo 'fail';
        die;
    }
    /**
     * 获取支付接口实例
     */
    private function _get_payment_api()
    {
        $inc_file = BASE_PATH . DS . 'api' . DS . 'payment' . DS . $this->payment_code . DS . $this->payment_code . '.php';
        if (is_file($inc_file)) {
            require $inc_file;
        }
        $payment_api = new $this->payment_code();
        return $payment_api;
    }
    /**
     * 获取支付接口信息
     */
    private function _get_payment_config()
    {
        $model_mb_payment = model('mb_payment');
        //读取接口配置信息
        $condition = array();
        if ($this->payment_code == 'wxpay3') {
            $condition['payment_code'] = 'wxpay';
        } else {
            $condition['payment_code'] = $this->payment_code;
        }
        $payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);
        return $payment_info['payment_config'];
    }
    /**
     * 更新订单状态
     */
    private function _update_order($out_trade_no, $trade_no)
    {
        $model_order = model('order');
        $logic_payment = logic('payment');
        $tmp = explode('_', $out_trade_no);
        $out_trade_no = $tmp[0];
		$order_type = $tmp[1];
        // wxpay_jsapi
        $paymentCode = $this->payment_code;
        if ($paymentCode == 'wxpay_jsapi') {
            $paymentCode = 'wx_jsapi';
        } elseif ($paymentCode == 'wxpay3') {
            $paymentCode = 'wxpay';
        }
        if ($order_type == 'r') {
            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (intval($result['data']['api_pay_state'])) {
                return array('state' => true);
            }
            $order_list = $result['data']['order_list'];
            $result = $logic_payment->updateRealOrder($out_trade_no, $paymentCode, $order_list, $trade_no);
            $api_pay_amount = 0;
            if (!empty($order_list)) {
                foreach ($order_list as $order_info) {
                    $api_pay_amount += $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
                }
            }
			if ($result['state']) {
				//处理分销商相关
				logic('distributor')->deal_dis_public($order_list);
				logic('distributor')->deal_commission_state($order_list);
				//股东分红处理
				logic('distributor')->deal_team_commission($order_list);				
				$model_wechat = model('wechat');
				$weixin_config = $model_wechat->getInfoOne('weixin_wechat','');
				$disconfig = $model_wechat->getInfoOne('distributor_setting','','dis_bonus_name');
				$access_token = logic('weixin_token')->get_access_token($weixin_config);
				foreach($order_list as $orderinfo){
					$flag = logic('weixin_message')->addorder($access_token, $disconfig ,$weixin_config, $orderinfo['buyer_name'], $orderinfo['order_amount'], $orderinfo['order_id'], 1);
				}
			}
			
            $log_buyer_id = $order_list[0]['buyer_id'];
            $log_buyer_name = $order_list[0]['buyer_name'];
            $log_desc = '实物订单使用' . orderPaymentName($paymentCode) . '成功支付，支付单号：' . $out_trade_no;
        } elseif ($order_type == 'v') {
            $result = $logic_payment->getVrOrderInfo($out_trade_no);
            $order_info = $result['data'];
            if (!in_array($result['data']['order_state'], array(ORDER_STATE_NEW, ORDER_STATE_CANCEL))) {
                return array('state' => true);
            }
            $result = $logic_payment->updateVrOrder($out_trade_no, $paymentCode, $result['data'], $trade_no);
            $api_pay_amount = $order_info['order_amount'] - $order_info['pd_amount'] - $order_info['rcb_amount'];
			if ($result['state']) {
				//处理分销商相关
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
            $log_buyer_id = $order_info['buyer_id'];
            $log_buyer_name = $order_info['buyer_name'];
            $log_desc = '虚拟订单使用' . orderPaymentName($paymentCode) . '成功支付，支付单号：' . $out_trade_no;
        }else if($order_type == 'p'){
			$result = $logic_payment->getPdOrderInfo($out_trade_no);
			$order_info = $result['data'];
            if (intval($order_info['pdr_payment_state'])) {
                return array('state' => true);
            }
			$payment_info = array(
			    'payment_code' => $paymentCode,
				'payment_name' => orderPaymentName($paymentCode)
			);
            $result = $logic_payment->updatePdOrder($out_trade_no, $trade_no, $payment_info, $order_info);
            $api_pay_amount = $order_info['api_pay_amount'];
            $log_buyer_id = $order_info['pdr_member_id'];
            $log_buyer_name = $order_info['pdr_member_name'];
            $log_desc = '余额充值使用' . orderPaymentName($paymentCode) . '成功支付，支付单号：' . $out_trade_no;
		}
        if ($result['state']) {
            //记录消费日志
            lib\queue::push('addConsume', array('member_id' => $log_buyer_id, 'member_name' => $log_buyer_name, 'consume_amount' => ncPriceFormat($api_pay_amount), 'consume_time' => TIMESTAMP, 'consume_remark' => $log_desc));
        }
        return $result;
    }
}