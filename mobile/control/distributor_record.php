<?php
/**
 * 我的分销记录
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');

class distributor_record extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
		if (empty($this->member_info['is_distributor'])) {
            output_error('您还不是分销商', array('distributor' => '0'));
        }
	}

    /**
     * 我的分销记录
     */
    public function indexOp() {
		$model_dis = model('distributor');
		$member_ids = array();
		
		//获得下级
		$str = '%,'.$this->member_info['member_id'].',%';
		$condition_dis['dis_path'] = array('like',$str);
		$result = $model_dis->getInfoList('distributor_account', $condition_dis, 0, 'member_id desc', 'member_id');
		foreach($result as $rr){
			$member_ids[] = $rr['member_id'];
		}
		$member_ids[] = $this->member_info['member_id'];
		
		$lists = $goods_list = $goods_ids = array();
		
		//获得分销记录
		$where['owner_id'] = array('in',$member_ids);
		
		$record_list = $model_dis->getInfoList('distributor_goodsrecord', $where, 10, 'record_addtime desc');
		
		foreach($record_list as $r_k=>$r_v){
			$goods_ids[] = $r_v['goods_id'];
		}
		
		//获得商品记录
		if(!empty($goods_ids)){
			$condition_good['goods_id'] = array('in',$goods_ids);
			$result = $model_dis->getInfoList('goods',$condition_good, 0, '','goods_id,goods_name,goods_image');
			foreach($result as $r){
				$goods_list[$r['goods_id']] = $r;
			}
		}
		
		//状态
		$_STATUS = array(
			'10'=>'未付款',
			'20'=>'已付款',
			'30'=>'已发货',
			'40'=>'已完成'
		);
		
		foreach($record_list as $key=>$value){
			$lists[] = array(
				'goods_name'=>empty($goods_list[$value['goods_id']]) ? '暂无' : $goods_list[$value['goods_id']]['goods_name'],
				'goods_image'=>empty($goods_list[$value['goods_id']]) ? '' : thumb($goods_list[$value['goods_id']]),
				'record_type'=>$this->member_info['member_id']==$value['owner_id'] ? '自己分销' : '下属分销',
				'status'=>$_STATUS[$value['record_status']],
				'record_status'=>$value['record_status'],
				'price'=>$value['goods_price'],
				'num'=>$value['goods_num'],
				'addtime'=>date('Y-m-d H:i:s',$value['record_addtime'])
			);
		}
		
		$page_count = $model_dis->gettotalpage();
	
        output_data(array('record_list' => $lists), mobile_page($page_count));
    }
	
}
