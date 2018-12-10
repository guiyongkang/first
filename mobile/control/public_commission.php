<?php
/**
 * 我的红包记录
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');

class public_commission extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
		if (empty($this->member_info['is_distributor'])) {
            output_error('您还不是分销商', array('distributor' => '0'));
        }
		if(empty($_GET['area_id']) || !is_numeric($_GET['area_id']) || intval($_GET['area_id'])<=0){
			output_error('请勿非法提交', array('distributor' => '0'));
		}
		$this->area_id = intval($_GET['area_id']);
	}

    /**
     * 我的红包记录
     */
    public function indexOp() {
		$level = empty($_POST['level']) ? 'all' : $_POST['level'];
		
		$lists = array();
		
		$TYPE = array(
			'all'=>'全部',
			'level'=>'级别奖',
			'parent'=>'见点奖',
			'invite'=>'直接推荐奖',
			'thankful'=>'感恩奖'
		);
		
		$model_dis = model('distributor');
		
		$where['area_id'] = intval($_GET['area_id']);
		$where['member_id'] = $this->member_info['member_id'];
		
		$result = $model_dis->getInfoOne('distributor_gp',$where, 'addtime','addtime asc');
		if(empty($result)){
			$area_info = $model_dis->getInfoOne('distributor_gp_area',array('item_id'=>$this->area_id), 'item_note');
			output_error($area_info['item_note'], array('distributor' => '0'));
		}
		unset($where);
		unset($result);
		
		//获得总红包
		$where['member_id'] = $this->member_info['member_id'];
		$result = $model_dis->getInfoOne('distributor_gp_detail', $where, 'SUM(detail_bonus) as money');
		$total = empty($result['money']) ? 0 : $result['money'];
		
		if($level != 'all'){
			$where['detail_type'] = $level;
		}
		
		$result = $model_dis->getInfoOne('distributor_gp_detail', $where, 'SUM(detail_bonus) as money');
		$count = empty($result['money']) ? 0 : $result['money'];
		
		$record_list = $model_dis->getInfoList('distributor_gp_detail', $where, 10, 'detail_addtime desc');
		
		foreach($record_list as $key=>$value){
			$lists[] = array(
				'money'=>$value['detail_bonus'],
				'desc'=>$value['detail_desc'],
				'addtime'=>date('Y-m-d H:i:s',$value['detail_addtime']),
				'status'=>$value['detail_status'],
				'id'=>$value['item_id']
			);
		}
		
		$page_count = $model_dis->gettotalpage();
	
		$curinfo = empty($TYPE[$level]) ? '' : $TYPE[$level].'(￥'.$count.')';
        output_data(array('commission_list' => $lists,'total'=>$total,'curinfo'=>$curinfo), mobile_page($page_count));
    }
}
