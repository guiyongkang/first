<?php
namespace lib\smsapi;
use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class yunpian_send
{
    const HOST = 'yunpian.com';
    private static final function __replyResult($jsonStr)
    {
        //header("Content-type: text/html; charset=utf-8");
        $result = json_decode($jsonStr);
        if ($result->code == 0) {
            $data['state'] = 'true';
            return ture;
        } else {
            $data['state'] = 'false';
            $data['msg'] = $result->msg;
            return false;
        }
    }
    public static final function sendSms($path, $apikey, $encoded_text, $mobile, $tpl_id = '', $encoded_tpl_value = '')
    {
        $client = new lib\httpclient(self::HOST);
        $client->setDebug(false);
        if (!$client->post($path, array('apikey' => $apikey, 'text' => $encoded_text, 'mobile' => $mobile, 'tpl_id' => $tpl_id, 'tpl_value' => $encoded_tpl_value))) {
            return '-10000';
        } else {
            return self::__replyResult($client->getContent());
        }
    }
	/**
	* 模板接口发短信
	* apikey 为云片分配的apikey
	* tpl_id 为模板id
	* tpl_value 为模板值
	* mobile 为接收短信的手机号
	*/
	public static function tpl_send_sms($tpl_id, $tpl_value, $mobile)
	{
		$path = '/v1/sms/tpl_send.json';
		return self::sendSms($path, core\config::get('mobile_key'), $mobile, $tpl_id, $tpl_value);
	}
	/**
	* 普通接口发短信
	* apikey 为云片分配的apikey
	* text 为短信内容
	* mobile 为接收短信的手机号
	*/
	public static function send_sms($content, $mobile)
	{
		$path = '/v1/sms/send.json';
		return self::sendSms($path, core\config::get('mobile_key'), str_replace(core\config::get('site_name'), core\config::get('mobile_signature'), $content), $mobile);
	}
}