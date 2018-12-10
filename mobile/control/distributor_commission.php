<?php
/**
 * 我的佣金记录
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');

class distributor_commission extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
		if (empty($this->member_info['is_distributor'])) {
            output_error('您还不是分销商', array('distributor' => '0'));
        }
	}

    /**
     * 我的佣金记录
     */
    public function indexOp() {
		$model_dis = model('distributor');

		//状态
		$_STATUS = array(
			'10'=>'未付款',
			'20'=>'已付款',
			'30'=>'已发货',
			'40'=>'已完成'
		);
		
		$lists = array();
		
		$total_money = 0;
		//获得佣金记录
		$where['member_id'] = $this->member_info['member_id'];
		$result = $model_dis->getInfoOne('distributor_goodsrecord_detail',$where, 'SUM(detail_bonus) as money');
		if(!empty($result['money'])){
			$total_money = $result['money'];
		}
		$record_list = $model_dis->getInfoList('distributor_goodsrecord_detail', $where, 20, 'detail_addtime desc');
		
		foreach($record_list as $key=>$value){
			$lists[] = array(
				'money'=>$value['detail_bonus'],
				'status'=>$_STATUS[$value['detail_status']],
				'record_status'=>$value['detail_status'],
				'desc'=>$value['detail_desc'],
				'addtime'=>date('Y-m-d H:i:s',$value['detail_addtime'])
			);
		}
		
		
		$page_count = $model_dis->gettotalpage();
	
        output_data(array('record_list' => $lists,'total_money'=>$total_money), mobile_page($page_count));
    }
	
}
