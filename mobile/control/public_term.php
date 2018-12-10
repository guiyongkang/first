<?php
/**
 * 我的公排团队
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');

class public_term extends mobileMemberControl {
	
	private $area_id;
	public function __construct(){
		parent::__construct();
		if (empty($this->member_info['is_distributor'])) {
            output_error('您还不是分销商', array('distributor' => '0'));
        }
		if(empty($_POST['area_id']) || !is_numeric($_POST['area_id']) || intval($_POST['area_id'])<=0){
			output_error('请勿非法提交', array('distributor' => '0'));
		}
		$this->area_id = intval($_POST['area_id']);
	}

    /**
     * 我的公排团队
     */
    public function indexOp() {
		$my_term_list = $level_list = array();
		$level = empty($_POST['level']) ? 1 : intval($_POST['level']);
		$model_dis = model('distributor');
		
		//获取我的坐标
		$my_position = $model_dis->getInfoOne('distributor_gp', array('member_id'=>$this->member_info['member_id'],'area_id'=>$this->area_id), '*', 'ralate_id asc');
		if(empty($my_position)){
			$area_info = $model_dis->getInfoOne('distributor_gp_area',array('item_id'=>$this->area_id), 'item_note');
			output_error($area_info['item_note'], array('distributor' => '0'));
		}
		
		//获取我的全部下级数量
		$str = '%,'.$this->member_info['member_id'].',%';
		$condition['parentpath'] = array('like',$str);
		$total = $model_dis->getCount('distributor_gp',$condition);
		if($total==0){
			output_data(array('term_list' => array(),'hasmore'=>false,'total'=>0,'curinfo'=>'','level_list'=>array()));
		}
		
		//获取最深层次的y坐标
		$result = $model_dis->getInfoOne('distributor_gp', $condition, 'MAX(distributor_y) as y');
		$max_level = $result['y'] - $my_position['distributor_y'];
		for($i=0; $i<$max_level; $i++){
			$level_list[$i] = $i;
		}
		if($max_level<$level){
			$curinfo = $level.'级'.'<i>(0)</i>';
			output_data(array('term_list' => array(),'hasmore'=>false,'total'=>$total,'curinfo'=>$curinfo,'level_list'=>$level_list));
		}
		
		//获取递增方式
		$dis_setting = $model_dis->getInfoOne('distributor_setting','', 'public_times');
		
		//计算x区间
		$min_x = pow($dis_setting['public_times'],$level)*($my_position['distributor_x']-1)+1;
		$max_x = pow($dis_setting['public_times'],$level)*$my_position['distributor_x'];
		$where['parentpath'] = array('like',$str);
		$where['distributor_x'] = array('between',array($min_x,$max_x));
		$where['distributor_y'] = $my_position['distributor_y'] + $level;
		$count = $model_dis->getCount('distributor_gp',$where);
		if($count==0){
			$curinfo = $level.'级'.'<i>(0)</i>';
			output_data(array('term_list' => array(),'hasmore'=>false,'total'=>$total,'curinfo'=>$curinfo,'level_list'=>$level_list));
		}
		
		$my_term_list = $model_dis->getInfoList('distributor_gp',$where, 10, 'distributor_y asc,distributor_x asc,ralate_id asc');
		$page_count = $model_dis->gettotalpage();
		
		$curinfo = $level.'级'.'<i>('.$count.')</i>';
		
		$memberids = array();
		foreach($my_term_list as $k_t=>$v_t){
			$memberids[] = $v_t['member_id'];
		}
		
		//获取会员信息
		$members = array();
		$result = model()->query('SELECT member_name,member_id,member_mobile FROM shop_member WHERE member_id in('.implode(',',$memberids).')');
		if($result==null){
			$curinfo = $level.'级'.'<i>(0)</i>';
			output_data(array('term_list' => array(),'hasmore'=>false,'total'=>$total,'curinfo'=>$curinfo,'level_list'=>$level_list));
		}else{
			foreach($result as $kk=>$vv){
				$members[$vv['member_id']] = $vv;
			}
		}
		
		$_STATUS = array('已出局','正常');
		$lists = array();
		//组合数组
		foreach($my_term_list as $mid=>$meminfo){
			$lists[$mid]['member_id'] = $meminfo['member_id'];
			$lists[$mid]['addtime'] = date('Y-m-d H:i:s',$meminfo['addtime']);
			$lists[$mid]['y'] = $level;
			$lists[$mid]['x'] = $meminfo['distributor_x'];
			$lists[$mid]['state'] = $_STATUS[$meminfo['status']];
			$lists[$mid]['status'] = $meminfo['status'];
			$lists[$mid]['nick_name']  = empty($members[$meminfo['member_id']]) ? '暂无！' : $members[$meminfo['member_id']]['member_name'];
			$lists[$mid]['avatar']  = getMemberAvatarForID($meminfo['member_id']);
		}

        output_data(array('term_list' => $lists,'total'=>$total,'curinfo'=>$curinfo,'level_list'=>$level_list),mobile_page($page_count));
    }
	
}
