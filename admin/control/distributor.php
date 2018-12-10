<?php
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class distributor extends SystemControl
{
	private $setting_links = array(
		array('url' => 'act=distributor&op=setting', 'lang' => 'nc_distributor_setting_basic'),
		array('url' => 'act=distributor&op=setting_dis', 'lang' => 'nc_distributor_setting_dis'),
		array('url' => 'act=distributor&op=setting_public', 'lang' => 'nc_distributor_setting_public'),
		array('url' => 'act=distributor&op=setting_commission', 'lang' => 'nc_distributor_setting_commission'),
		array('url' => 'act=distributor&op=setting_codebg', 'lang' => 'nc_distributor_setting_codebg')
	);
	
	public function __construct(){
		parent::__construct();
		
	}
	
	/*分销产品管理*/
	public function goodslistOp(){
		core\language::read('goods');
		$model_goods = model('goods');
        /**
         * 处理商品分类
         */
        $choose_gcid = isset($_REQUEST['choose_gcid']) && intval($_REQUEST['choose_gcid']) > 0 ? intval($_REQUEST['choose_gcid']) : 0;
        $gccache_arr = model('goods_class')->getGoodsclassCache($choose_gcid, 3);
        core\tpl::output('gc_json', json_encode($gccache_arr['showclass'], JSON_UNESCAPED_UNICODE));
        core\tpl::output('gc_choose_json', json_encode($gccache_arr['choose_gcid'], JSON_UNESCAPED_UNICODE));
        /**
         * 查询条件
         */
        $where = array();
		$where['is_distribute'] = 1;
        if (!empty($_GET['search_goods_name'])) {
            $where['goods_name'] = array('like', '%' . trim($_GET['search_goods_name']) . '%');
        }
        if (isset($_GET['search_commonid']) && intval($_GET['search_commonid']) > 0) {
            $where['goods_commonid'] = intval($_GET['search_commonid']);
        }
        if ($choose_gcid > 0) {
            $where['gc_id_' . $gccache_arr['showclass'][$choose_gcid]['depth']] = $choose_gcid;
        }
		$goods_list = array();
		
		$_GET['type'] = isset($_GET['type']) ? $_GET['type'] : 'all';
		$goods_list = $model_goods->getGoodsCommonList($where);
		$dis_good = array();
		if(!empty($goods_list)){
			$model_distributor = model('distributor');
			$ids = array();
			foreach($goods_list as $v){
				$ids[] = $v['goods_commonid'];
			}
			$condition['good_id'] = array('in',$ids);
			$result = $model_distributor->getInfoList('distributor_goods',$condition,'','','good_id,good_status');
			foreach($result as $g){
				$dis_good[$g['good_id']] = $g['good_status'];
			}
		}
		
        core\tpl::output('goods_list', $goods_list);
		core\tpl::output('dis_good', $dis_good);
        core\tpl::output('page', $model_goods->showpage(2));        
        core\tpl::output('search', $_GET);
		core\tpl::showpage('distributor_goods');
	}
	
	/*选择分销产品*/
	public function goodsimportOp(){
		core\language::read('goods');
		
		$model_goods = model('goods');
		/**
		 * 处理商品分类
		 */
		$choose_gcid = ($t = isset($_REQUEST['choose_gcid']) && intval($_REQUEST['choose_gcid'])) > 0 ? $t : 0;
		$gccache_arr = model('goods_class')->getGoodsclassCache($choose_gcid, 3);
		core\tpl::output('gc_json', json_encode($gccache_arr['showclass'], JSON_UNESCAPED_UNICODE));
		core\tpl::output('gc_choose_json', json_encode($gccache_arr['choose_gcid'], JSON_UNESCAPED_UNICODE));
		/**
		 * 查询条件
		 */
		$where = array();
		$where['is_distribute'] = 0;
		if (!empty($_GET['search_goods_name'])) {
			$where['goods_name'] = array('like', '%' . trim($_GET['search_goods_name']) . '%');
		}
		if (isset($_GET['search_commonid']) && intval($_GET['search_commonid']) > 0) {
			$where['goods_commonid'] = intval($_GET['search_commonid']);
		}
		if ($choose_gcid > 0) {
			$where['gc_id_' . $gccache_arr['showclass'][$choose_gcid]['depth']] = $choose_gcid;
		}
		$goods_list = array();
			
		$_GET['type'] = isset($_GET['type']) ? $_GET['type'] : 'all';
		$goods_list = $model_goods->getGoodsCommonList($where);
			
		core\tpl::output('goods_list', $goods_list);
		core\tpl::output('page', $model_goods->showpage(2));        
		core\tpl::output('search', $_GET);
		core\tpl::showpage('distributor_goodsimport');
	}
	
	/*分销商品导入动作*/
	public function importgoodsOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_distributor = model('distributor');
		
		if(empty($_POST['gid']) && empty($_GET['gid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=goodsimport');
		}
		
		$gooids = array();
		if(!empty($_POST['gid'])){
			$gooids = $_POST['gid'];
		}else{
			$gooids[] = $_GET['gid'];
		}
		
		//检测是否已经加入
		$model_goods = model('goods');
		$condition['is_distribute'] = 1;
		$condition['goods_commonid'] = array('in',$gooids);
		$goods_list = $model_goods->getGoodsCommonList($condition);
		if(!empty($goods_list)){
			$error_mes = '';
			foreach($goods_list as $value){
				$error_mes .= '<br />'.$value['goods_name'];
			}
			error($lang['good_info_again'].$error_mes,'index.php?act='.$_GET['act'].'&op=goodsimport');
		}
		
		$flag = true;
		$model_distributor->beginTransaction();
		
		$adddata = array();
		foreach($gooids as $id){
			$adddata[] = array(
				'good_id'=>$id
			);
		}
		
		$add = $model_distributor->addAll('distributor_goods',$adddata);
		$flag = $flag && $add;
		
		$updatedata = array('is_distribute'=>1);
		$where['goods_commonid'] = array('in',$gooids);
		$set = $model_distributor->editInfo('goods_common',$updatedata,$where);
		$flag = $flag && $set;
		if ($flag){
			$model_distributor->commit();
			success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=goodslist');
		}else {
			$model_distributor->rollback();
			error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=goodsimport');
		}
	}
	
	/*分销商品删除动作*/
	public function delgoodsOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		$model_distributor = model('distributor');
		
		if(empty($_POST['gid']) && empty($_GET['gid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=goodslist');
		}
		
		$gooids = array();
		if(!empty($_POST['gid'])){
			$gooids = $_POST['gid'];
		}else{
			$gooids[] = $_GET['gid'];
		}
		
		$flag = true;
		$model_distributor->beginTransaction();
		
		$condition['good_id'] = array('in', $gooids);
		$del = $model_distributor->delInfo('distributor_goods',$condition);
		$flag = $flag && $del;
		
		$updatedata = array('is_distribute'=>0);
		$where['goods_commonid'] = array('in',$gooids);
		$set = $model_distributor->editInfo('goods_common',$updatedata,$where);
		$flag = $flag && $set;
		if ($flag){
			$model_distributor->commit();
			success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=goodslist');
		}else {
			$model_distributor->rollback();
			error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=goodslist');
		}
	}
	
	/*编辑分销商品*/
	public function editgoodsOp(){
		core\language::read('goods');
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		
		if (chksubmit()){
			if(empty($_POST['itemid'])){
				error($lang['not_info_id']);
			}
			
			$where['item_id'] = intval($_POST['itemid']);
			$update_array = array(
				'good_profit'=>empty($_POST['profit']) ? 100 : number_format($_POST['profit'],2,'.',''),
				'good_dis_commission'=>empty($_POST['discommission']) ? '' : json_encode($_POST['discommission'], JSON_UNESCAPED_UNICODE),
				'good_team_commission'=>empty($_POST['teamcommission']) ? '' : json_encode($_POST['teamcommission'], JSON_UNESCAPED_UNICODE),
				'good_status'=>1
			);
			
			$result = $model_distributor->editInfo('distributor_goods',$update_array,$where);
			if ($result === true){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			if(empty($_GET['gid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=goodslist');
			}
		
			$condition['good_id'] = $_GET['gid'];
			$good_info = $model_distributor->getInfoOne('distributor_goods',$condition);
			
			if(empty($good_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=goodslist');
			}
			
			$dis_setting = $model_distributor->getInfoOne('distributor_setting','','dis_bonus_level,dis_bonus_self,public_bonus_level');
			$commission_setting = $model_distributor->getInfoOne('distributor_commission','');
			
			if(empty($commission_setting)){
				$commission_setting = array(
					'profit'=>100,
					'dis_commission'=>'',
					'public_commission'=>''
				);
				$itemid = $model_distributor->addInfo('distributor_commission',$commission_setting);
			}
			
			if(empty($good_info['good_dis_commission'])){
				$good_info['good_dis_commission'] = $commission_setting['dis_commission'] ? json_decode($commission_setting['dis_commission'], true) : array();
			}else{
				$good_info['good_dis_commission'] = json_decode($good_info['good_dis_commission'], true);
			}
			
			if(empty($good_info['good_public_commission'])){
				$good_info['good_public_commission'] = $commission_setting['public_commission'] ? json_decode($commission_setting['public_commission'], true) : array();
			}else{
				$good_info['good_public_commission'] = json_decode($good_info['good_public_commission'], true);
			}
			
			if(empty($good_info['good_team_commission'])){
				$good_info['good_team_commission'] = $commission_setting['team_commission'] ? json_decode($commission_setting['team_commission'], true) : array();
			}else{
				$good_info['good_team_commission'] = json_decode($good_info['good_team_commission'], true);
			}
			
			$good_info['good_profit'] = $good_info['good_profit']>0 ? $good_info['good_profit'] : $commission_setting['profit'];
			
			//获取分销商级别
			$level_list = $model_distributor->getInfoList('distributor_level','',30,'level_addtime asc','level_id,level_name');
			$level_ids = array();
			foreach($level_list as $value){
				$level_ids[] = $value['level_id'];
			}
			
			//获取商品信息
			$model_goods = model('goods');
        	$goods_detail = $model_goods->getGoodeCommonInfo(array('goods_commonid'=>$_GET['gid']),'goods_name,goods_image,goods_price,goods_costprice');
			$goods_detail['goods_image'] = thumb($goods_detail, '60');
			$goods_detail['goods_profit'] = number_format(($goods_detail['goods_price'] - $goods_detail['goods_costprice']),2,'.','');
			$commonid = $_GET['gid'];
			$goodscommon_list = $model_goods->getGoodeCommonInfoByID($commonid, 'spec_name');
			$goods_list = $model_goods->getGoodsList(array('goods_commonid' => $commonid), 'goods_id,goods_spec,store_id,goods_price,goods_serial,goods_storage,goods_image');
			
			if (!empty($goodscommon_list) && !empty($goods_list)) {
				$spec_name = array_values((array) unserialize($goodscommon_list['spec_name']));
				foreach ($goods_list as $key => $val) {
					$goods_spec = array_values((array) unserialize($val['goods_spec']));
					$spec_array = array();
					foreach ($goods_spec as $k => $v) {
						$spec_array[] = '<div class="goods_spec">' . $spec_name[$k] . lang('nc_colon') . '<em title="' . $v . '">' . $v . '</em>' . '</div>';
					}
					$goods_list[$key]['goods_image'] = thumb($val, '60');
					$goods_list[$key]['goods_spec'] = implode('', $spec_array);
					$goods_list[$key]['goods_profit'] = number_format(($val['goods_price'] - $goods_detail['goods_costprice']),2,'.','');
				}
			}
			
			//获得股东类型列表
			$team_list = $model_distributor->getInfoList('distributor_team','',30,'team_invitenum asc,team_addtime asc');
			core\tpl::output('setting',$dis_setting);
			core\tpl::output('good_info',$good_info);
			core\tpl::output('goods_detail',$goods_detail);
			core\tpl::output('team_list',$team_list);
			core\tpl::output('goods_list',$goods_list);
			core\tpl::output('level_list',$level_list);
			core\tpl::output('level_ids',$level_ids);
			core\tpl::output('top_link', $this->sublink($this->setting_links, 'setting_commission'));
			core\tpl::showpage('distributor_goods_edit');
		}
	}
	
	/**
	 * 基本设置
	 */
	public function settingOp() {
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		
        $model_setting = model('setting');
		if (chksubmit()){
			$update_array = array();
			$update_array['distributor_isuse'] = intval($_POST['isuse']);
			$result = $model_setting->updateSetting($update_array);
			if ($result === true){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			$setting_list = $model_setting->getListSetting();
			$model_distributor = model('distributor');
			$dis_setting = $model_distributor->getInfoOne('distributor_setting','');
			if(empty($dis_setting)){
				$Data = array(
					'dis_bonus_level'=>1
				);
				$itemid = $model_distributor->addInfo('distributor_setting',$Data);
				
				//增加默认分销商级别
				$level_data = array(
					'level_name'=>'普通分销商',
					'level_come_type'=>6,
					'level_come_value'=>'',
					'level_thumb'=>'',
					'level_default'=>1,
					'level_addtime'=>time()
				);
				$levelid = $model_distributor->addInfo('distributor_level',$level_data);
				
				//增加公排默认分区
				$area_data = array(
					'item_name'=>'普通区',
					'item_condition'=>0,
					'item_default'=>1
				);
				$areaid = $model_distributor->addInfo('distributor_gp_area',$area_data);
			}
			
			core\tpl::output('setting',$setting_list);
			core\tpl::output('top_link', $this->sublink($this->setting_links, 'setting'));
			core\tpl::showpage('distributor_setting');
		}
	}
	
	/**
	 * 分销设置
	 */
	public function setting_disOp() {
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		
		if (chksubmit()){
			if(empty($_POST['itemid'])){
				error($lang['not_info_id']);
			}
			if(empty($_POST['cometype'])){
				error($lang['submit_no_permission']);
			}
			
			$where['item_id'] = intval($_POST['itemid']);
			$update_array = array(
				'dis_come_type'=> intval($_POST['cometype']),
				'dis_goods_open'=> intval($_POST['goodsopen']),
				'dis_bonus_level'=> intval($_POST['bonuslevel']),
				'dis_bonus_self'=> intval($_POST['bonusself']),
				'dis_name'=> trim($_POST['name']),
				'dis_bonus_name'=> trim($_POST['bonusname']),
				'member_inviter'=> trim($_POST['memberinviter'])
			);
			
			$result = $model_distributor->editInfo('distributor_setting',$update_array,$where);
			if ($result === true){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			$dis_setting = $model_distributor->getInfoOne('distributor_setting','');
			
			//获取分销商级别数
			$level_count = $model_distributor->getCount('distributor_level','');
			
			core\tpl::output('setting',$dis_setting);
			core\tpl::output('level_count',$level_count);
			core\tpl::output('top_link', $this->sublink($this->setting_links, 'setting_dis'));
			core\tpl::showpage('distributor_setting_dis');
		}
	}
	
	/**
	 * 公排设置
	 */
	public function setting_publicOp() {
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		
		if (chksubmit()){
			if(empty($_POST['itemid'])){
				error($lang['not_info_id']);
			}
			if(empty($_POST['cometype'])){
				error($lang['submit_no_permission']);
			}
			
			$where['item_id'] = intval($_POST['itemid']);
			$dis_setting = $model_distributor->getInfoOne('distributor_setting',$where,'public_status');
			
			if(intval($_POST['multi'])==1){
				if(in_array($_POST['cometype'],array(1,2))){
					if(is_numeric($_POST['comevalue'][$_POST['cometype']])){
						$_POST['comevalue'][$_POST['cometype']] = number_format($_POST['comevalue'][$_POST['cometype']],2,'.','');
					}else{
						error($lang['comevalue_is_number']);
					}
				}elseif($_POST['cometype']==3){
					if(!empty($_POST['comevalue'][$_POST['cometype']])){
						$_POST['comevalue'][$_POST['cometype']] = str_replace(',,',',',$_POST['comevalue'][$_POST['cometype']]);
						$_POST['comevalue'][$_POST['cometype']] = trim($_POST['comevalue'][$_POST['cometype']],',');
					}else{
						error($lang['comevalue_is_notnull']);
					}
				}elseif($_POST['cometype']==4){
					$_POST['comevalue'][$_POST['cometype']] = '';
				}
				$_POST['returnvalue'][$_POST['returntype']] = '';
			}else{
				if(in_array($_POST['returntype'],array(1,2))){
					if(is_numeric($_POST['returnvalue'][$_POST['returntype']])){
						$_POST['returnvalue'][$_POST['returntype']] = number_format($_POST['returnvalue'][$_POST['returntype']],2,'.','');
					}else{
						error($lang['returnvalue_is_number']);
					}
				}elseif($_POST['returntype']==3){
					if(!empty($_POST['returnvalue'][$_POST['returntype']])){
						$_POST['returnvalue'][$_POST['returntype']] = str_replace(',,',',',$_POST['returnvalue'][$_POST['returntype']]);
						$_POST['returnvalue'][$_POST['returntype']] = trim($_POST['returnvalue'][$_POST['returntype']],',');
					}else{
						error($lang['returnvalue_is_notnull']);
					}
				}elseif(in_array($_POST['returntype'],array(4,6))){
					$_POST['returnvalue'][$_POST['returntype']] = '';
				}
				$_POST['comevalue'][$_POST['cometype']] = '';
			}
			
			$update_array = array(
				'public_open'=> intval($_POST['open']),
				'public_bonus_level'=>intval($_POST['bonuslevel']),
				'public_multi'=> intval($_POST['multi']),
				'public_come_type'=> intval($_POST['cometype']),
				'public_come_value'=> empty($_POST['comevalue'][$_POST['cometype']]) ? '' : trim($_POST['comevalue'][$_POST['cometype']]),
				'public_out_level'=> intval($_POST['outlevel']),
				'public_return_type'=> intval($_POST['returntype']),
				'public_return_value'=> empty($_POST['returnvalue'][$_POST['returntype']]) ? '' : trim($_POST['returnvalue'][$_POST['returntype']]),
			);
			if($dis_setting['public_status']==0){
				$update_array['public_times'] = intval($_POST['times']);
			}
			
			$result = $model_distributor->editInfo('distributor_setting',$update_array,$where);
			if ($result === true){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			$dis_setting = $model_distributor->getInfoOne('distributor_setting','');
			$dis_setting['come_value'] = $dis_setting['return_value'] = array(
				1=>'',
				2=>'',
				3=>'',
				4=>'',
			);
			$dis_setting['come_value'][$dis_setting['public_come_type']] = $dis_setting['public_come_value'];
			$dis_setting['return_value'][$dis_setting['public_return_type']] = $dis_setting['public_return_value'];
			$model_goods = model('goods');
			//参与公排产品
			$goods_list_come = array();
			if($dis_setting['public_come_type'] == 3 && !empty($dis_setting['public_come_value'])){
				$where_come['is_distribute'] = 1;
				$where_come['goods_commonid'] = array('in',explode(',',$dis_setting['public_come_value']));
				$goods_list_come = $model_goods->getGoodsCommonList($where_come,'goods_commonid,goods_name');
			}
			
			//出局后重新排位门槛
			$goods_list_return = array();
			if($dis_setting['public_return_type'] == 3 && !empty($dis_setting['public_return_value'])){
				$where_return['is_distribute'] = 1;
				$where_return['goods_commonid'] = array('in',explode(',',$dis_setting['public_return_value']));
				$goods_list_return = $model_goods->getGoodsCommonList($where_return,'goods_commonid,goods_name');
			}
			
			// 一级分类列表
       		$gc_list = model('goods_class')->getGoodsClassListByParentId(0);
        	core\tpl::output('gc_list', $gc_list);
			core\tpl::output('goods_list_come', $goods_list_come);
			core\tpl::output('goods_list_return', $goods_list_return);
			core\tpl::output('setting',$dis_setting);
			core\tpl::output('top_link', $this->sublink($this->setting_links, 'setting_public'));
			core\tpl::showpage('distributor_setting_public');
		}
	}
	
	/**
	 * 佣金明细设置
	 */
	public function setting_commissionOp() {
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		
		if (chksubmit()){
			if(empty($_POST['itemid'])){
				error($lang['not_info_id']);
			}
			
			$where['item_id'] = intval($_POST['itemid']);
			$update_array = array(
				'profit'=>empty($_POST['profit']) ? 100 : number_format($_POST['profit'],2,'.',''),
				'dis_commission'=>empty($_POST['discommission']) ? '' : json_encode($_POST['discommission'], JSON_UNESCAPED_UNICODE),
				'team_commission'=>empty($_POST['teamcommission']) ? '' : json_encode($_POST['teamcommission'], JSON_UNESCAPED_UNICODE),
				'public_commission'=>empty($_POST['publiccommission']) ? '' : json_encode($_POST['publiccommission'], JSON_UNESCAPED_UNICODE)
			);
			
			$result = $model_distributor->editInfo('distributor_commission',$update_array,$where);
			if ($result === true){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			$dis_setting = $model_distributor->getInfoOne('distributor_setting','','dis_bonus_level,dis_bonus_self,public_bonus_level');
			$commission_setting = $model_distributor->getInfoOne('distributor_commission','');
			
			if(empty($commission_setting)){
				$commission_setting = array(
					'profit'=>100,
					'dis_commission'=>'',
					'public_commission'=>'',
					'team_commission'=>'',
				);
				$itemid = $model_distributor->addInfo('distributor_commission',$commission_setting);
				$commission_setting['item_id'] = $itemid;
			}
			
			$commission_setting['dis_commission'] = $commission_setting['dis_commission'] ? json_decode($commission_setting['dis_commission'], true) : array();
			$commission_setting['public_commission'] = $commission_setting['public_commission'] ? json_decode($commission_setting['public_commission'], true) : array();
			$commission_setting['team_commission'] = $commission_setting['team_commission'] ? json_decode($commission_setting['team_commission'], true) : array();
			//获取分销商级别
			$level_list = $model_distributor->getInfoList('distributor_level','',30,'level_addtime asc','level_id,level_name');
			$level_ids = array();
			foreach($level_list as $value){
				$level_ids[] = $value['level_id'];
			}
			
			//获得公排分区列表
			$result = $model_distributor->getInfoList('distributor_gp_area','','','item_id asc','item_id,item_name');
			$public_area = array();
			foreach($result as $r){
				$public_area[$r['item_id']] = $r['item_name'];
			}
			
			//获得股东类型列表
			$team_list = $model_distributor->getInfoList('distributor_team','',30,'team_invitenum asc,team_addtime asc');
			
			core\tpl::output('setting',$dis_setting);
			core\tpl::output('public_area',$public_area);
			core\tpl::output('commission_setting',$commission_setting);
			core\tpl::output('team_list',$team_list);
			core\tpl::output('level_list',$level_list);
			core\tpl::output('level_ids',$level_ids);
			core\tpl::output('top_link', $this->sublink($this->setting_links, 'setting_commission'));
			core\tpl::showpage('distributor_setting_commission');
		}
	}
	
	/**
	 * 推广二维码背景图设置
	 */
	public function setting_codebgOp() {
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		
		if (chksubmit()){
			if(empty($_POST['itemid'])){
				error($lang['not_info_id']);
			}
			
			if($_POST['qrcodewidth']>370){
				error('二维码宽度不得超过370px');
			}
			
			$where['item_id'] = intval($_POST['itemid']);
			$update_array = array(
				'qrcode_bg'=> str_replace(UPLOAD_SITE_URL,'',trim($_POST['thumb'])),
				'qrcode_top'=>intval($_POST['qrcodetop']),
				'qrcode_left'=>intval($_POST['qrcodeleft']),
				'qrcode_width'=>intval($_POST['qrcodewidth']),
				'title_color'=>strtoupper(trim($_POST['titlecolor']))
			);
			
			$result = $model_distributor->editInfo('distributor_other_setting',$update_array,$where);
			if ($result === true){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			$dis_setting = $model_distributor->getInfoOne('distributor_other_setting','');
			if(empty($dis_setting)){
				$dis_setting = array(
					'qrcode_bg'=>'',
					'qrcode_top'=>350,
					'qrcode_left'=>100,
					'title_color'=>'#FFFFFF',
					'qrcode_width'=>'370'
				);
				$itemid = $model_distributor->addInfo('distributor_other_setting',$dis_setting);
				$dis_setting['item_id'] = $itemid;
			}
			core\tpl::output('setting',$dis_setting);
			core\tpl::output('top_link', $this->sublink($this->setting_links, 'setting_codebg'));
			core\tpl::showpage('distributor_setting_codebg');
		}
	}
	
	/*
	*ajax获取数据
	*/
	public function ajaxOp(){
		if(empty($_GET['branch'])){
			$data = array(
				'status'=>0,
				'html'=>''
			);
			echo json_encode($data, JSON_UNESCAPED_UNICODE);
			exit;
		}
		
		switch($_GET['branch']){
			case 'get_goodlist':
				$model_goods = model('goods');
				$where = array();
				$where['is_distribute'] = 1;
				if (!empty($_GET['keyword'])) {
					$where['goods_name'] = array('like', '%' . trim($_GET['keyword']) . '%');
				}
				if (!empty($_GET['cate_id'])) {
					$choose_gcid = $_GET['cate_id'];
        			$gccache_arr = model('goods_class')->getGoodsclassCache($choose_gcid, 3);
					$where['gc_id_' . $gccache_arr['showclass'][$choose_gcid]['depth']] = $choose_gcid;
				}
				$goods_list = array();
				$goods_list = $model_goods->getGoodsCommonList($where,'goods_commonid,goods_name');
				if(empty($goods_list)){
					$data = array(
						'status'=>0,
						'html'=>''
					);
					echo json_encode($data, JSON_UNESCAPED_UNICODE);
					exit;
				}
				$html = '';
				foreach($goods_list as $v){
					$html .= '<option value="'.$v['goods_commonid'].'">'.'['.$v['goods_commonid'].']'.$v['goods_name'].'</option>';
				}
				$data = array(
					'status'=>0,
					'html'=>$html
				);
				echo json_encode($data, JSON_UNESCAPED_UNICODE);
				exit;
			break;
		}
	}
	
	/*
	*分销商级别
	*/
	public function levellistOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		
        $level_list = $model_distributor->getInfoList('distributor_level','',30,'level_addtime asc');
		$where['is_distribute'] = 1;
		
		$model_goods = model('goods');
		$goods_list = array();
		$result = $model_goods->getGoodsCommonList($where,'goods_commonid,goods_name');
		foreach($result as $value){
			$goods_list[$value['goods_commonid']] = '['.$value['goods_commonid'].']'.$value['goods_name'];
		}
		
		core\tpl::output('level_list',$level_list);
		core\tpl::output('goods_list',$goods_list);
		core\tpl::output('show_page',$model_distributor->showpage('2'));
		core\tpl::showpage('distributor_level_list');
	}
	
	/*
	*添加分销商级别
	*/
	public function addlevelOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		$dis_setting = $model_distributor->getInfoOne('distributor_setting','','dis_come_type,dis_bonus_level');
		if($dis_setting['dis_come_type']==4){
			error($lang['addlevel_no_permission']);
		}
		if (chksubmit()){
			if(empty($_POST['name'])){
				error($lang['dis_name_is_not_null']);
			}
			
			if(empty($_POST['cometype'])){
				error($lang['submit_no_permission']);
			}
			
			if($_POST['cometype'] != $dis_setting['dis_come_type']){
				error($lang['submit_no_permission']);
			}
			
			/*检测成为分销商门槛限制具体值*/
			if(in_array($_POST['cometype'],array(1,2,5))){
				if(is_numeric($_POST['comevalue'])){
					$_POST['comevalue'] = number_format($_POST['comevalue'],2,'.','');
				}else{
					error($lang['dis_comevalue_is_number']);
				}
			}elseif($_POST['cometype']==3){
				if(!empty($_POST['comevalue'])){
					$_POST['comevalue'] = str_replace(',,',',',$_POST['comevalue']);
					$_POST['comevalue'] = trim($_POST['comevalue'],',');
				}else{
					error($lang['dis_comevalue_is_notnull']);
				}
			}
			
			$update_array = array(
				'level_name'=>trim($_POST['name']),
				'level_come_type'=>intval($_POST['cometype']),
				'level_come_value'=>empty($_POST['comevalue']) ? '' : $_POST['comevalue'],
				'level_thumb'=>str_replace(UPLOAD_SITE_URL,'',trim($_POST['thumb'])),
				'level_addtime'=>time()
			);
			
			/*检测购买获得佣金明细*/
			if($_POST['cometype']==5){
				$comecommission = array();
				foreach($_POST['comecommission'] as $key=>$val){
					$comecommission[$key] = empty($val) ? 0 : number_format($val,2,'.','');
				}
				$update_array['level_come_commission'] = empty($comecommission) ? '' : json_encode($comecommission,JSON_UNESCAPED_UNICODE);
			}
			
			if(!in_array($_POST['updatetype'],array(3,5))){
				error($lang['submit_no_permission']);				
			}else{
				if(empty($_POST['updatevalue'])){
					error($lang['dis_updatevalue_is_notnull']);
				}
				if($_POST['updatetype']==5){
					$_POST['updatevalue'] = number_format($_POST['updatevalue'],2,'.','');
					$updatecommission = array();
					foreach($_POST['updatecommission'] as $key=>$val){
						$updatecommission[$key] = empty($val) ? 0 : number_format($val,2,'.','');
					}
				}else{
					$updatecommission = array();
					$_POST['updatevalue'] = str_replace(',,',',',$_POST['updatevalue']);
					$_POST['updatevalue'] = trim($_POST['updatevalue'],',');
				}
				$update_array['level_update_type'] = intval($_POST['updatetype']);
				$update_array['level_update_value'] = $_POST['updatevalue'];
				$update_array['level_update_commission'] = empty($updatecommission) ? '' : json_encode($updatecommission,JSON_UNESCAPED_UNICODE);
			}
			
			$result = $model_distributor->addInfo('distributor_level',$update_array);
			if ($result){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			// 一级分类列表
       		$gc_list = model('goods_class')->getGoodsClassListByParentId(0);
        	core\tpl::output('gc_list', $gc_list);
			core\tpl::output('setting',$dis_setting);
			core\tpl::showpage('distributor_level_add');
		}
	}
	
	/*
	*修改分销商级别
	*/
	public function editlevelOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
        $model_distributor = model('distributor');
		$dis_setting = $model_distributor->getInfoOne('distributor_setting','','dis_come_type,dis_bonus_level');
		if (chksubmit()){
			if(empty($_POST['lid'])){
				error($lang['not_info_id']);
			}
			
			$condition['level_id'] = intval($_POST['lid']);
			$level_info = $model_distributor->getInfoOne('distributor_level',$condition,'level_default');
			if(empty($level_info)){
				error($lang['info_not_exist']);
			}
			
			if(empty($_POST['name'])){
				error($lang['dis_name_is_not_null']);
			}
			
			if(empty($_POST['cometype'])){
				error($lang['submit_no_permission']);
			}
			
			if($level_info['level_default']==0){//不是第一个分销商级别的  则判断门槛是否和总门槛一致
				if($_POST['cometype'] != $dis_setting['dis_come_type']){
					error($lang['submit_no_permission']);
				}
			}
			
			
			/*检测成为分销商门槛限制具体值*/
			if(in_array($_POST['cometype'],array(1,2,5))){
				if(is_numeric($_POST['comevalue'])){
					$_POST['comevalue'] = number_format($_POST['comevalue'],2,'.','');
				}else{
					error($lang['dis_comevalue_is_number']);
				}
			}elseif($_POST['cometype']==3){
				if(!empty($_POST['comevalue'])){
					$_POST['comevalue'] = str_replace(',,',',',$_POST['comevalue']);
					$_POST['comevalue'] = trim($_POST['comevalue'],',');
				}else{
					error($lang['dis_comevalue_is_notnull']);
				}
			}elseif($level_info['level_default']==1 && ($_POST['cometype']==0 || $_POST['cometype']==4)){//第一个分销商级别无门槛或购买任意商品
				$_POST['comevalue'] = '';
			}
			
			$update_array = array(
				'level_name'=>trim($_POST['name']),
				'level_come_type'=>intval($_POST['cometype']),
				'level_come_value'=>empty($_POST['comevalue']) ? '' : $_POST['comevalue'],
				'level_thumb'=>str_replace(UPLOAD_SITE_URL,'',trim($_POST['thumb']))
			);
			
			/*检测购买获得佣金明细*/
			if($_POST['cometype']==5){
				$comecommission = array();
				foreach($_POST['comecommission'] as $key=>$val){
					$comecommission[$key] = empty($val) ? 0 : number_format($val,2,'.','');
				}
				$update_array['level_come_commission'] = empty($comecommission) ? '' : json_encode($comecommission,JSON_UNESCAPED_UNICODE);
			}
			
			if($level_info['level_default']==1){//第一个分销商级别无升级设置
				$_POST['updatetype'] = 0;
				$update_array['level_update_value'] = '';
				$update_array['level_update_commission'] = '';
			}else{
				if(!in_array($_POST['updatetype'],array(3,5))){
					error($lang['submit_no_permission']);				
				}else{
					if(empty($_POST['updatevalue'])){
						error($lang['dis_updatevalue_is_notnull']);
					}
					if($_POST['updatetype']==5){
						$_POST['updatevalue'] = number_format($_POST['updatevalue'],2,'.','');
						$updatecommission = array();
						foreach($_POST['updatecommission'] as $key=>$val){
							$updatecommission[$key] = empty($val) ? 0 : number_format($val,2,'.','');
						}
					}else{
						$updatecommission = array();
						$_POST['updatevalue'] = str_replace(',,',',',$_POST['updatevalue']);
						$_POST['updatevalue'] = trim($_POST['updatevalue'],',');
					}
					$update_array['level_update_type'] = intval($_POST['updatetype']);
					$update_array['level_update_value'] = $_POST['updatevalue'];
					$update_array['level_update_commission'] = empty($updatecommission) ? '' : json_encode($updatecommission,JSON_UNESCAPED_UNICODE);
				}
			}
			
			$result = $model_distributor->editInfo('distributor_level',$update_array,$condition);
			if ($result){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			if(empty($_GET['lid'])){
				error($lang['not_info_id']);
			}
			$condition['level_id'] = intval($_GET['lid']);
			$level_info = $model_distributor->getInfoOne('distributor_level',$condition,'*');
			if(empty($level_info)){
				error($lang['info_not_exist']);
			}
			
			
			$model_goods = model('goods');
			$goods_list_come = $goods_list_update = $comecommission = $updatecommission = array();
			if($level_info['level_come_type'] == 3 && !empty($level_info['level_come_value'])){
				$where_come['is_distribute'] = 1;
				$where_come['goods_commonid'] = array('in',explode(',',$level_info['level_come_value']));
				$goods_list_come = $model_goods->getGoodsCommonList($where_come,'goods_commonid,goods_name');
			}
			
			if($level_info['level_come_type'] == 5 && !empty($level_info['level_come_commission'])){
				$comecommission = json_decode($level_info['level_come_commission'], true);
			}
			
			if($level_info['level_update_type'] == 3 && !empty($level_info['level_update_value'])){
				$where_update['is_distribute'] = 1;
				$where_update['goods_commonid'] = array('in',explode(',',$level_info['level_update_value']));
				$goods_list_update = $model_goods->getGoodsCommonList($where_update,'goods_commonid,goods_name');
			}
			
			if($level_info['level_update_type'] == 5 && !empty($level_info['level_update_commission'])){
				$updatecommission = json_decode($level_info['level_update_commission'], true);
			}
			
			
			// 一级分类列表
       		$gc_list = model('goods_class')->getGoodsClassListByParentId(0);
			core\tpl::output('level_info', $level_info);
			core\tpl::output('gc_list', $gc_list);
			core\tpl::output('goods_list_come', $goods_list_come);
			core\tpl::output('goods_list_update', $goods_list_update);
			core\tpl::output('comecommission', $comecommission);
			core\tpl::output('updatecommission', $updatecommission);
			core\tpl::output('setting',$dis_setting);
			if($level_info['level_default']==1){
				core\tpl::showpage('distributor_level_editdefault');
			}else{
				core\tpl::showpage('distributor_level_edit');
			}
		}
	}
	
	/*
	*删除分销商级别
	*/
	public function dellevelOp(){
		core\language::read('distributor');
		$lang = core\language::getLangContent();
		
		if(empty($_GET['lid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=levellist');
		}
		
		if (intval($_GET['lid']) > 0){
			$model_distributor = model('distributor');
			$condition = array('level_id'=>intval($_GET['lid']));
			$level_info = $model_distributor->getInfoOne('distributor_level',$condition);
			if(empty($level_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=levellist');
			}
			
			if($level_info['level_default']==1){
				error($lang['first_dislevel_not_delete'],'index.php?act='.$_GET['act'].'&op=levellist');
			}
			
			$result = $model_distributor->delInfo('distributor_level',$condition);
			
			//delete images
			if(!empty($level_info['level_thumb'])){
				@unlink(BASE_UPLOAD_PATH.$level_info['level_thumb']);
			}
			
			success($lang['nc_common_del_succ'],'index.php?act='.$_GET['act'].'&op=levellist');
		}else {
			error($lang['nc_common_del_fail'],'index.php?act='.$_GET['act'].'&op=levellist');
		}
	}
	
	/*清除会员二维码海报*/
	public function clear_qrcodeOp(){
		$dirName = BASE_UPLOAD_PATH . DS . ATTACH_POSTER . DS;
		if(file_exists($dirName) && $handle=opendir($dirName)){
			while(false!==($item = readdir($handle))){
				if($item!= "." && $item != ".."){
					unlink($dirName.'/'.$item);
				}
			}
			closedir( $handle);
		}
		
		$dirName = BASE_UPLOAD_PATH . DS . ATTACH_QRCODE . DS;
		if(file_exists($dirName) && $handle=opendir($dirName)){
			while(false!==($item = readdir($handle))){
				if($item!= "." && $item != ".."){
					unlink($dirName.'/'.$item);
				}
			}
			closedir( $handle);
		}
		success('清除成功','index.php?act='.$_GET['act'].'&op=setting_codebg');
	}
}

