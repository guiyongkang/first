<?php
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class team extends SystemControl
{
	
	public function __construct(){
		parent::__construct();
		
	}
	
	/*
	*团队管理
	*/
	public function listsOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
			
        $team_list = $model_distributor->getInfoList('distributor_team','',30,'team_invitenum asc,team_addtime asc');
		
		core\tpl::output('team_list',$team_list);
		core\tpl::output('show_page',$model_distributor->showpage('2'));
		core\tpl::showpage('distributor_team_lists');
	}
	
	/*
	*添加股东类型
	*/
	public function addteamOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		
		if (chksubmit()){
			if(empty(trim($_POST['name']))){
				error('请填写股东级别');
			}
			
			$team_info = $model_distributor->getInfoOne('distributor_team',array('team_name'=>trim($_POST['name'])),'team_id');
			if(!empty($team_info['team_id'])){
				error('股东级别不能重复');
			}
			
			if(empty($_POST['invitenum'])){
				error('请填写升级条件');
			}
			
			if(!is_numeric($_POST['invitenum'])){
				error('升级条件必须为数字');
			}
			
			$num = intval($_POST['invitenum']);
			
			if($num<1){
				error('升级条件必须大于1，且为整数');
			}
			
			$update_array = array(
				'team_name'=>trim($_POST['name']),
				'team_invitenum'=>$num,
				'team_addtime'=>time()
			);
			
			$result = $model_distributor->addInfo('distributor_team',$update_array);
			if ($result){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			core\tpl::showpage('distributor_team_add');
		}
	}
	
	/*
	*修改股东类型
	*/
	public function editteamOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		
		if (chksubmit()){
			if(empty($_POST['tid'])){
				error($lang['not_info_id']);
			}
			
			
			if(empty(trim($_POST['name']))){
				error('请填写股东级别');
			}
			
			$team_info = $model_distributor->getInfoOne('distributor_team',array('team_name'=>trim($_POST['name'])),'team_id');
			if(!empty($team_info['team_id']) && intval($_POST['tid'])!=$team_info['team_id']){
				error('股东级别不能重复');
			}
			
			if(empty($_POST['invitenum'])){
				error('请填写升级条件');
			}
			
			if(!is_numeric($_POST['invitenum'])){
				error('升级条件必须为数字');
			}
			
			$num = intval($_POST['invitenum']);
			
			if($num<1){
				error('升级条件必须大于1，且为整数');
			}
			
			$update_array = array(
				'team_name'=>trim($_POST['name']),
				'team_invitenum'=>$num
			);
			$condition['team_id'] = intval($_POST['tid']);
			$result = $model_distributor->editInfo('distributor_team',$update_array,$condition);
			if ($result){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			if(empty($_GET['tid'])){
				error($lang['not_info_id']);
			}
			$condition['team_id'] = intval($_GET['tid']);
			$team_info = $model_distributor->getInfoOne('distributor_team',$condition,'*');
			if(empty($team_info)){
				error($lang['info_not_exist']);
			}
			
			core\tpl::output('team_info', $team_info);
			core\tpl::showpage('distributor_team_edit');
		}
	}
	
	/*
	*删除股东类型
	*/
	public function delteamOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		
		if(empty($_GET['tid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=lists');
		}
		
		if (intval($_GET['tid']) > 0){
			$model_distributor = model('distributor');
			$condition = array('team_id'=>intval($_GET['tid']));
			$result = $model_distributor->delInfo('distributor_team',$condition);
			
			success($lang['nc_common_del_succ'],'index.php?act='.$_GET['act'].'&op=lists');
		}else {
			error($lang['nc_common_del_fail'],'index.php?act='.$_GET['act'].'&op=lists');
		}
	}
	
	/*分红记录列表*/
	public function recordOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		$record_lists = array();
		$condition = array();
		if(!empty($_GET['membername'])){
			$search_memberids = array();
			$search_memberids[] = 0;
			$where_member['member_name'] = array('like','%'.trim($_GET['membername']).'%');
			$result = model('member')->getMemberList($where_member,'member_id');
			foreach($result as $r){
				$search_memberids[] = $r['member_id'];
			}
			unset($result);
			unset($r);
			if(!empty($search_memberids)){
				$condition['member_id'] = array('in',$search_memberids);
			}
		}
		
		if(!empty($_GET['goodname'])){
			$search_goodids = array();
			$search_goodids[] = 0;
			$where_good['goods_name'] = array('like','%'.trim($_GET['goodname']).'%');
			$result = model('goods')->getGoodsList($where_good,'goods_id');
			foreach($result as $r){
				$search_goodids[] = $r['goods_id'];
			}
			unset($result);
			unset($r);
			if(!empty($search_goodids)){
				$condition['goods_id'] = array('in',$search_goodids);
			}
		}
		
		if(!empty($_GET['teamid'])){
			$condition['team_id'] = $_GET['teamid'];
		}
		
		$lists = $model_dis->getInfoList('distributor_fenhong',$condition, 20, 'detail_id desc');
		$showpageinfo = $model_dis->showpage('2');
		core\tpl::output('show_page', $showpageinfo);
		
		//获得分销商及上级会员信息
		$member_ids = $member_info = $recordids = $goods_ids = $goods_info = array();
		foreach($lists as $account_id=>$account){
			if(!in_array($account['member_id'],$member_ids)){
				$member_ids[] = $account['member_id'];
			}
			if(!in_array($account['goods_id'],$goods_ids)){
				$goods_ids[] = $account['goods_id'];
			}
		}
		
		//获得商品信息
		$con_good['goods_id'] = array('in',$goods_ids);
		$result = $model_dis->getInfoList('goods',$con_good, 0, '','goods_name,goods_id,goods_image');
		foreach($result as $rrrr){
			$goods_info[$rrrr['goods_id']] = $rrrr;
		}
		
		if(!empty($member_ids)){
			$where['member_id'] = array('in',$member_ids);
			$result = model('member')->getMemberList($where,'member_id,member_name');
			foreach($result as $r){
				$member_info[$r['member_id']] = $r['member_name'];
			}
		}
		
		//获得股东类型
		$team_list = array();
		$result = $model_dis->getInfoList('distributor_team','',30,'team_invitenum asc,team_addtime asc');
		foreach($result as $r_t){
			$team_list[$r_t['team_id']] = $r_t['team_name'];
		}
		
		//统计总金额
		$total_amount = 0;
		foreach($lists as $value){
			$total_amount = $total_amount + $value['detail_bonus'];
			$value['member_name'] = empty($member_info[$value['member_id']]) ? '暂无' : $member_info[$value['member_id']];
			$value['member_avatar'] = getMemberAvatarForID($value['member_id']);
			$value['team_name'] = empty($team_list[$value['team_id']]) ? '' : $team_list[$value['team_id']];
			$value['goodname'] = empty($goods_info[$value['goods_id']]) ? '' : $goods_info[$value['goods_id']]['goods_name'];
			$value['goodimg'] = empty($goods_info[$value['goods_id']]) ? '' : thumb($goods_info[$value['goods_id']]);
			$record_lists[$value['detail_id']] = $value;
		}
		
        core\tpl::output('record_lists', $record_lists);
		core\tpl::output('team_list', $team_list);
		core\tpl::output('total_amount', $total_amount);
		core\tpl::output('search', $_GET);
		core\tpl::showpage('distributor_team_record');
	}
}

