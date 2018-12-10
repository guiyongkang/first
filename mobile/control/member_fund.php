<?php
/**
 * 我的资金相关信息
 *
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class member_fund extends mobileMemberControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 预存款日志列表
     */
    public function predepositlogOp()
    {
        $model_predeposit = model('predeposit');
        $where = array();
        $where['lg_member_id'] = $this->member_info['member_id'];
        $where['lg_av_amount'] = array('neq', 0);
        $list = $model_predeposit->getPdLogList($where, $this->page, '*', 'lg_id desc');
        $page_count = $model_predeposit->gettotalpage();
        if ($list) {
            foreach ($list as $k => $v) {
                $v['lg_add_time_text'] = @date('Y-m-d H:i:s', $v['lg_add_time']);
                $list[$k] = $v;
            }
        }
        output_data(array('list' => $list), mobile_page($page_count));
    }
    /**
     * 充值卡余额变更日志
     */
    public function rcblogOp()
    {
        $model_rcb_log = model('rcb_log');
        $where = array();
        $where['member_id'] = $this->member_info['member_id'];
        $where['available_amount'] = array('neq', 0);
        $log_list = $model_rcb_log->getRechargeCardBalanceLogList($where, $this->page, '', 'id desc');
        $page_count = $model_rcb_log->gettotalpage();
        if ($log_list) {
            foreach ($log_list as $k => $v) {
                $v['add_time_text'] = @date('Y-m-d H:i:s', $v['add_time']);
                $log_list[$k] = $v;
            }
        }
        output_data(array('log_list' => $log_list), mobile_page($page_count));
    }
    /**
     * 充值明细
     */
    public function pdrechargelistOp()
    {
        $where = array();
        $where['pdr_member_id'] = $this->member_info['member_id'];
        $model_pd = model('predeposit');
        $list = $model_pd->getPdRechargeList($where, $this->page, '*', 'pdr_id desc');
        $page_count = $model_pd->gettotalpage();
        if ($list) {
            foreach ($list as $k => $v) {
                $v['pdr_add_time_text'] = @date('Y-m-d H:i:s', $v['pdr_add_time']);
                $v['pdr_payment_state_text'] = $v['pdr_payment_state'] == 1 ? '已支付' : '未支付';
                $list[$k] = $v;
            }
        }
        output_data(array('list' => $list), mobile_page($page_count));
    }
    /**
     * 提现记录
     */
    public function pdcashlistOp()
    {
        $where = array();
        $where['pdc_member_id'] = $this->member_info['member_id'];
        $model_pd = model('predeposit');
        $list = $model_pd->getPdCashList($where, $this->page, '*', 'pdc_id desc');
        $page_count = $model_pd->gettotalpage();
		$STATE = array('申请中','已支付','已驳回');
        if ($list) {
            foreach ($list as $k => $v) {
                $v['pdc_add_time_text'] = @date('Y-m-d H:i:s', $v['pdc_add_time']);
                $v['pdc_payment_time_text'] = @date('Y-m-d H:i:s', $v['pdc_payment_time']);
				$v['pdc_payment_state_text'] = $STATE[$v['pdc_payment_state']];
                $list[$k] = $v;
            }
        }
        output_data(array('list' => $list), mobile_page($page_count));
    }
	
	/**
     * 转账记录
     */
    public function pdzzlistOp()
    {
        $where = array();
        $where['from_member_id|to_member_id'] = $this->member_info['member_id'];
        $model_pd = model('predeposit');
        $list = $model_pd->getPdZzList($where, $this->page, '*', 'record_id desc');
        $page_count = $model_pd->gettotalpage();
        if ($list) {
            foreach ($list as $k => $v) {
                $v['adddate'] = @date('Y-m-d H:i:s', $v['add_time']);
				$v['amount'] = $v['to_member_id'] == $this->member_info['member_id'] ? $v['amount'] : -$v['amount'];
				$v['amount'] = str_replace('.00','',$v['amount']);
				$v['desc'] = $v['to_member_id'] == $this->member_info['member_id'] ? '会员['.$v['from_member_name'].']向你转账'.$v['amount'].'积分' : '你向会员['.$v['from_member_name'].']转账'.$v['amount'].'积分';
                $list[$k] = $v;
            }
        }
        output_data(array('list' => $list), mobile_page($page_count));
    }
	
	/**
	转账动作
	*/
	public function pdzzOp(){
		if(empty($_POST['userno'])){
			output_error('请输入收款会员ID');
		}
		if(!is_numeric($_POST['userno'])){
			output_error('收款会员ID格式不正确');
		}
		$userno = intval($_POST['userno']);
		if($userno<1){
			output_error('收款会员ID必须大于1');
		}
		
		if($userno == $this->member_info['member_id']){
			output_error('禁止自身转账');
		}
		
		$shoukuan = model('member')->getMemberInfoByID($userno, 'member_id,member_name');
		if(empty($shoukuan) || !$shoukuan){
			output_error('收款会员不存在');
		}
		
		if(empty($_POST['amount'])){
			output_error('请输入转账金额');
		}
		
		if(!is_numeric($_POST['amount'])){
			output_error('转账金额格式不正确');
		}
		$amount = number_format($_POST['amount'],2,'.','');
		if($amount<0.01){
			output_error('转账金额必须大于0.01');
		}
		
		if($this->member_info['available_predeposit']<$amount){
			output_error('您的余额不足');
		}
		
		$model = model('predeposit');
		$flag = true;
		$model->beginTransaction();
		
		$insert_data = array(
			'from_member_id'=>$this->member_info['member_id'],
			'from_member_name'=>$this->member_info['member_name'],
			'to_member_id'=>$userno,
			'to_member_name'=>$shoukuan['member_id'],
			'amount'=>$amount,
			'add_time'=>time(),
		);
		
		$result = $model->addPdZz($insert_data);
		$flag = $flag && $result;
		
		//扣除from_member_id预存款
		$yue_data = array(
			'amount'=>$amount,
			'member_id'=>$this->member_info['member_id'],
			'member_name'=>$this->member_info['member_name'],
			'desc'=>'向会员['.$shoukuan['member_name'].']转账'
		);
		$result = $model->changePd('pdzz_from',$yue_data);
		$flag = $flag && $result;
		
		//增加to_member_id预存款
		$yue_data = array(
			'amount'=>$amount,
			'member_id'=>$shoukuan['member_id'],
			'member_name'=>$shoukuan['member_name'],
			'desc'=>'会员['.$this->member_info['member_name'].']向我转账'
		);
		$result = $model->changePd('pdzz_to',$yue_data);
		$flag = $flag && $result;
		
		if($flag){
			$model->commit();
			output_data(array('message'=>'转账成功'));
		}else{
			$model_dis->rollback();
			output_error('转账成功');
		}
	}
	
    /**
     * 充值卡充值
     */
    public function rechargecard_addOp()
    {
        $param = $_POST;
        $rc_sn = trim($param['rc_sn']);
        //print_r $rc_sn;
        if (!$rc_sn) {
            output_error('请输入平台充值卡号11');
        }
        // if(!$this->check()){
        //	output_error('验证码错误');
        // }
        try {
            model('predeposit')->addRechargeCard($rc_sn, array('member_id' => $this->member_info['member_id'], 'member_name' => $this->member_info['member_name']));
            output_data('1');
        } catch (\Exception $e) {
            output_error($e->getMessage());
        }
    }
    /**
     * 预存款提现记录详细
     */
    public function pdcashinfoOp()
    {
        $param = $_GET;
        $pdc_id = intval($param["pdc_id"]);
        if ($pdc_id <= 0){
            output_error('参数错误');
        }
        $where = array();
        $where['pdc_member_id'] =  $this->member_info['member_id'];
        $where['pdc_id'] = $pdc_id;
        $info = model('predeposit')->getPdCashInfo($where);
        if (!$info){
            output_error('参数错误');
        }
		
		$STATE = array('申请中','已支付','已驳回');
        $info['pdc_add_time_text'] = $info['pdc_add_time']?@date('Y-m-d H:i:s',$info['pdc_add_time']):'';
        $info['pdc_payment_time_text'] = $info['pdc_payment_time']?@date('Y-m-d H:i:s',$info['pdc_payment_time']):'';
        $info['pdc_payment_state_text'] = $STATE[$info['pdc_payment_state']];
		$info['pdc_payment_return_text'] = $info['pdc_note'];
        output_data(array('info' => $info));
    }
    /**
     * 充值列表
     */
    public function indexOp()
    {
        $condition = array();
        $condition['pdr_member_id'] = $this->member_info['member_id'];
        if (!empty($_GET['pdr_sn'])) {
            $condition['pdr_sn'] = $_GET['pdr_sn'];
        }
        $model_pd = model('predeposit');
        $list = $model_pd->getPdRechargeList($condition, 20, '*', 'pdr_id desc');
        foreach ($list as $key => $value) {
            $list[$key]['pdr_add_time_text'] = date('Y-m-d H:i:s', $value['pdr_add_time']);
        }
        $page_count = $model_pd->gettotalpage();
        output_data(array('list' => $list), mobile_page($page_count));
    }
    /**
     * 我的积分 我的余额
     */
    public function my_assetOp()
    {
        $point = $this->member_info['member_points'];
        output_data(array('point' => $point));
    }
    protected function getMemberAndGradeInfo($is_return = false)
    {
        $member_info = array();
        //会员详情及会员级别处理
        if ($this->member_info['member_id']) {
            $model_member = model('member');
            $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
            if ($member_info) {
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
                $member_info = array_merge($member_info, $member_gradeinfo);
                $member_info['security_level'] = $model_member->getMemberSecurityLevel($member_info);
            }
        }
        if ($is_return == true) {
            //返回会员信息
            return $member_info;
        } else {
            //输出会员信息
            core\tpl::output('member_info', $member_info);
        }
    }
    /**
     * AJAX验证
     *
     */
    protected function check()
    {
        if (checkSeccode($_POST['nchash'], $_POST['captcha'])) {
            return true;
        } else {
            return false;
        }
    }
	/**
	 * 充值添加
	 */
	public function recharge_addOp()
	{
		$pdr_amount = abs(floatval($_POST['amount']));
		if ($pdr_amount <= 0) {
			output_error('充值金额为大于或者等于0.01的数字');
		}
        $model_pdr = model('predeposit');
        $data = array();
        $data['pdr_sn'] = $pay_sn = $model_pdr->makeSn();
        $data['pdr_member_id'] = $this->member_info['member_id'];
        $data['pdr_member_name'] = $this->member_info['member_name'];
        $data['pdr_amount'] = $pdr_amount;
        $data['pdr_add_time'] = TIMESTAMP;
        $insert = $model_pdr->addPdRecharge($data);
        if ($insert) {
            //转向到商城支付页面
			output_data($pay_sn);
        }
	}
	/**
	 * 预存款充值下单时支付页面
	 */
	public function pd_payOp() 
	{
		$pay_sn = $_POST['pay_sn'];
		if (!preg_match ( '/^\d{18}$/', $pay_sn)) {
			output_error('参数错误');
		}
		
		// 查询支付单信息
		$model_order = model('predeposit');
		$pd_info = $model_order->getPdRechargeInfo(array(
			'pdr_sn' => $pay_sn,
			'pdr_member_id' => $this->member_info['member_id']
		));
		if (empty($pd_info)) {
			output_error('该订单不存在');
		}
		if (intval($pd_info['pdr_payment_state'])) {
			output_error('您的订单已经支付，请勿重复支付');
		}
		// 显示支付接口列表
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
		$pay['pay_sn'] = $pay_sn;
		$pay['pay_amount'] = ncPriceFormat($pd_info['pdr_amount']);
		$pay['payment_list'] = $payment_list ? array_values($payment_list) : array();
        output_data(array('pay_info' => $pay));
	}
}