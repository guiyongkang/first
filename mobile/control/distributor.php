<?php
/**
 * 分销中心逻辑
 **/
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class distributor extends mobileMemberControl
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 增加用户分销商身份或更新分销商
     */
    public function deal_distributorOp()
    {
		$model_dis = model('distributor');
		$costone = 0;
		$condition['buyer_id'] = $this->member_info['member_id'];
		$condition['order_state'] = array('gt',10);
		$result = $model_dis->getInfoOne('order',$condition, 'SUM(order_amount) as amount');		
		$costall = empty($result['amount']) ? 0 : $result['amount'];
		unset($result);
		$result = $model_dis->getInfoOne('vr_order',$condition, 'SUM(order_amount) as amount');
		$costall = empty($result['amount']) ? $costall : ($costall+$result['amount']);
		unset($result);		
		$f = logic('distributor')->deal_distributor($this->member_info['member_id'],array('register'=>0,'costone'=>$costone, 'costall'=>$costall,'commonids'=>array()));
		
		/*
		if($this->member_info['is_distributor']==1 && $this->member_info['inviter_id']>0){
			//公排分区升级
			logic('distributor')->public_update($this->member_info['inviter_id']);
		}*/
        output_data('1');
    }
}