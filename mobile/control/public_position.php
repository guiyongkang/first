<?php
/**
 * 我的公排位置
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');

class public_position extends mobileMemberControl {
	
	private $area_id;
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
     * 我的公排位置
     */
    public function indexOp() {
		$model_dis = model('distributor');
		
		//状态
		$_STATUS = array('已出局','正常');
		
		$lists = array();
		$where['area_id'] = intval($_GET['area_id']);
		$where['member_id'] = $this->member_info['member_id'];
		
		$result = $model_dis->getInfoOne('distributor_gp',$where, 'addtime','addtime asc');
		if(empty($result)){
			$area_info = $model_dis->getInfoOne('distributor_gp_area',array('item_id'=>$this->area_id), 'item_note');
			output_error($area_info['item_note'], array('distributor' => '0'));
		}
		unset($result);
			
		$record_list = $model_dis->getInfoList('distributor_gp', $where, 20, 'addtime desc');
		
		foreach($record_list as $key=>$value){
			$lists[] = array(
				'y'=>$value['distributor_y'],
				'x'=>$value['distributor_x'],
				'state'=>$_STATUS[$value['status']],
				'status'=>$value['status'],
				'addtime'=>date('m-d H:i',$value['addtime'])
			);
		}
		
		$page_count = $model_dis->gettotalpage();
	
        output_data(array('position_list' => $lists), mobile_page($page_count));
    }
}
