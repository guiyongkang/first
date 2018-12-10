<?php
/**
 * 手机支付方式
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class mb_payment extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function indexOp()
    {
        $this->payment_listOp();
    }
    public function payment_listOp()
    {
        $model_mb_payment = model('mb_payment');
        $mb_payment_list = $model_mb_payment->getMbPaymentList();
        core\tpl::output('mb_payment_list', $mb_payment_list);
        core\tpl::showpage('mb_payment.list');
    }
    /**
     * 编辑
     */
    public function payment_editOp()
    {
        $payment_id = intval($_GET["payment_id"]);
        $model_mb_payment = model('mb_payment');
        $mb_payment_info = $model_mb_payment->getMbPaymentInfo(array('payment_id' => $payment_id));
        core\tpl::output('payment', $mb_payment_info);
        core\tpl::showpage('mb_payment.edit');
    }
    /**
     * 编辑保存
     */
    public function payment_saveOp()
    {
		$model_mb_payment = model('mb_payment');
        $payment_id = intval($_POST["payment_id"]);
        $data = array();
        $data['payment_state'] = intval($_POST["payment_state"]);
        switch ($_POST['payment_code']) {
            case 'alipay':
                $payment_config = array('alipay_account' => $_POST['alipay_account'], 'alipay_key' => $_POST['alipay_key'], 'alipay_partner' => $_POST['alipay_partner']);
                break;
            case 'wxpay':
                $payment_config = array('wxpay_appid' => $_POST['wxpay_appid'], 'wxpay_appsecret' => $_POST['wxpay_appsecret'], 'wxpay_appkey' => $_POST['wxpay_appkey'], 'wxpay_partnerid' => $_POST['wxpay_partnerid'], 'wxpay_partnerkey' => $_POST['wxpay_partnerkey']);
                break;
            case 'wxpay_jsapi':
				$mb_payment_info = $model_mb_payment->getMbPaymentInfo(array('payment_id' => $payment_id));
				
				$_POST['apiclientcert'] = $mb_payment_info['payment_config']['apiclientcert'];
				$_POST['apiclientkey'] = $mb_payment_info['payment_config']['apiclientkey'];
				if (!empty($_FILES['apiclientcert']['name'])) {
					$upload = new lib\uploadfile();
					$upload->set('default_dir', ATTACH_COMMON);
					$upload->set('is_img', false);
					$result = $upload->upfile('apiclientcert');
					if ($result) {
						$_POST['apiclientcert'] = $upload->file_name;
					} else {
						error($upload->error);
					}
				}
				
				if (!empty($_FILES['apiclientkey']['name'])) {
					$upload = new lib\uploadfile();
					$upload->set('default_dir', ATTACH_COMMON);
					$upload->set('is_img', false);
					$result = $upload->upfile('apiclientkey');
					if ($result) {
						$_POST['apiclientkey'] = $upload->file_name;
					} else {
						error($upload->error);
					}
				}
                $payment_config = array('appId' => $_POST['appId'], 'appSecret' => $_POST['appSecret'], 'partnerId' => $_POST['partnerId'], 'apiKey' => $_POST['apiKey'],'apiclientkey'=>$_POST['apiclientkey'],'apiclientcert'=>$_POST['apiclientcert']);
                break;
            case 'alipay_native':
                $payment_config = array('alipay_account' => $_POST['alipay_account'], 'alipay_key' => $_POST['alipay_key'], 'alipay_partner' => $_POST['alipay_partner']);
                break;
            default:
                error(lang('param_error'));
        }
        $data['payment_config'] = $payment_config;
        
        $result = $model_mb_payment->editMbPayment($data, array('payment_id' => $payment_id));
        if ($result) {
            success(core\language::get('nc_common_save_succ'), urlAdmin('mb_payment', 'payment_list'));
        } else {
            error(core\language::get('nc_common_save_fail'), urlAdmin('mb_payment', 'payment_list'));
        }
    }
}