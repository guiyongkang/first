<?php
/**
 * 我的分销团队
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');

class distributor_term extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
		if (empty($this->member_info['is_distributor'])) {
            output_error('您还不是分销商', array('distributor' => '0'));
        }
	}

    /**
     * 团队列表
     */
    public function indexOp() {
		$childs_all = array();
		$my_term_list = array();
		
		$pagesize = 10;
		$curpage = empty($_POST['curpage']) ? 1 : intval($_POST['curpage']);
		$level = empty($_POST['level']) ? 1 : intval($_POST['level']);
		
		$model_dis = model('distributor');
		//分销层级
		$dis_setting = $model_dis->getInfoOne('distributor_setting','', 'dis_bonus_level,dis_name');
		$dis_level_arr = array('一','二','三','四','五','六','七','八','九');
		
		for($j=0; $j<$dis_setting['dis_bonus_level']; $j++){
			$level_list[$j] = array(
				'id'=>$j+1,
				'name'=>$dis_level_arr[$j].'级'.$dis_setting['dis_name']
			);
		}
		
		$str = ','.$this->member_info['member_id'].',';
		$result = model()->query('SELECT * FROM shop_distributor_account WHERE dis_path LIKE "%'.$str.'%"');
		
		if($result == null){
			$curinfo = $dis_level_arr[$level-1].'级'.$dis_setting['dis_name'].'<i>(0)</i>';
			output_data(array('term_list' => array(),'hasmore'=>false,'total'=>0,'curinfo'=>$curinfo,'level_list'=>$level_list));
		}
		
		$total = count($result);
		
		foreach($result as $value){
			if($level>0){
				$arr = explode(',',trim($value['dis_path'],','));
				$arr = array_reverse($arr);
				$position = array_search($this->member_info['member_id'],$arr);
				if($position==$level-1){
					$childs_all[] = $value;
				}
			}else{
				$childs_all[] = $value;
			}
		}
		
		$count = count($childs_all);
		
		if($count==0){
			$curinfo = $dis_level_arr[$level-1].'级'.$dis_setting['dis_name'].'<i>(0)</i>';
			output_data(array('term_list' => array(),'hasmore'=>false,'total'=>$total,'curinfo'=>$curinfo,'level_list'=>$level_list));
		}
		
		$curinfo = $dis_level_arr[$level-1].'级'.$dis_setting['dis_name'].'<i>('.$count.')</i>';
		
		$hasmore = $count>$curpage*$pagesize ? true : false;
		$length = $count>$curpage*$pagesize ? $pagesize : $count-($curpage-1)*$pagesize;
		
		$my_term_list = array_slice($childs_all,(($curpage-1)*$pagesize),$length);
		
		if(empty($my_term_list)){
			$curinfo = $dis_level_arr[$level-1].'级'.$dis_setting['dis_name'].'<i>(0)</i>';
			output_data(array('term_list' => array(),'hasmore'=>false,'total'=>$total,'curinfo'=>$curinfo,'level_list'=>$level_list));
		}
		
		$memberids = array();
		foreach($my_term_list as $k_t=>$v_t){
			$memberids[] = $v_t['member_id'];
		}
		
		//获取分销商级别
		$model_dis = model('distributor');
		$dis_levels = array();
		$result = $model_dis->getInfoList('distributor_level','','','level_id asc','level_name,level_id');
		foreach($result as $key=>$val){
			$dis_levels[$val['level_id']] = $val['level_name'];
		}
		
		//获取会员信息
		$members = array();
		$result = model()->query('SELECT member_name,member_id,member_mobile FROM shop_member WHERE member_id in('.implode(',',$memberids).')');
		if($result==null){
			$curinfo = $dis_level_arr[$level-1].'级'.$dis_setting['dis_name'].'<i>(0)</i>';
			output_data(array('term_list' => array(),'hasmore'=>false,'total'=>$total,'curinfo'=>$curinfo,'level_list'=>$level_list));
		}else{
			foreach($result as $kk=>$vv){
				$members[$vv['member_id']] = $vv;
			}
		}
		
		$lists = array();
		//组合数组
		foreach($my_term_list as $mid=>$meminfo){
			$lists[$mid]['member_id'] = $meminfo['member_id'];
			$lists[$mid]['addtime'] = date('Y-m-d H:i:s',$meminfo['addtime']);
			$lists[$mid]['level_name'] = empty($dis_levels[$meminfo['level_id']]) ? '暂无！' : $dis_levels[$meminfo['level_id']];
			$lists[$mid]['nick_name']  = empty($members[$meminfo['member_id']]) ? '暂无！' : $members[$meminfo['member_id']]['member_name'];
			$lists[$mid]['telephone']  = empty($members[$meminfo['member_id']]) ? '暂无！' : $members[$meminfo['member_id']]['member_mobile'];
			$lists[$mid]['avatar']  = getMemberAvatarForID($meminfo['member_id']);
		}

        output_data(array('term_list' => $lists,'hasmore'=>$hasmore,'total'=>$total,'curinfo'=>$curinfo,'level_list'=>$level_list));
    }
	
}
