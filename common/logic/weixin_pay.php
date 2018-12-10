<?php
namespace common\logic;
use core;
use lib;
class weixin_pay{
	
	//佣金提现
	public function commission_withdraw($record){		
		$model_dis = model('distributor');
		
		$member_info = $model_dis->getInfoOne('member',array('member_id'=>$record['member_id']),'member_wxopenid');
		
		$mb_payment_info = model('mb_payment')->getMbPaymentInfo(array('payment_id' => 2));
		$tradeno = $mb_payment_info['payment_config']['partnerId'].date('YmdHis').rand(1000, 9999);
		
		$wxpay = new lib\wxSDk\ComPay();
		
		//公共设置项
		
		$wxpay->certfile(str_replace('http://'.$_SERVER['HTTP_HOST'],$_SERVER["DOCUMENT_ROOT"],UPLOAD_SITE_URL).'/'.ATTACH_COMMON.DS.$mb_payment_info['payment_config']['apiclientcert']);
		$wxpay->keyfile(str_replace('http://'.$_SERVER['HTTP_HOST'],$_SERVER["DOCUMENT_ROOT"],UPLOAD_SITE_URL).'/'.ATTACH_COMMON.DS.$mb_payment_info['payment_config']['apiclientkey']);
		$wxpay->setApiKey($mb_payment_info['payment_config']['apiKey']);
		$wxpay->setMchid($mb_payment_info['payment_config']['partnerId']);
		$wxpay->setMchAppid($mb_payment_info['payment_config']['appId']);
		$wxpay->setOpenid($member_info['member_wxopenid']);
		$wxpay->setPartnerTradeNo($tradeno);
		$wxpay->setTotalAmount($record['record_amount']*100);
		if($record['method_code'] == 'wxhongbao'){
			$wxpay->setSendName('红包提现');
			$wxpay->setWishing('红包提现');
			$wxpay->setActName('红包提现');
			$wxpay->setRemark('红包提现');
			$rsxml = $wxpay->RedBag();
		}else{
			$wxpay->setRemark('转帐提现');
			$rsxml = $wxpay->ComPay();
		}
		
		$response = simplexml_load_string($rsxml, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		$return_code = trim($response->return_code);
		
		$return_msg = trim($response->return_msg);
		
		if($return_code=='SUCCESS'){
			$result_code = trim($response->result_code);
			if($result_code=='SUCCESS'){
				//处理提现记录
				$res = array();
				$res['status'] = 1;
				$res['outtradeno'] = $tradeno;				
				if($record['method_code']=='wxhongbao'){
					$res['tradetime'] = time();
					$res['tradeno'] = trim($response->send_listid);					
				}else{					
					$res['tradetime'] = time();
					$res['tradeno'] = trim($response->payment_no);					
				}
				
				return $res;
			}else{
				return array("status"=>0,"msg"=>trim($response->err_code_des));
			}
		}else{
			return array("status"=>0,"msg"=>$return_msg);
		}
		
		return true;
	}
	
	//佣金提现
	public function pd_withdraw($record){		
		$model_dis = model('distributor');
		
		$member_info = $model_dis->getInfoOne('member',array('member_id'=>$record['member_id']),'member_wxopenid');
		
		$mb_payment_info = model('mb_payment')->getMbPaymentInfo(array('payment_id' => 2));
		$tradeno = $mb_payment_info['payment_config']['partnerId'].date('YmdHis').rand(1000, 9999);
		
		$wxpay = new lib\wxSDk\ComPay();
		
		//公共设置项
		
		$wxpay->certfile(str_replace('http://'.$_SERVER['HTTP_HOST'],$_SERVER["DOCUMENT_ROOT"],UPLOAD_SITE_URL).'/'.ATTACH_COMMON.DS.$mb_payment_info['payment_config']['apiclientcert']);
		$wxpay->keyfile(str_replace('http://'.$_SERVER['HTTP_HOST'],$_SERVER["DOCUMENT_ROOT"],UPLOAD_SITE_URL).'/'.ATTACH_COMMON.DS.$mb_payment_info['payment_config']['apiclientkey']);
		$wxpay->setApiKey($mb_payment_info['payment_config']['apiKey']);
		$wxpay->setMchid($mb_payment_info['payment_config']['partnerId']);
		$wxpay->setMchAppid($mb_payment_info['payment_config']['appId']);
		$wxpay->setOpenid($member_info['member_wxopenid']);
		$wxpay->setPartnerTradeNo($tradeno);
		$wxpay->setTotalAmount($record['record_amount']*100);
		if($record['method_code'] == 'wxhongbao'){
			$wxpay->setSendName('元提现');
			$wxpay->setWishing('元提现');
			$wxpay->setActName('元提现');
			$wxpay->setRemark('元提现');
			$rsxml = $wxpay->RedBag();
		}else{
			$wxpay->setRemark('元提现');
			$rsxml = $wxpay->ComPay();
		}
		
		$response = simplexml_load_string($rsxml, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		$return_code = trim($response->return_code);
		
		$return_msg = trim($response->return_msg);
		
		if($return_code=='SUCCESS'){
			$result_code = trim($response->result_code);
			if($result_code=='SUCCESS'){
				//处理提现记录
				$res = array();
				$res['status'] = 1;
				$res['outtradeno'] = $tradeno;				
				if($record['method_code']=='wxhongbao'){
					$res['tradetime'] = time();
					$res['tradeno'] = trim($response->send_listid);					
				}else{					
					$res['tradetime'] = time();
					$res['tradeno'] = trim($response->payment_no);					
				}
				
				return $res;
			}else{
				return array("status"=>0,"msg"=>trim($response->err_code_des));
			}
		}else{
			return array("status"=>0,"msg"=>$return_msg);
		}
		
		return true;
	}
	
}
?>