<?php
/**
 * 我的提现方式
 *
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class withdraw extends mobileMemberControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 提现方式列表
     */
    public function withdraw_methodOp()
    {
        $model_dis = model('distributor');
		$withdraw_list = array();
        $result = $model_dis->getInfoList('withdraw_method_member',array('member_id'=>$this->member_info['member_id']), 0, 'method_id desc');
		foreach($result as $value){
			$withdraw_list[] = array(
				'meid'=>$value['method_id'],
				'title'=>$value['method_title'],
				'no'=>$value['method_no'],
				'name'=>$value['method_name'],
				'is_default'=>$value['is_default'],
				'edit'=>in_array($value['method_code'],array('wxhongbao','wxzhuanzhang','yue')) ? 0 : 1
			);
		}
        output_data(array('withdraw_list' => $withdraw_list));
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
     * 提现方式详细信息
     */
    public function method_infoOp()
    {
        $method_id = intval($_POST['meid']);
        $model_dis = model('distributor');
        
       	$method_info = $model_dis->getInfoOne('withdraw_method_member',array('method_id'=>$method_id,'member_id'=>$this->member_info['member_id']));
        if (!empty($method_info)) {
			$data = array(
				'title'=>$method_info['method_title'],
				'name'=>$method_info['method_name'],
				'no'=>$method_info['method_no'],
				'is_default'=>$method_info['is_default'],
			);
            output_data(array('method_info' => $data));
        } else {
            output_error('提现方式不存在');
        }
    }
    /**
     * 删除地址
     */
    public function withdraw_delOp()
    {
		$model_dis = model('distributor');
        $method_id = intval($_POST['meid']);
        $method_info = $model_dis->getInfoOne('withdraw_method_member',array('method_id'=>$method_id,'member_id'=>$this->member_info['member_id']), 'is_default');
		$result = $model_dis->delInfo('withdraw_method_member',array('method_id'=>$method_id));
		if($result){
			if($method_info['is_default']==1){
				$method_first = $model_dis->getInfoOne('withdraw_method_member',array('member_id'=>$this->member_info['member_id']), '*', 'method_id asc');
				if(!empty($method_first)){
					$flag = $model_dis->editInfo('withdraw_method_member',array('is_default'=>1), array('method_id'=>$method_first['method_id']));
				}
			}
		}
        
        output_data('1');
    }
    /**
     * 新增地址
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
    /**
     * 编辑地址
     */
    public function method_editOp()
    {
        $method_id = intval($_POST['meid']);
        $model_dis = model('distributor');
        
       	$method_info = $model_dis->getInfoOne('withdraw_method_member',array('method_id'=>$method_id,'member_id'=>$this->member_info['member_id']));
		
        if (empty($method_info)) {
			output_error('提现方式不存在');
        } else {
			if($method_info['is_default']==0 && $_POST['is_default']==1){
				$flag = $model_dis->editInfo('withdraw_method_member',array('is_default'=>0), array('member_id'=>$this->member_info['member_id']));
			}
			
			if($method_info['is_default']==1 && $_POST['is_default']==0){
				$method_first = $model_dis->getInfoOne('withdraw_method_member',array('member_id'=>$this->member_info['member_id']), '*', 'method_id asc');
				if(!empty($method_first)){
					$flag = $model_dis->editInfo('withdraw_method_member',array('is_default'=>1), array('method_id'=>$method_first['method_id']));
				}
			}
            $data = array(
				'method_name'=>$_POST['name'],
				'method_no'=>$_POST['no'],
				'is_default'=>$_POST['is_default'],
			);
			$result = $model_dis->editInfo('withdraw_method_member',$data, array('member_id'=>$this->member_info['member_id'],'method_id'=>$method_id));			
            output_data(array('method_id' => $method_id));
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
		}else{
			$method_info = $model_dis->getInfoOne('withdraw_method_member',array('is_default'=>1,'member_id'=>$this->member_info['member_id']));
			if (empty($method_info)) {
				$method_info = $model_dis->getInfoOne('withdraw_method_member',array('member_id'=>$this->member_info['member_id']),'*','method_id desc');
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
			if($result['method_yue']>0){
				$desc .= '<p>系统自动将提现金额的 '.str_replace('.00','',$result['method_yue']).'% 转入余额；</p>';
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
		$total = number_format($_POST['money'],2,'.','');
		if($total<0.01){
			output_data(array('result'=>0,'errorinfo'=>'输入提现金额格式不对'));
			exit;
		}
		//会员当前可提现佣金
		$my_money = $this->get_my_withdraw_money();
		
		if($my_money<$total){
			output_data(array('result'=>0,'errorinfo'=>'提现金额不得超出可提现余额'));
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
			if($admin_method['method_min']>$total){
				output_data(array('result'=>0,'errorinfo'=>'最小提现金额为'.$admin_method['method_min'].'积分'));
				exit;
			}
		}
		
		//最大值限制
		if($admin_method['method_max']>0){
			if($admin_method['method_max']<$total){
				output_data(array('result'=>0,'errorinfo'=>'最大提现金额为'.$admin_method['method_max'].'积分'));
				exit;
			}
		}
		
		//获得手续费
		$fee = 0;
		if($admin_method['method_fee']>0){
			$fee = $total * $admin_method['method_fee'] * 0.01;
			$fee = number_format($fee,2,'.','');
		}
		
		//获得转入余额
		$yue = 0;
		if($admin_method['method_yue']>0){
			$yue = $total * $admin_method['method_yue'] * 0.01;
			$yue = number_format($yue,2,'.','');
		}
		
		//第三方需转账金额
		$amount = $total - $fee - $yue;
		
		if($user_method['method_code']== 'wxhongbao' && ($amount<1 || $amount>200)){
			output_data(array('result'=>0,'errorinfo'=>'微信红包提现金额需在1-200积分之间'));
			exit;
		}
		
		if($user_method['method_code']== 'wxhongbao' && $amount<1){
			output_data(array('result'=>0,'errorinfo'=>'微信转账提现金额需大于1积分'));
			exit;
		}
		//是否需要审核
		$is_check = $admin_method['method_check'];
		
		$flag = true;
		$model_dis->beginTransaction();
		
		//插入提现记录
		$record_data = array(
			'member_id'=>$this->member_info['member_id'],
			'method_code'=>$user_method['method_code'],
			'method_title'=>$user_method['method_title'],
			'method_name'=>$user_method['method_name'],
			'method_no'=>$user_method['method_no'],
			'record_total'=>$total,
			'record_fee'=>$fee,
			'record_yue'=>$yue,
			'record_amount'=>$amount,
			'record_addtime'=>time(),
			'record_status'=>0
		);
		
		$result = $model_dis->addInfo('withdraw_record',$record_data);
		$record_id = $result;
		$flag = $flag && $result;
		
		if($user_method['method_code']=='yue'){//直接转入余额
			$yue_data = array(
				'amount'=>$amount,
				'order_sn'=>$record_id,
				'member_id'=>$this->member_info['member_id'],
				'member_name'=>$this->member_info['member_name']
			);
			$result = model('predeposit')->changePd('commission_come',$yue_data);
			$flag = $flag && $result;
			
			//更新记录状态
			$result = $model_dis->editInfo('withdraw_record',array('record_status'=>1), array('record_id'=>$record_id));
			$flag = $flag && $result;
			$message = '提现成功，已转入余额';
		}else{
			
			//微信红包处理
			if(($user_method['method_code'] == 'wxhongbao' || $user_method['method_code'] == 'wxzhuanzhang') && $is_check==0){
				$record_data['record_id'] = $record_id;
				$response = logic('weixin_pay')->commission_withdraw($record_data);
				if($response['status']==1){//发送成功
					$record_data1 = array(
						"record_status"=>1,
						"record_outtradeno"=>$response['outtradeno'],
						"record_tradetime"=>$response['tradetime'],
						"record_tradeno"=>$response['tradeno'],
						"record_tradetype"=>$user_method['method_code']
					);
					$result = $model_dis->editInfo('withdraw_record',$record_data1,array('record_id'=>$record_id));
					$flag = $flag && $result;
					
					if($yue>0){//余额处理
						$yue_data = array(
							'amount'=>$yue,
							'order_sn'=>$record_id,
							'member_id'=>$this->member_info['member_id'],
							'member_name'=>$this->member_info['member_name']
						);
						$result = model('predeposit')->changePd('commission_come',$yue_data);
						$flag = $flag && $result;
					}
					
					$message = '提现成功，请及时查收';
				}else{
					$result = false;
					$flag = $flag && $result;
					$message = $response['msg'];
				}
			}else{
				$message = '提现成功,等待管理员审核';
			}
		}
		
		
		if($flag){
			$model_dis->commit();
			logic('distributor')->public_update($this->member_info['member_id']);
			output_data(array('result'=>1,'message'=>$message));
		}else{
			$model_dis->rollback();
			output_data(array('result'=>0,'errorinfo'=>'提现失败'));
		}
	}
	
	public function withdraw_recordOp(){
		$model_dis = model('distributor');

		//状态
		$_STATUS = array(
			'0'=>'待审核',
			'1'=>'已发放',
			'2'=>'已驳回'
		);
		
		$lists = array();
		
		//获得佣金记录
		$where['member_id'] = $this->member_info['member_id'];		
		$record_list = $model_dis->getInfoList('withdraw_record', $where, 20, 'record_addtime desc');
		
		foreach($record_list as $key=>$value){
			$lists[] = array(
				'status'=>$_STATUS[$value['record_status']],
				'record_status'=>$value['record_status'],
				'total'=>$value['record_total'],
				'fee'=>$value['record_fee'],
				'yue'=>$value['record_yue'],
				'note'=>$value['record_note'],
				'title'=>$value['method_title'],
				'name'=>$value['method_name'],
				'no'=>$value['method_no'],
				'amount'=>$value['record_amount'],
				'addtime'=>date('Y-m-d H:i:s',$value['record_addtime'])
			);
		}
		
		$page_count = $model_dis->gettotalpage();
	
        output_data(array('record_list' => $lists), mobile_page($page_count));
	}
	
	//获取会员可提现佣金
	private function get_my_withdraw_money(){
		$model_dis = model('distributor');
		
		$bonus = $withdraw = 0;
		//获得会员总得佣金（状态为已完成 40）	
		$condition = array(
			'member_id'=>$this->member_info['member_id'],
			'detail_status'=>20
		);	
		$result = $model_dis->getInfoOne('distributor_goodsrecord_detail',$condition,'SUM(detail_bonus) as money');
		$bonus = empty($result['money']) ? 0 : $result['money'];
		unset($result);
		unset($condition);
		
		//获得股东分红总额
		$condition = array(
			'member_id'=>$this->member_info['member_id']
		);	
		$result = $model_dis->getInfoOne('distributor_fenhong',$condition,'SUM(detail_bonus) as money');
		$bonus = empty($result['money']) ? $bonus : $bonus + $result['money'];
		unset($result);
		unset($condition);
		
		//获得可提现的公排分区列表
		$result = $model_dis->getInfoList('distributor_gp_area',array('is_withdraw'=>1),'','item_id asc','item_id');
		$public_area_id = array();
		foreach($result as $r){
			$public_area_id[] = $r['item_id'];
		}
		unset($result);
		if(!empty($public_area_id)){
			$condition['member_id'] = $this->member_info['member_id'];
			$condition['detail_status'] = 0;
			$condition['area_id'] = array('in',$public_area_id);
			$result = $model_dis->getInfoOne('distributor_gp_detail', $condition, 'SUM(detail_bonus) as money');
			$bonus = empty($result['money']) ? $bonus : ($bonus + $result['money']);
			unset($result);
			unset($condition);
		}
		
		//获得会员总提现额（状态为已申请或已执行 0,1）
		$condition = array(
			'member_id'=>$this->member_info['member_id'],
			'record_status'=>array('in',array(0,1))
		);
		$result = $model_dis->getInfoOne('withdraw_record',$condition,'SUM(record_total) as money');
		$withdraw = empty($result['money']) ? 0 : $result['money'];
		unset($result);
		unset($condition);
		
		return $withdraw>=$bonus ? 0 : number_format(($bonus-$withdraw),2,'.','');
	}
    
}