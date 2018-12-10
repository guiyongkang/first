<?php
/**
 * 支付方式
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class payment extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('payment');
    }
    /**
     * 支付方式
     */
    public function indexOp()
    {
        $model_payment = model('payment');
        $payment_list = $model_payment->getPaymentList(array('payment_code' => array('neq', 'predeposit')));
        core\tpl::output('payment_list', $payment_list);
        core\tpl::showpage('payment.list');
    }
    /**
     * 编辑
     */
    public function editOp()
    {
        $model_payment = model('payment');
        if (chksubmit()) {
            $payment_id = intval($_POST['payment_id']);
            $data = array();
            $data['payment_state'] = intval($_POST['payment_state']);
            $payment_config = '';
			$config_array = array();
			if(!empty($_POST['config_name'])){
				$config_array = explode(',', $_POST['config_name']);
			}
            //配置参数
            if (!empty($config_array) && is_array($config_array)) {
                $config_info = array();
                foreach ($config_array as $k) {
                    $config_info[$k] = trim($_POST[$k]);
                }
                $payment_config = serialize($config_info);
            }
            $data['payment_config'] = $payment_config;
            //支付接口配置信息
            $model_payment->editPayment($data, array('payment_id' => $payment_id));
            success(core\language::get('nc_common_save_succ'), 'index.php?act=payment&op=index');
        }
        $payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
        $payment = $model_payment->getPaymentInfo(array('payment_id' => $payment_id));
        if (!empty($payment['payment_config'])) {
            core\tpl::output('config_array', unserialize($payment['payment_config']));
        }
        core\tpl::output('payment', $payment);
        core\tpl::showpage('payment.edit');
    }
}