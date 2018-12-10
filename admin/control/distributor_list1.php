<?php
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class distributor_list extends SystemControl
{	
	public function __construct(){
		parent::__construct();
		
	}
	
	/*分销商列表*/
	public function dislistsOp(){
		$model_dis = model('distributor');
		$dis_lists = array();
		
		//获得分销商列表
		$condition = array();
		if(!empty($_GET['levelid'])){
			$condition['level_id'] = intval($_GET['levelid']);
		}
		if(!empty($_GET['membername'])){
			$search_memberids = array();
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
		
		$lists = $model_dis->getInfoList('distributor_account',$condition, 20, 'distributor_id desc');
		$showpageinfo = $model_dis->showpage('2');
		core\tpl::output('show_page', $showpageinfo);
		
		//分销商级别
		$dis_level = array();
		$result = $model_dis->getInfoList('distributor_level','', 30, 'level_id asc', 'level_id,level_name');
		foreach($result as $r){
			$dis_level[$r['level_id']] = $r['level_name'];
		}
		unset($result);
		unset($r);
		core\tpl::output('dis_level', $dis_level);
		
		//获得分销商及上级会员信息
		$member_ids = $member_info = array();
		foreach($lists as $account_id=>$account){
			if(!in_array($account['member_id'],$member_ids)){
				$member_ids[] = $account['member_id'];
			}
			if(!in_array($account['inviter_id'],$member_ids) && $account['inviter_id']>0){
				$member_ids[] = $account['inviter_id'];
			}
		}
		
		if(!empty($member_ids)){
			$where['member_id'] = array('in',$member_ids);
			$result = model('member')->getMemberList($where,'member_id,member_name');
			foreach($result as $r){
				$member_info[$r['member_id']] = $r['member_name'];
			}
		}
		
		
		foreach($lists as $value){
			$value['member_name'] = empty($member_info[$value['member_id']]) ? '暂无' : $member_info[$value['member_id']];
			$value['member_avatar'] = getMemberAvatarForID($value['member_id']);
			$value['inviter_name'] = empty($member_info[$value['inviter_id']]) ? '暂无' : $member_info[$value['inviter_id']];
			$value['inviter_avatar'] = getMemberAvatarForID($value['inviter_id']);
			$value['level_name'] = empty($dis_level[$value['level_id']]) ? '暂无' : $dis_level[$value['level_id']];
			$dis_lists[$value['distributor_id']] = $value;
		}
		
        core\tpl::output('dis_lists', $dis_lists);
		core\tpl::output('search', $_GET);
		core\tpl::showpage('distributor_dislist');
	}
	
	//更变分销商级别
	public function changelevelOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model = model('distributor');
		
		if(empty($_POST['aid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=dislists');
		}
		
		if(empty($_POST['level_id'])){
			error($lang['not_level_id'],'index.php?act='.$_GET['act'].'&op=dislists');
		}
		
		$where['distributor_id'] = array('in',$_POST['aid']);
		$update['level_id'] = intval($_POST['level_id']);
		$result = $model->editInfo('distributor_account',$update, $where);
		if($result){
			success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=dislists');
		}else{
			error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=dislists');
		}
	}
	
	//显示分销明细统计
	public function showdetailOp(){
		$model_dis = model('distributor');
		if(empty($_POST['type'])){
			echo json_encode(array('html'=>''));
			exit;
		}
		$type = $_POST['type'];
		if(empty($_POST['aid'])){
			echo json_encode(array('html'=>''));
			exit;
		}
		$aid = intval($_POST['aid']);
		$member_info = model('member')->getMemberInfoByID($aid, 'member_name');
		
		$html = '<table width="400px" style="margin:10px auto" cellpadding="0" cellspacing="0">';
		switch($type){
			case 'dis':
				//获取下级分销商
				$childs = array();
				$result = $model_dis->query('select member_id from shop_distributor_account where dis_path like "%,'.$aid.',%"');
				
				if(is_array($result)){
					foreach($result as $r){
						$childs[] = $r['member_id'];
					}
				}
				$childs[] = -1;
				
				//获取自身的分销总额
				$html .= '<tr><td style="height:40px; width:120px; line-height:40px; font-size:16px;">自身分销总额</td><td style="height:40px; line-height:40px; font-size:16px; color:red">&yen; ';
				$result = $model_dis->getInfoOne('distributor_goodsrecord',array('owner_id'=>$aid),'sum(goods_price*goods_num) as money');
				$html .= $result['money'] ? $result['money'] : '0';
				$html .= ' 元</td></tr>';
				
				//获取下级销售额
				$html .= '<tr><td style="height:40px; width:120px; line-height:40px; font-size:16px;">下级分销总额</td><td style="height:40px; line-height:40px; font-size:16px; color:red">&yen; ';
				$result = $model_dis->getInfoOne('distributor_goodsrecord',array('owner_id'=>array('in',$childs)),'sum(goods_price*goods_num) as money');
				$html .= $result['money'] ? $result['money'] : '0';
				$html .= ' 元</td></tr>';
			break;
			case 'com':
				//获取自身的分销总额
				$html .= '<tr><td style="height:40px; width:150px; line-height:40px; font-size:16px;">分销商品获得佣金</td><td style="height:40px; line-height:40px; font-size:16px; color:red">&yen; ';
				$result = $model_dis->getInfoOne('distributor_goodsrecord_detail',array('member_id'=>$aid),'sum(detail_bonus) as money');
				$html .= $result['money'] ? $result['money'] : '0';
				$html .= ' 元</td></tr>';
				
				//获取下级销售额
				$html .= '<tr><td style="height:40px; width:150px; line-height:40px; font-size:16px;">下级公排获得佣金</td><td style="height:40px; line-height:40px; font-size:16px; color:red">&yen; ';
				$result = $model_dis->getInfoOne('distributor_gp_detail',array('member_id'=>$aid),'sum(detail_bonus) as money');
				$html .= $result['money'] ? $result['money'] : '0';
				$html .= ' 元</td></tr>';
			break;
		}
		$html .= '</table>';
		echo json_encode(array('html'=>$html,'member_name'=>isset($member_info['member_name']) ? $member_info['member_name'] : ''));
		exit;
	}
	
	//分销商的下属
	public function childlistsOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		if(empty($_GET['aid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=dislists');
		}
		$aid = intval($_GET['aid']);
		
		$model_dis = model('distributor');
		//分销商级别
		$dis_level = array();
		$result = $model_dis->getInfoList('distributor_level','', 30, 'level_id asc', 'level_id,level_name');
		foreach($result as $r){
			$dis_level[$r['level_id']] = $r['level_name'];
		}
		unset($result);
		unset($r);
		core\tpl::output('dis_level', $dis_level);
		
		//获取本人的信息
		$my_info = $model_dis->getInfoOne('member',array('member_id'=>$aid),'is_distributor,member_name,member_id');
		if(empty($my_info)){
			error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=dislists');
		}
		
		if(empty($my_info['is_distributor'])){
			error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=dislists');
		}
		
		//获取下属列表
		$my_childs = array();
		$con = '1';
		if(!empty($_GET['levelid'])){
			$con .= ' and a.level_id='.intval($_GET['levelid']);
		}
		if(!empty($_GET['membername'])){
			$con .= ' and m.member_name like "%'.trim($_GET['membername']).'%"';
		}
		$con .= ' and a.dis_path LIKE "%'.$aid.'%"';
		$result = $model_dis->query('SELECT a.*,m.member_name FROM shop_distributor_account AS a LEFT JOIN shop_member AS m on a.member_id=m.member_id WHERE '.$con.' ORDER BY a.distributor_id DESC');
		if(is_array($result)){
			$inviter_ids = $members = array();
			foreach($result as $value){
				if($value['inviter_id']>0 && !in_array($value['inviter_id'],$inviter_ids)){
					$inviter_ids[] = $value['inviter_id'];
				}
				$my_childs[$value['member_id']] = $value;
			}
			unset($result);
			if(!empty($inviter_ids)){
				$result = $model_dis->query('SELECT member_name,member_id FROM shop_member WHERE member_id in('.implode(',',$inviter_ids).')');
				if(is_array($result)){
					foreach($result as $rr){
						$members[$rr['member_id']] = $rr['member_name'];
					}
				}
			}
			
			foreach($my_childs as $memberid=>$membderinfo){
				$my_childs[$memberid]['member_avatar'] = getMemberAvatarForID($memberid);
				$my_childs[$memberid]['inviter_name'] = empty($members[$membderinfo['inviter_id']]) ? '暂无' : $members[$membderinfo['inviter_id']];
				$my_childs[$memberid]['inviter_avatar'] = getMemberAvatarForID($membderinfo['inviter_id']);
				$my_childs[$memberid]['level_name'] = empty($dis_level[$membderinfo['level_id']]) ? '暂无' : $dis_level[$membderinfo['level_id']];
			}
		}
		if(empty($_GET['level'])){
			core\tpl::output('dis_lists', $my_childs);
		}else{
			$level = intval($_GET['level']);
			$lists = array();
			foreach($my_childs as $childid=>$child){
				$arr = explode(',',trim($child['dis_path'],','));
				$arr = array_reverse($arr);
				$position = array_search($aid,$arr);
				if($position==$level-1){
					$lists[$child['member_id']] = $child;
				}
			}
			core\tpl::output('dis_lists', $lists);
		}
		
		$dis_setting = $model_dis->getInfoOne('distributor_setting','','dis_bonus_level');
		core\tpl::output('maxlevel', $dis_setting['dis_bonus_level']);
		core\tpl::output('my_info', $my_info);
		core\tpl::output('search', $_GET);
		core\tpl::showpage('distributor_childlist');
	}
	
	/*公排卡位列表*/
	public function publistsOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		
		//获得公排分区列表
		$result = $model_dis->getInfoList('distributor_gp_area','','','item_id asc','item_id,item_name');
		$public_area = array();
		foreach($result as $r){
			$public_area[] = $r;
		}		
		core\tpl::output('public_area', $public_area);
		if(empty($public_area)){
			error('请设置公排分区','index.php?act='.$_GET['act'].'&op=pubareas');
		}
		
		if(!empty($_GET['area_id'])){
			$area_id = intval($_GET['area_id']);
		}else{
			$area_id = $public_area[0]['item_id'];
		}
		core\tpl::output('area_id', $area_id);
		
		$pub_lists = array();
		$condition = array();
		$condition['area_id'] = $area_id;
		if(!empty($_GET['miny']) && !empty($_GET['maxy'])){
			$condition['distributor_y'] = array('between',array(intval($_GET['miny']),intval($_GET['maxy'])));
		}elseif(!empty($_GET['miny'])){
			$condition['distributor_y'] = array('egt',intval($_GET['miny']));
		}elseif(!empty($_GET['maxy'])){
			$condition['distributor_y'] = array('elt',intval($_GET['maxy']));
		}
		
		if(!empty($_GET['minx']) && !empty($_GET['maxx'])){
			$condition['distributor_x'] = array('between',array(intval($_GET['minx']),intval($_GET['maxx'])));
		}elseif(!empty($_GET['minx'])){
			$condition['distributor_x'] = array('egt',intval($_GET['minx']));
		}elseif(!empty($_GET['maxx'])){
			$condition['distributor_x'] = array('elt',intval($_GET['maxx']));
		}
		
		if(!empty($_GET['status'])){
			$condition['status'] = intval($_GET['status'])-1;
		}
		if(!empty($_GET['membername'])){
			$search_memberids = array();
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
		
		$lists = $model_dis->getInfoList('distributor_gp',$condition, 20, 'ralate_id desc');
		$showpageinfo = $model_dis->showpage('2');
		core\tpl::output('show_page', $showpageinfo);
		
		//获得分销商及上级会员信息
		$member_ids = $member_info = $recordids = array();
		foreach($lists as $account_id=>$account){
			if(!in_array($account['member_id'],$member_ids)){
				$member_ids[] = $account['member_id'];
			}
			if(!in_array($account['parentid'],$member_ids) && $account['parentid']>0){
				$member_ids[] = $account['parentid'];
			}
			
			$recordids[] = $account['ralate_id'];
		}
		
		
		
		//获得红包列表
		$hongbaolist = array();
		$con['record_id'] = array('in',$recordids);
		$result = $model_dis->getInfoList('distributor_gp_detail',$con, 0, 'detail_level asc');
		foreach($result as $rrr){
			if($rrr['detail_level']==0){
				$hongbaolist[$rrr['record_id']]['other'][$rrr['detail_type']] = array('member_id'=>$rrr['member_id'],'detail_bonus'=>$rrr['detail_bonus']);
			}else{
				$hongbaolist[$rrr['record_id']]['level'][$rrr['detail_level']] = array('member_id'=>$rrr['member_id'],'detail_bonus'=>$rrr['detail_bonus']);
			}
			
			if(!in_array($rrr['member_id'],$member_ids)){
				$member_ids[] = $rrr['member_id'];
			}
		}
		
		if(!empty($member_ids)){
			$where['member_id'] = array('in',$member_ids);
			$result = model('member')->getMemberList($where,'member_id,member_name');
			foreach($result as $r){
				$member_info[$r['member_id']] = $r['member_name'];
			}
		}
		
		foreach($lists as $value){
			$value['member_name'] = empty($member_info[$value['member_id']]) ? '暂无' : $member_info[$value['member_id']];
			$value['member_avatar'] = getMemberAvatarForID($value['member_id']);
			$value['parent_name'] = empty($member_info[$value['parentid']]) ? '暂无' : $member_info[$value['parentid']];
			$value['parent_avatar'] = getMemberAvatarForID($value['parentid']);
			//级别奖
			if(!empty($hongbaolist[$value['ralate_id']]) && !empty($hongbaolist[$value['ralate_id']]['level'])){
				foreach($hongbaolist[$value['ralate_id']]['level'] as $key=>$vv){
					$value['prize_level'][$key] = array(
						'member_name'=>empty($member_info[$vv['member_id']]) ? '暂无' : $member_info[$vv['member_id']],
						'money'=>$vv['detail_bonus']
					);
				}
			}
			//其他奖项
			if(!empty($hongbaolist[$value['ralate_id']]) && !empty($hongbaolist[$value['ralate_id']]['other'])){
				foreach($hongbaolist[$value['ralate_id']]['other'] as $k=>$v){
					$value['prize_other'][$k] = array(
						'member_name'=>empty($member_info[$v['member_id']]) ? '暂无' : $member_info[$v['member_id']],
						'money'=>$v['detail_bonus']
					);
				}
			}
			$pub_lists[$value['ralate_id']] = $value;
		}
		
        core\tpl::output('pub_lists', $pub_lists);
		core\tpl::output('search', $_GET);
		core\tpl::showpage('distributor_publist');
	}
	
	/*公排列表位置下级会员列表*/
	public function pubchildsOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		if(empty($_GET['aid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=publists');
		}
		
		if(empty($_GET['area_id'])){
			error('请勿非法提交','index.php?act='.$_GET['act'].'&op=publists');
		}
		
		$aid = intval($_GET['aid']);
		$model_dis = model('distributor');
		
		//获得公排分区列表
		$result = $model_dis->getInfoList('distributor_gp_area','','','item_id asc','item_id,item_name');
		$public_area = array();
		foreach($result as $r){
			$public_area[] = $r;
		}		
		core\tpl::output('public_area', $public_area);
		if(empty($public_area)){
			error('请设置公排分区','index.php?act='.$_GET['act'].'&op=pubareas');
		}
		
		$area_id = intval($_GET['area_id']);
		core\tpl::output('area_id', $area_id);
		
		$my_info = $model_dis->getInfoOne('distributor_gp',array('ralate_id'=>$aid));
		if(empty($my_info)){
			error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=publists');
		}
		
		$dis_setting = $model_dis->getInfoOne('distributor_setting','','public_times');
		$pub_lists = array();
		$condition = array();
		$condition['area_id'] = $area_id;
		$condition['parentpath'] = array('like','%,'.$my_info['member_id'].',%');
		$result = $model_dis->getInfoOne('distributor_gp',$condition,'MAX(distributor_y) as y');
		if(is_array($result) && !empty($result['y'])){
			core\tpl::output('my_child_level', intval($result['y'])-$my_info['distributor_y']);
		}else{
			core\tpl::output('my_child_level', 0);
		}
		
		if(!empty($_GET['level'])){
			$condition['distributor_y'] = $my_info['distributor_y'] + intval($_GET['level']);
			$minx = pow($dis_setting['public_times'],intval($_GET['level']))*($my_info['distributor_x']-1)+1;
			$maxx = pow($dis_setting['public_times'],intval($_GET['level']))*$my_info['distributor_x'];
			$condition['distributor_x'] = array('between',array($minx,$maxx));
		}
		if(!empty($_GET['status'])){
			$condition['status'] = intval($_GET['status'])-1;
		}
		if(!empty($_GET['membername'])){
			$search_memberids = array();
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
		
		$lists = $model_dis->getInfoList('distributor_gp',$condition, 20, 'ralate_id desc');
		$showpageinfo = $model_dis->showpage('2');
		core\tpl::output('show_page', $showpageinfo);
		
		//获得分销商及上级会员信息
		$member_ids = $member_info = $recordids = array();
		foreach($lists as $account_id=>$account){
			if(!in_array($account['member_id'],$member_ids)){
				$member_ids[] = $account['member_id'];
			}
			if(!in_array($account['parentid'],$member_ids) && $account['parentid']>0){
				$member_ids[] = $account['parentid'];
			}
			
			$recordids[] = $account['ralate_id'];
		}
		
		
		
		//获得红包列表
		$hongbaolist = array();
		$con['record_id'] = array('in',$recordids);
		$result = $model_dis->getInfoList('distributor_gp_detail',$con, 0, 'detail_level asc');
		foreach($result as $rrr){
			if($rrr['detail_level']==0){
				$hongbaolist[$rrr['record_id']]['other'][$rrr['detail_type']] = array('member_id'=>$rrr['member_id'],'detail_bonus'=>$rrr['detail_bonus']);
			}else{
				$hongbaolist[$rrr['record_id']]['level'][$rrr['detail_level']] = array('member_id'=>$rrr['member_id'],'detail_bonus'=>$rrr['detail_bonus']);
			}
			
			if(!in_array($rrr['member_id'],$member_ids)){
				$member_ids[] = $rrr['member_id'];
			}
		}
		
		if(!empty($member_ids)){
			$where['member_id'] = array('in',$member_ids);
			$result = model('member')->getMemberList($where,'member_id,member_name');
			foreach($result as $r){
				$member_info[$r['member_id']] = $r['member_name'];
			}
		}
		
		foreach($lists as $value){
			$value['member_name'] = empty($member_info[$value['member_id']]) ? '暂无' : $member_info[$value['member_id']];
			$value['member_avatar'] = getMemberAvatarForID($value['member_id']);
			$value['parent_name'] = empty($member_info[$value['parentid']]) ? '暂无' : $member_info[$value['parentid']];
			$value['parent_avatar'] = getMemberAvatarForID($value['parentid']);
			//级别奖
			if(!empty($hongbaolist[$value['ralate_id']]) && !empty($hongbaolist[$value['ralate_id']]['level'])){
				foreach($hongbaolist[$value['ralate_id']]['level'] as $key=>$vv){
					$value['prize_level'][$key] = array(
						'member_name'=>empty($member_info[$vv['member_id']]) ? '暂无' : $member_info[$vv['member_id']],
						'money'=>$vv['detail_bonus']
					);
				}
			}
			//其他奖项
			if(!empty($hongbaolist[$value['ralate_id']]) && !empty($hongbaolist[$value['ralate_id']]['other'])){
				foreach($hongbaolist[$value['ralate_id']]['other'] as $k=>$v){
					$value['prize_other'][$k] = array(
						'member_name'=>empty($member_info[$v['member_id']]) ? '暂无' : $member_info[$v['member_id']],
						'money'=>$v['detail_bonus']
					);
				}
			}
			$pub_lists[$value['ralate_id']] = $value;
		}
		
        core\tpl::output('pub_lists', $pub_lists);
		core\tpl::output('search', $_GET);
		core\tpl::showpage('distributor_pubchilds');
	}
	
	/*分销记录列表*/
	public function disrecordOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		$record_lists = array();
		$condition = array();
		if(!empty($_GET['status'])){
			$condition['status'] = intval($_GET['status']);
		}
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
				$condition[$_GET['fields'].'_id'] = array('in',$search_memberids);
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
		
		$lists = $model_dis->getInfoList('distributor_goodsrecord',$condition, 20, 'record_id desc');
		$showpageinfo = $model_dis->showpage('2');
		core\tpl::output('show_page', $showpageinfo);
		
		//获得分销商及上级会员信息
		$member_ids = $member_info = $recordids = $goods_ids = $goods_info = array();
		foreach($lists as $account_id=>$account){
			if(!in_array($account['buyer_id'],$member_ids)){
				$member_ids[] = $account['buyer_id'];
			}
			if(!in_array($account['owner_id'],$member_ids) && $account['owner_id']>0){
				$member_ids[] = $account['owner_id'];
			}
			
			if(!in_array($account['goods_id'],$goods_ids)){
				$goods_ids[] = $account['goods_id'];
			}
			
			$recordids[] = $account['record_id'];
		}
		
		//获得商品信息
		$con_good['goods_id'] = array('in',$goods_ids);
		$result = $model_dis->getInfoList('goods',$con_good, 0, '','goods_name,goods_id,goods_image');
		foreach($result as $rrrr){
			$goods_info[$rrrr['goods_id']] = $rrrr;
		}
		
		//获得佣金列表
		$hongbaolist = array();
		$con['record_id'] = array('in',$recordids);
		$result = $model_dis->getInfoList('distributor_goodsrecord_detail',$con, 0, 'detail_level asc');
		foreach($result as $rrr){
			$hongbaolist[$rrr['record_id']][$rrr['detail_level']] = array('member_id'=>$rrr['member_id'],'detail_bonus'=>$rrr['detail_bonus']);
			
			if(!in_array($rrr['member_id'],$member_ids)){
				$member_ids[] = $rrr['member_id'];
			}
		}
		
		if(!empty($member_ids)){
			$where['member_id'] = array('in',$member_ids);
			$result = model('member')->getMemberList($where,'member_id,member_name');
			foreach($result as $r){
				$member_info[$r['member_id']] = $r['member_name'];
			}
		}
		
		foreach($lists as $value){
			$value['owner_name'] = empty($member_info[$value['owner_id']]) ? '暂无' : $member_info[$value['owner_id']];
			$value['owner_avatar'] = getMemberAvatarForID($value['owner_id']);
			$value['buyer_name'] = empty($member_info[$value['buyer_id']]) ? '暂无' : $member_info[$value['buyer_id']];
			$value['buyer_avatar'] = getMemberAvatarForID($value['buyer_id']);
			//佣金
			if(!empty($hongbaolist[$value['record_id']])){
				foreach($hongbaolist[$value['record_id']] as $key=>$vv){
					$value['prize_level'][$key] = array(
						'member_name'=>empty($member_info[$vv['member_id']]) ? '暂无' : $member_info[$vv['member_id']],
						'money'=>$vv['detail_bonus']
					);
				}
			}
			$value['goodname'] = empty($goods_info[$value['goods_id']]) ? '' : $goods_info[$value['goods_id']]['goods_name'];
			$value['goodimg'] = empty($goods_info[$value['goods_id']]) ? '' : thumb($goods_info[$value['goods_id']]);
			$record_lists[$value['record_id']] = $value;
		}
		
        core\tpl::output('record_lists', $record_lists);
		core\tpl::output('search', $_GET);
		core\tpl::showpage('distributor_disrecord');
	}
	
	//提现方式
	public function withdrawmethodOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		$lists = $model_dis->getInfoList('withdraw_method','', 20, 'method_id asc');
		
		if(empty($lists)){
			$lists[] = array(
				'method_code'=>'wxhongbao',
				'method_name'=>'微信红包',
				'method_check'=>0,
				'method_status'=>1,
				'method_min'=>1,
				'method_max'=>200,
				'method_fee'=>0,
				'method_yue'=>0
			);
			$lists[] = array(
				'method_code'=>'wxzhuanzhang',
				'method_name'=>'微信转账',
				'method_check'=>0,
				'method_status'=>1,
				'method_min'=>0,
				'method_max'=>0,
				'method_fee'=>0,
				'method_yue'=>0
			);
			$lists[] = array(
				'method_code'=>'alipay',
				'method_name'=>'支付宝',
				'method_check'=>1,
				'method_status'=>1,
				'method_min'=>0,
				'method_max'=>0,
				'method_fee'=>0,
				'method_yue'=>0
			);
			
			$lists[] = array(
				'method_code'=>'yue',
				'method_name'=>'转入余额',
				'method_check'=>0,
				'method_status'=>1,
				'method_min'=>0,
				'method_max'=>0,
				'method_yue'=>0,
				'method_fee'=>0
			);
			
			$flag = $model_dis->addAll('withdraw_method',$lists);
			$lists = $model_dis->getInfoList('withdraw_method','', 20, 'method_id asc');
		}
		core\tpl::output('lists', $lists);
		core\tpl::showpage('distributor_withdrawmethod');
	}
	
	//编辑提现方式
	public function withdrawmethodeditOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		if (chksubmit()){
			if(empty($_POST['mid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
			}
			
			$mid = intval($_POST['mid']);
			$method_info = $model_dis->getInfoOne('withdraw_method',array('method_id'=>$mid));
			if(empty($method_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
			}
			
			$update_array = array();
			$update_array['method_status'] = intval($_POST['status']);
			$update_array['method_min'] = $_POST['min'] ? number_format($_POST['min'],2,'.','') : 0;
			$update_array['method_max'] = $_POST['max'] ? number_format($_POST['max'],2,'.','') : 0;
			if($method_info['method_code']!='yue'){
				$update_array['method_fee'] = $_POST['fee'] ? number_format($_POST['fee'],2,'.','') : 0;
				$update_array['method_yue'] = $_POST['yue'] ? number_format($_POST['yue'],2,'.','') : 0;
			}
			
			if($method_info['method_code']=='bank'){
				$update_array['method_name'] = trim($_POST['name']);
			}elseif($method_info['method_code']=='wxhongbao' || $method_info['method_code']=='wxzhuanzhang'){
				$update_array['method_check'] = intval($_POST['check']);
			}
			
			$where['method_id'] = $mid;
			$result = $model_dis->editInfo('withdraw_method',$update_array,$where);
			if ($result){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
			
		}else{
			if(empty($_GET['mid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
			}
			
			$mid = intval($_GET['mid']);
			$method_info = $model_dis->getInfoOne('withdraw_method',array('method_id'=>$mid));
			if(empty($method_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
			}
			
			core\tpl::output('method_info', $method_info);
			core\tpl::showpage('distributor_withdrawmethodedit');
		}
	}
	
	//添加提现方式
	public function withdrawmethodaddOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		if (chksubmit()){
			
			if(empty($_POST['name'])){
				error($lang['not_info_method_name'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
			}
			
			$update_array = array();
			
			$update_array['method_status'] = intval($_POST['status']);
			$update_array['method_name'] = trim($_POST['name']);
			$update_array['method_min'] = $_POST['min'] ? number_format($_POST['min'],2,'.','') : 0;
			$update_array['method_max'] = $_POST['max'] ? number_format($_POST['max'],2,'.','') : 0;
			$update_array['method_fee'] = $_POST['fee'] ? number_format($_POST['fee'],2,'.','') : 0;
			$update_array['method_yue'] = $_POST['yue'] ? number_format($_POST['yue'],2,'.','') : 0;
			
			$result = $model_dis->addInfo('withdraw_method',$update_array);
			if ($result){
				$update_data['method_code'] = 'bank_'.$result;
				$flag = $model_dis->editInfo('withdraw_method',$update_data,array('method_id'=>$result));
				success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
			}else {
				error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
			}
			
		}else{
			core\tpl::showpage('distributor_withdrawmethodadd');
		}
	}
	
	/*删除提现方式*/
	public function withdrawmethoddelOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_distributor = model('distributor');
		
		if(empty($_GET['mid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
		}
		
		$flag = $model_distributor->delInfo('withdraw_method',array('method_id'=>intval($_GET['mid'])));
		
		if ($flag){
			success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
		}else {
			error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=withdrawmethod');
		}
	}
	
	/*提现记录*/
	public function withdrawrecordOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		$record_lists = array();
		$condition = array();
		if(!empty($_GET['code'])){
			$condition['method_code'] = $_GET['code'];
		}
		if(!empty($_GET['status'])){
			$condition['status'] = intval($_GET['status'])-1;
		}
		if(!empty($_GET['membername'])){
			$search_memberids = array();
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
		
		$lists = $model_dis->getInfoList('withdraw_record',$condition, 20, 'record_id desc');
		$showpageinfo = $model_dis->showpage('2');
		core\tpl::output('show_page', $showpageinfo);
		
		//获得分销商及上级会员信息
		$member_ids = $member_info = array();
		foreach($lists as $account_id=>$account){
			if(!in_array($account['member_id'],$member_ids)){
				$member_ids[] = $account['member_id'];
			}
		}
		
		if(!empty($member_ids)){
			$where['member_id'] = array('in',$member_ids);
			$result = model('member')->getMemberList($where,'member_id,member_name');
			foreach($result as $r){
				$member_info[$r['member_id']] = $r['member_name'];
			}
		}
		
		//获取提现方式
		$method_list = $model_dis->getInfoList('withdraw_method','', '', 'method_id asc');
		
		foreach($lists as $value){
			$value['member_name'] = empty($member_info[$value['member_id']]) ? '暂无' : $member_info[$value['member_id']];
			$value['member_avatar'] = getMemberAvatarForID($value['member_id']);
			$record_lists[$value['record_id']] = $value;
		}
		core\tpl::output('method_list', $method_list);
        core\tpl::output('record_lists', $record_lists);
		core\tpl::output('search', $_GET);
		core\tpl::showpage('distributor_withdrawrecord');
	}
	
	/*提现微信方式发送*/
	public function withdrawsendOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		if(empty($_GET['rid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
			
		$rid = intval($_GET['rid']);
		$record_info = $model_dis->getInfoOne('withdraw_record',array('record_id'=>$rid));
		if(empty($record_info)){
			error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
		
		if($record_info['record_status']!=0){
			error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
		
		if(!in_array($record_info['method_code'],array('wxhongbao','wxzhuanzhang'))){
			error('该记录提现方式不是微信红包或微信转帐','index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
		
		if($record_info['record_amount']<1){
			error('最小额度为1元','index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
		
		if($record_info['record_amount']>200 && $record_info['method_code'] == 'wxhongbao'){
			error('微信红包最大额度为200元','index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
		
		$data = logic('weixin_pay')->commission_withdraw($record_info);
		if($data['status']==1){
			//处理返回数据
			$record_data = array(
				"record_status"=>1,
				"record_outtradeno"=>$data['outtradeno'],
				"record_tradetime"=>$data['tradetime'],
				"record_tradeno"=>$data['tradeno'],
				"record_tradetype"=>$record_info['method_code']
			);
			$result = $model_dis->editInfo('withdraw_record',$record_data,array('record_id'=>$rid));
			
			if($record_info['record_yue']>0){
				$member_info = $model_dis->getInfoOne('member',array('member_id'=>$record_info['member_id']),'member_name');
				$yue_data = array(
					'amount'=>$record_info['record_yue'],
					'order_sn'=>$rid,
					'member_id'=>$record_info['member_id'],
					'member_name'=>$member_info['member_name']
				);
				$result = model('predeposit')->changePd('commission_come',$yue_data);
			}
			error('处理成功','index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}else{
			error($data['msg'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
	}
	
	/*提现其他方式审核*/
	public function withdrawdealOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		if(empty($_GET['rid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
			
		$rid = intval($_GET['rid']);
		$record_info = $model_dis->getInfoOne('withdraw_record',array('record_id'=>$rid));
		if(empty($record_info)){
			error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
		
		$member_info = $model_dis->getInfoOne('member',array('member_id'=>$record_info['member_id']),'member_name');
		
		if($record_info['record_yue']>0){
			$yue_data = array(
				'amount'=>$record_info['record_yue'],
				'order_sn'=>$rid,
				'member_id'=>$record_info['member_id'],
				'member_name'=>$member_info['member_name']
			);
			$result = model('predeposit')->changePd('commission_come',$yue_data);
		}
		
		$flag = $model_dis->editInfo('withdraw_record',array('record_status'=>1),array('record_id'=>$rid));
		if ($flag){
			success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}else {
			error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
		}
	}
	
	/*提现驳回*/
	public function withdrawrejectOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_dis = model('distributor');
		
		if (chksubmit()){
			if(empty($_POST['rid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
			}
				
			$rid = intval($_POST['rid']);
			$record_info = $model_dis->getInfoOne('withdraw_record',array('record_id'=>$rid));
			if(empty($record_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
			}
			
			if(empty($_POST['note'])){
				error('请填写原因','index.php?act='.$_GET['act'].'&op=withdrawrecord');
			}
			$flag = $model_dis->editInfo('withdraw_record',array('record_status'=>2,'record_note'=>trim($_POST['note'])),array('record_id'=>$rid));
			if ($flag){
				success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
			}else {
				error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
			}
		}else{
			if(empty($_GET['rid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
			}
				
			$rid = intval($_GET['rid']);
			$record_info = $model_dis->getInfoOne('withdraw_record',array('record_id'=>$rid));
			if(empty($record_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=withdrawrecord');
			}
			core\tpl::output('record_info', $record_info);
			core\tpl::showpage('distributor_withdrawrecordreject');
		}		
	}
	
	/*
	*公排分区
	*/
	public function pubareasOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
        $area_list = $model_distributor->getInfoList('distributor_gp_area','',30,'item_id asc');
		core\tpl::output('area_list',$area_list);
		core\tpl::output('show_page',$model_distributor->showpage('2'));
		core\tpl::showpage('distributor_pubarea_list');
	}
	
	/*
	*添加公排分区
	*/
	public function pubareasaddOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		
		if (chksubmit()){
			if(empty($_POST['name'])){
				error('请输入分区名称');
			}
			
			if(empty($_POST['condition'])){
				error('请输入升级条件');
			}
			
			if(!is_numeric($_POST['condition'])){
				error('升级条件只能输入整数');
			}
			
			$_POST['condition'] = intval($_POST['condition']);
			if($_POST['condition']<=0){
				error('请输入有效的升级条件');
			}
			
			$update_array = array(
				'item_name'=>trim($_POST['name']),
				'item_condition'=>$_POST['condition'],
				'item_note'=>trim($_POST['note']),
				'is_withdraw'=>empty($_POST['withdraw']) ? 0 : $_POST['withdraw']
			);
			
			$result = $model_distributor->addInfo('distributor_gp_area',$update_array);
			if ($result){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			core\tpl::showpage('distributor_pubarea_add');
		}
	}
	
	/*
	*修改公排分区
	*/
	public function pubareaseditOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		if (chksubmit()){
			if(empty($_POST['tid'])){
				error($lang['not_info_id']);
			}
			
			$condition['item_id'] = intval($_POST['tid']);
			$area_info = $model_distributor->getInfoOne('distributor_gp_area',$condition,'item_default');
			if(empty($area_info)){
				error($lang['info_not_exist']);
			}
			
			if(empty($_POST['name'])){
				error('请输入分区名称');
			}
			
			$update_array = array(
				'item_name'=>trim($_POST['name']),
				'item_note'=>trim($_POST['note']),
				'is_withdraw'=>empty($_POST['withdraw']) ? 0 : $_POST['withdraw']
			);
			
			if($area_info['item_default']==0){
				if(empty($_POST['condition'])){
					error('请输入升级条件');
				}
				
				if(!is_numeric($_POST['condition'])){
					error('升级条件只能输入整数');
				}
				
				$_POST['condition'] = intval($_POST['condition']);
				if($_POST['condition']<=0){
					error('请输入有效的升级条件');
				}
				$update_array['item_condition'] = $_POST['condition'];
			}
			
			$result = $model_distributor->editInfo('distributor_gp_area',$update_array,$condition);
			if ($result){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			if(empty($_GET['tid'])){
				error($lang['not_info_id']);
			}
			$condition['item_id'] = intval($_GET['tid']);
			$area_info = $model_distributor->getInfoOne('distributor_gp_area',$condition,'*');
			if(empty($area_info)){
				error($lang['info_not_exist']);
			}
			
			core\tpl::output('area_info', $area_info);
			core\tpl::showpage('distributor_pubarea_edit');
		}
	}
	
	/*
	*删除公排分区
	*/
	public function pubareasdelOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		
		if(empty($_GET['tid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=pubareas');
		}
		
		if (intval($_GET['tid']) > 0){
			$model_distributor = model('distributor');
			$condition = array('item_id'=>intval($_GET['tid']));
			$area_info = $model_distributor->getInfoOne('distributor_gp_area',$condition);
			if(empty($area_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=pubareas');
			}
			
			if($area_info['item_default']==1){
				error('第一个分区不能删除','index.php?act='.$_GET['act'].'&op=pubareas');
			}
			
			$public_info = $model_distributor->getInfoOne('distributor_gp',array('area_id'=>intval($_GET['tid'])),'*','ralate_id asc');
			if(!empty($public_info)){
				error('该分区下有公排数据，不能删除','index.php?act='.$_GET['act'].'&op=pubareas');
			}
			
			$result = $model_distributor->delInfo('distributor_gp_area',$condition);
			
			success($lang['nc_common_del_succ'],'index.php?act='.$_GET['act'].'&op=pubareas');
		}else {
			error($lang['nc_common_del_fail'],'index.php?act='.$_GET['act'].'&op=pubareas');
		}
	}
}

