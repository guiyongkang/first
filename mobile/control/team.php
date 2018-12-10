<?php
/**
 * 我的佣金记录
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');

class team extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
		if (empty($this->member_info['is_distributor'])) {
            output_error('您还不是分销商', array('distributor' => '0'));
        }
	}
	
	public function indexOp(){
		$model_dis = model('distributor');
		
		//获得我的级别
		$where['member_id'] = $this->member_info['member_id'];
		$result = $model_dis->getInfoOne('distributor_account',$where, 'team_id');
		$my_teamid = empty($result['team_id']) ? 0 : $result['team_id'];
		unset($result);
		
		$my_info = '';
		
		//获取股东级别
		$team_list = array();
		$result = $model_dis->getInfoList('distributor_team','',30,'team_invitenum asc,team_addtime asc');
		$i=0;
		foreach($result as $key=>$value){
			$i++;
			if($value['team_id']==$my_teamid){
				$my_info = $value['team_name'];
			}
			
			$team_list[] = array(
				'id'=>$i,
				'name'=>$value['team_name'],
				'people'=>$value['team_invitenum']
			);
		}
		unset($result);
		
		output_data(array('position_list' => $team_list,'my_info'=>$my_info));
	}

    /**
     * 我的分红记录
     */
    public function commissionOp() {
		$model_dis = model('distributor');
		$lists = array();
		
		$total_money = 0;
		//获得分红记录
		$where['member_id'] = $this->member_info['member_id'];
		$result = $model_dis->getInfoOne('distributor_fenhong',$where, 'SUM(detail_bonus) as money');
		if(!empty($result['money'])){
			$total_money = $result['money'];
		}
		$record_list = $model_dis->getInfoList('distributor_fenhong', $where, 20, 'addtime desc');
		
		foreach($record_list as $key=>$value){
			$lists[] = array(
				'money'=>$value['detail_bonus'],
				'desc'=>$value['detail_desc'],
				'addtime'=>date('Y-m-d H:i:s',$value['addtime'])
			);
		}
		
		$page_count = $model_dis->gettotalpage();
	
        output_data(array('record_list' => $lists,'total_money'=>$total_money), mobile_page($page_count));
    }
	
}
