<?php
/**
 * 我的提现方式
 *
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class pd_tixian extends mobileMemberControl
{
    public function __construct()
    {
        parent::__construct();
    }
    
	/**
     * 后台设置提现方式列表
     */
    public function withdraw_enabledOp()
    {
        $model_dis = model('distributor');
		$withdraw_list = array();
        
		$my_method = array();
		$result_temp = $model_dis->getInfoList('withdraw_method_member',array('member_id'=>$this->member_info['member_id']), 0, 'method_id asc','method_code');
		if(!empty($result_temp)){
			foreach($result_temp as $temp){
				$my_method[] = $temp['method_code'];
			}
		}
		
		$result = $model_dis->getInfoList('withdraw_method',array('method_status'=>1), 0, 'method_id asc');
		foreach($result as $value){
			if(in_array($value['method_code'],array('wxhongbao','wxzhuanzhang','yue')) && in_array($value['method_code'],$my_method)){
				continue;
			}elseif($value['method_code']=='yue'){
				continue;
			}else{
				$withdraw_list[] = array(
					'code'=>$value['method_code'],
					'name'=>$value['method_name']
				);
			}
		}
        output_data(array('withdraw_list' => $withdraw_list));
    }
    
    /**
     * 新增提现方式
     */
    public function method_addOp()
    {
		if(empty($_POST['code'])){
			output_error('非法提交');
		}
		
		if(!in_array($_POST['code'],array('wxhongbao','wxzhuanzhang','yue'))){
			if(empty($_POST['name']) || empty($_POST['no'])){
				output_error('请填写姓名和账号');
			}
		}
		
        $model_dis = model('distributor');
        $method_info = $model_dis->getInfoOne('withdraw_method',array('method_code'=>$_POST['code']), 'method_name');
		if(empty($method_info)){
			output_error('请填写姓名和账号');
		}
		
		if($_POST['is_default']==1){
			$flag = $model_dis->editInfo('withdraw_method_member',array('is_default'=>0), array('member_id'=>$this->member_info['member_id']));
		}
		
		$Data = array(
			'member_id'=>$this->member_info['member_id'],
			'method_code'=>$_POST['code'],
			'method_title'=>$method_info['method_name'],
			'method_name'=>$_POST['name'],
			'method_no'=>$_POST['no'],
			'is_default'=>$_POST['is_default']
		);
        $result = $model_dis->addInfo('withdraw_method_member',$Data);
        if ($result) {
            output_data(array('method_id' => $result));
        } else {
            output_error('保存失败');
        }
    }
    
	//获得会员可用的提现方式
    public function method_listOp()
    {
        $model_dis = model('distributor');
		$enabled_list = array();
		$result = $model_dis->getInfoList('withdraw_method',array('method_status'=>1), 0, 'method_id asc');
		foreach($result as $r){
			$enabled_list[] = $r['method_code'];
		}
		
		$method_list = array();
		$condition['member_id'] = $this->member_info['member_id'];
		$condition['method_code'] = array('in',$enabled_list);
        $result = $model_dis->getInfoList('withdraw_method_member',$condition, 0, 'method_id desc');
		foreach($result as $value){
			if($value['method_code'] == 'yue'){
			}
			$method_list[] = array(
				'meid'=>$value['method_id'],
				'title'=>$value['method_title'],
				'no'=>$value['method_no'],
				'name'=>$value['method_name'],
				'is_default'=>$value['is_default']
			);
		}
        output_data(array('method_list' => $method_list));
    }
	
	/**
     * 提现方式校验
     */
    public function method_checkOp()
    {
		$model_dis = model('distributor');
		if(!empty($_POST['meid'])){
			$method_id = intval($_POST['meid']);
			$method_info = $model_dis->getInfoOne('withdraw_method_member',array('method_id'=>$method_id,'member_id'=>$this->member_info['member_id']));
			if (empty($method_info)) {
				output_error('提现方式不存在');
			}
			
			if($method_info['method_code']=='yue'){
				output_error('提现方式无效');
			}
		}else{
			$method_info = $model_dis->getInfoOne('withdraw_method_member',array('is_default'=>1,'member_id'=>$this->member_info['member_id'],'method_code'=>array('neq','yue')));
			if (empty($method_info)) {
				$method_info = $model_dis->getInfoOne('withdraw_method_member',array('member_id'=>$this->member_info['member_id'],'method_code'=>array('neq','yue')),'*','method_id desc');
				if(empty($method_info)){
					output_data(array('method_info' => array()));
				}
			}
		}
		
        if (empty($method_info)) {
			output_error('提现方式不存在');
        } else {
			$result = $model_dis->getInfoOne('withdraw_method',array('method_code'=>$method_info['method_code']));
			$desc = '';
			if($result['method_min']>0){
				$desc .= '<p>最小提现额度 '.str_replace('.00','',$result['method_min']).' 积分；</p>';
			}
			if($result['method_max']>0){
				$desc .= '<p>最大提现额度 '.str_replace('.00','',$result['method_max']).' 积分；</p>';
			}
			if($result['method_fee']>0){
				$desc .= '<p>系统自动扣除提现金额的 '.str_replace('.00','',$result['method_fee']).'% 手续费；</p>';
			}
			$data = array(
				'title'=>$method_info['method_title'],
				'meid'=>$method_info['method_id'],
				'name'=>$method_info['method_name'] ? $method_info['method_name'] : $method_info['method_title'],
				'no'=>$method_info['method_no'],
				'desc'=>$desc
			);
            output_data(array('method_info' => $data));
        }
    }
	
	public function get_enabled_moneyOp(){
		$model_dis = model('distributor');
		$result = $model_dis->getInfoOne('distributor_setting','','dis_bonus_name');
		$money = $this->get_my_withdraw_money();
		
		$data = array(
			'name'=>empty($result['dis_bonus_name']) ? '佣金' : $result['dis_bonus_name'],
			'money'=>$money
		);
		output_data($data);
	}
	
	public function withdraw_applyOp(){
		if(empty($_POST['meid'])){
			output_data(array('result'=>0,'errorinfo'=>'请选择提现方式'));
			exit;
		}
		$method_id = intval($_POST['meid']);
		
		if(empty($_POST['money'])){
			output_data(array('result'=>0,'errorinfo'=>'请输入提现金额'));
			exit;
		}
		//本次提现总金额
		$amount = number_format($_POST['money'],2,'.','');
		if($amount<0.01){
			output_data(array('result'=>0,'errorinfo'=>'输入提现金额格式不对'));
			exit;
		}
		//会员当前可提现佣金
		$my_money = $this->get_my_withdraw_money();
		
		if($my_money<$amount){
			output_data(array('result'=>0,'errorinfo'=>'提现金额不得超出积分余额'));
			exit;
		}
		
		$model_dis = model('distributor');
		
		//获得提现方式详细信息
		$condition = array(
			'member_id'=>$this->member_info['member_id'],
			'method_id'=>$method_id
		);
		$user_method = $model_dis->getInfoOne('withdraw_method_member',$condition,'*');
		if(empty($user_method)){
			output_data(array('result'=>0,'errorinfo'=>'请选择有效的提现方式'));
			exit;
		}
		unset($condition);
		//获得后台此提现方式设置信息
		$condition = array(
			'method_code'=>$user_method['method_code']
		);
		$admin_method = $model_dis->getInfoOne('withdraw_method',$condition,'*');
		if(empty($admin_method)){
			output_data(array('result'=>0,'errorinfo'=>'请选择有效的提现方式'));
			exit;
		}
		
		//最小值限制
		if($admin_method['method_min']>0){
			if($admin_method['method_min']>$amount){
				output_data(array('result'=>0,'errorinfo'=>'最小提现金额为'.$admin_method['method_min'].'积分'));
				exit;
			}
		}
		
		//最大值限制
		if($admin_method['method_max']>0){
			if($admin_method['method_max']<$amount){
				output_data(array('result'=>0,'errorinfo'=>'最大提现金额为'.$admin_method['method_max'].'积分'));
				exit;
			}
		}
		
		//获得手续费
		$fee = 0;
		if($admin_method['method_fee']>0){
			$fee = $amount * $admin_method['method_fee'] * 0.01;
			$fee = number_format($fee,2,'.','');
		}
		
		//第三方需转账金额
		$avabled = $amount - $fee;
		
		if($user_method['method_code']== 'wxhongbao' && ($avabled<1 || $avabled>200)){
			output_data(array('result'=>0,'errorinfo'=>'微信红包提现金额需在1-200积分之间'));
			exit;
		}
		
		if($user_method['method_code']== 'wxhongbao' && $avabled<1){
			output_data(array('result'=>0,'errorinfo'=>'微信转账提现金额需大于1积分'));
			exit;
		}
		//是否需要审核
		$status = $admin_method['method_check'];
		$errorinfo = '提现失败';
		$flag = true;
		$model_dis->beginTransaction();
		
		//插入提现记录
		$pd_sn = mt_rand(10, 99) . sprintf('%010d', time() - 946656000) . sprintf('%03d', (double) microtime() * 1000) . sprintf('%03d', $this->member_info['member_id'] % 1000);
		$record_data = array(
			'pdc_sn'=> $pd_sn,
			'pdc_member_id'=>$this->member_info['member_id'],
			'pdc_member_name'=>$this->member_info['member_name'],
			'pdc_amount'=>$amount,
			'pdc_fee'=>$fee,
			'pdc_avabled'=>$avabled,
			'pdc_bank_code'=>$user_method['method_code'],
			'pdc_bank_name'=>$user_method['method_title'],
			'pdc_bank_user'=>$user_method['method_name'],
			'pdc_bank_no'=>$user_method['method_no'],
			'pdc_add_time'=>time(),
			'pdc_payment_state'=>0
		);
		
		$result = $model_dis->addInfo('pd_cash',$record_data);
		$record_id = $result;
		
		//冻结预存款
		$yue_data = array(
			'amount'=>$amount,
			'order_sn'=>$pd_sn,
			'member_id'=>$this->member_info['member_id'],
			'member_name'=>$this->member_info['member_name']
		);
		$result = model('predeposit')->changePd('cash_apply',$yue_data);
				
		$flag = $flag && $result;
		
		//微信红包处理
		if(($user_method['method_code'] == 'wxhongbao' || $user_method['method_code'] == 'wxzhuanzhang') && $status==0){
			$record_data['record_id'] = $record_id;
			$record_data['record_amount'] = $avabled;
			$record_data['method_code'] = $user_method['method_code'];
			$record_data['member_id'] = $this->member_info['member_id'];
			$response = logic('weixin_pay')->pd_withdraw($record_data);
			if($response['status']==1){//发送成功
				//处理用户预存款
				$yue_data = array(
					'amount'=>$amount,
					'order_sn'=>$pd_sn,
					'member_id'=>$this->member_info['member_id'],
					'member_name'=>$this->member_info['member_name']
				);
				$result = model('predeposit')->changePd('cash_pay',$yue_data);
				
				$record_data1 = array(
					"pdc_payment_state"=>1,
					'pdc_payment_admin'=>'system',
					"pdc_payment_outtradeno"=>$response['outtradeno'],
					"pdc_payment_time"=>$response['tradetime'],
					"pdc_payment_tradeno"=>$response['tradeno'],
					"pdc_payment_tradetype"=>$user_method['method_code']
				);
				$result = $model_dis->editInfo('pd_cash',$record_data1,array('pdc_id'=>$record_id));
				$flag = $flag && $result;
				$message = '提现成功，请及时查收';
			}else{
				$result = false;
				$flag = $flag && $result;
				$errorinfo = $response['msg'];
			}
		}else{
			$message = '提现成功,等待管理员审核';
		}
		
		if($flag){
			$model_dis->commit();
			output_data(array('result'=>1,'message'=>$message));
		}else{
			$model_dis->rollback();
			output_data(array('result'=>0,'errorinfo'=>$errorinfo));
		}
	}
	
	
	//获取会员可提现预存款
	private function get_my_withdraw_money(){
		$model_dis = model('distributor');
		$result = $model_dis->getInfoOne('member',array('member_id'=>$this->member_info['member_id']),'available_predeposit');
		return $result['available_predeposit'];
	}
    
}