<?php
namespace common\logic;
use core;
defined('SAFE_CONST') or exit('Access Invalid!');
class distributor
{
	/**
     * 获得分销商级别
     * @return array()
     */
    public function getLevelArr($field='*',$orderby = 'level_id asc')
    {
        $model_dis = model('distributor');
		$result = $model_dis->getInfoList('distributor_level','','',$orderby,$field);
		$dis_levels = array();
		foreach($result as $level){
			$dis_levels[$level['level_id']] = $level;
		}
		//ksort($dis_levels);
		return $dis_levels;
    }
	
	/*	
	*用户注册时调用	deal_distributor($member_id,array('register'=>1));
	*一次性消费,总消费调用,购买商品	deal_distributor($member_id,'',array('register'=>0,'costone'=>$costone,'costall'=>$costall,'commonids'=>$commonids));
	返回值：10 会员不存在 20 增加失败 30增加成功 40 升级失败 50 升级成功 
	*/
	public function deal_distributor($member_id,$extend = array()){
		
		$model_member = model('member');
		$model_dis = model('distributor');
		$member_info = $model_member->getMemberInfo(array('member_id'=>$member_id), 'member_id,is_distributor,inviter_id,member_name');
		
		if(empty($member_info)){
			return 10;
		}
		
		$action = '';
		if($member_info['is_distributor']==1){
			$action = 'update';
		}else{
			$action = 'add';
		}
		
		
		$distributor_data = array();
		$level_id = 0;
		if($action == 'add'){
			//获取分销商进入门槛
			$dis_config = $model_dis->getInfoOne('distributor_setting','','dis_come_type');
			switch($dis_config['dis_come_type']){
				case 1://一次性消费
					$level_id = $this->getMaxLevelIDByCost($extend['costone'],1);
				break;
				case 2://总消费
					$level_id = $this->getMaxLevelIDByCost($extend['costall'],2);
				break;
				case 3://指定商品
					$level_id = $this->getMaxLevelIDByGoods($extend['commonids']);
				break;
				case 4://购买任意商品
					$condition['level_default'] = 1;
					$type_array = array();
					if(!empty($extend['commonids'])){
						$type_array[] = 4;
					}else{
						if(!empty($extend['register'])){//注册来的
							$type_array[] = 6;
						}
					}
					$condition['level_come_type'] = array('in',$type_array);
					$dis_level_info = $model_dis->getInfoOne('distributor_level',$condition, 'level_id');
					$level_id = empty($dis_level_info['level_id']) ? 0 : $dis_level_info['level_id'];
				break;
			}
			
			if(empty($level_id)){
				return 20;
			}
			
			$flag = $this->add_distributor_account($member_info,$level_id);
			if($flag){
				$member_info['is_distributor']=1;
				$extend['register'] = 1;
				$f = $this->deal_public($member_info, $extend);
			}
			
			return $flag ? 30 : 20;
		}else{//分销商升级
			if($member_info['is_distributor']==0){
				return 40;
			}
			
			$distributor_info = $model_dis->getInfoOne('distributor_account',array('member_id'=>$member_id),'level_id,distributor_id');
			if(empty($distributor_info)){
				return 40;
			}
			
			$level_id_0 = $level_id_1 = 0;
			if(!empty($extend['commonids'])){
				$level_id_0 = $this->getMaxLevelIDByGoods($extend['commonids'],1);
			}
			
			if(!empty($extend['costall'])){
				$level_id_1 = $this->getMaxLevelIDByCost($extend['costall'],2,1);
			}
			
			$level_id = $level_id_0>=$level_id_1 ? $level_id_0 : $level_id_1;
			
			if($level_id>$distributor_info['level_id']){
				$flag = $model_dis->editInfo('distributor_account',array('level_id'=>$level_id), array('distributor_id'=>$distributor_info['distributor_id']));
			}
			
			$f = $this->deal_public($member_info, $extend);
		}
	}
	
	/**
     * 获得分销商级别最大值（按消费额）
	 * @one int 1 一次性消费 2 总消费
	 * @type int 0 增加分销商 1 升级分销商
     * @return int
     */
    public function getMaxLevelIDByCost($cost,$one=1,$type=0)
    {
		$levelid = 0;
        $dis_levels = $this->getLevelArr('level_id,level_come_type,level_come_value,level_update_type,level_update_value,level_default','level_id desc');
		foreach($dis_levels as $lid=>$level){
			if($type==0){
				if($level['level_come_type'] == $one && $level['level_come_value'] <= $cost){
					$levelid = $lid;
					break;
				}elseif($level['level_default'] == 1 && in_array($level['level_come_type'],array(6))){//第一个分销商级别判定
					$levelid = $lid;
					break;
				}
			}else{
				if($level['level_update_type'] == $one && $level['level_update_value'] <= $cost){
					$levelid = $lid;
					break;
				}
			}
			
		}
		
		return $levelid;
		
    }
	
    /**
     * 获得分销商级别最大值（按购买指定商品）
	 * @commonids array 商品commonid
     * @return int
     */
    public function getMaxLevelIDByGoods($commonids,$type=0)
    {
		$levelid = 0;
        $dis_levels = $this->getLevelArr('level_id,level_come_type,level_come_value,level_update_type,level_update_value,level_default');
		foreach($dis_levels as $lid=>$level){
			if($type==1){//升级使用
				if($level['level_update_type'] == 3){
					$level['level_update_value'] = trim($level['level_update_value'],',');
					$come_value = explode(',',$level['level_update_value']);
					
					$arr_temp = array_intersect($commonids,$come_value);
					
					if(!empty($arr_temp)){
						$levelid = $lid;
						break;
					}
					
				}
			}else{
				if($level['level_come_type'] == 3){
					$level['level_come_value'] = trim($level['level_come_value'],',');
					$come_value = explode(',',$level['level_come_value']);
					$arr_temp = array_intersect($commonids,$come_value);
					
					if(!empty($arr_temp)){
						$levelid = $lid;
						break;
					}
					
				}elseif($level['level_default'] == 1 && in_array($level['level_come_type'],array(6,4))){//第一个分销商级别判定
					$levelid = $lid;
					break;
				}
			}
		}
		
		return $levelid;
    }
	
	/*
	* 增加分销商
	*/	
	public function add_distributor_account($member_info,$level_id){
		if(empty($level_id)){
			return false;
		}
		
		$model_member = model('member');
		$model_dis = model('distributor');
		
		$member_data = $dis_account = array();
		
		if($member_info['inviter_id']>0){
			$inviter = $model_dis->getInfoOne('distributor_account',array('member_id'=>$member_info['inviter_id']), 'dis_path','distributor_id desc');
			if(empty($inviter)){
				$member_data['inviter_id'] = 0;
				$dis_account['inviter_id'] = 0;
				$dis_account['dis_path'] = '';
			}else{
				$dis_account['dis_path'] = empty($inviter['dis_path']) ? ','.$member_info['inviter_id'].',' : $inviter['dis_path'].$member_info['inviter_id'].',';
				$dis_account['inviter_id'] = $member_info['inviter_id'];
			}
		}
		
		$dis_account['member_id'] = $member_info['member_id'];
		$dis_account['level_id'] = $level_id;
		$dis_account['addtime'] = time();
		
		$flag = true;
		model()->beginTransaction();
		
		$distributor_id = $model_dis->addInfo('distributor_account',$dis_account);
		$flag = $flag && $distributor_id;
		
		$member_data['is_distributor'] = 1;
		$result = $model_member->editMember(array('member_id'=>$member_info['member_id']), $member_data);
		$flag = $flag && $result;
		
		if($flag){
			$model_dis->commit();
		}else{
			$model_dis->rollback();
		}
		return $flag;
	}
	
	/*
	*公排处理
	*/
	public function deal_public($member_info, $extend = array()){
		if(empty($member_info)){
			return true;
		}
		
		$model_dis = model('distributor');
		
		$dis_config = $model_dis->getInfoOne('distributor_setting','');
		if($dis_config['public_open']==0){
			return true;
		}
		
		if($member_info['is_distributor']==0){
			return true;
		}
		
		//获得应得公排分区
		$public_check = $model_dis->getInfoOne('distributor_gp',array('member_id'=>$member_info['member_id']),'status,area_id','ralate_id desc');
		if(empty($public_check)){
			$public_area = $model_dis->getInfoOne('distributor_gp_area',array('item_default'=>1),'item_id','item_id asc');
			if(empty($public_area)){
				return true;
			}
			$area_id = $public_area['item_id'];
		}else{
			if($member_info['inviter_id']==0){//顶级禁止多次排位
				return true;
			}
			$area_id = $public_check['area_id'];
			if($dis_config['public_multi']==0 && empty($extend['register'])){
				if($public_check['status']==1){
					return true;
				}else{
					if($dis_config['public_return_type']==6){
						return true;
					}
				}
			}
		}
		
		$eabled = 0;
		$goodids = array();
		$come_type = $dis_config['public_multi']==0 ? $dis_config['public_return_type'] : $dis_config['public_come_type'];
		$come_value = $dis_config['public_multi']==0 ? $dis_config['public_return_value'] : $dis_config['public_come_value'];
		if(empty($extend['register'])){//如果不是增加分销商时增加排位,则检测条件			
			switch($come_type){
				case 1://一次性消费
					$eabled = $extend['costone']>=$come_value ? 1 : 0;
				break;
				/*
				case 2:
					$eabled = $extend['costall']>=$come_value ? 1 : 0;
				break;
				*/
				case 3://购买指定商品
					$come_value = trim($come_value,',');
					$come_value_array = explode(',',$come_value);
					$arr_temp = array_intersect($extend['commonids'],$come_value_array);
					if(!empty($arr_temp)){
						$eabled = 1;
						$goodids = $arr_temp;
					}
				break;
				case 4://购买任意商品
					if(!empty($extend['commonids'])){						
						$eabled = 1;
						$come_value = trim($come_value,',');
						$come_value_array = explode(',',$come_value);
						$arr_temp = array_intersect($extend['commonids'],$come_value_array);
						if(!empty($arr_temp)){
							$goodids = $arr_temp;
						}
					}
				break;
			}
		}else{
			$eabled=1;
			if(!empty($extend['commonids']) && ($come_type==3 || $come_type==4)){
				$come_value = trim($come_value,',');
				$come_value_array = explode(',',$come_value);
				$arr_temp = array_intersect($extend['commonids'],$come_value_array);
				if(!empty($arr_temp)){
					$goodids = $arr_temp;
				}
			}
		}
		if($eabled==1){
			if(empty($goodids)){
				$flag = $this->add_public_account($member_info,$dis_config,$area_id);
			}else{
				if($dis_config['public_multi']==1){
					foreach($goodids as $goodid){
						$i=1;
						for($i=1;$i<=$extend['goods_num'][$goodid];$i++){
							$flag = $this->add_public_account($member_info,$dis_config,$area_id);
						}
					}
				}else{
					$flag = $this->add_public_account($member_info,$dis_config,$area_id);
				}
			}
		}else{
			return true;
		}
	}
	
	/*
	* 增加排位
	*/
	public function add_public_account($member_info,$dis_config=array(),$area_id){
		if(empty($member_info['member_id'])){
			return false;
		}
		
		$model_dis = model('distributor');
		$model_member = model('member');
		if(empty($dis_config)){
			$dis_config = $model_dis->getInfoOne('distributor_setting','');
		}
		
		//获得坐标、根id、公排上级id、公排上级父路径
		$position = $this->get_position($member_info['member_id'],$member_info['inviter_id'],$dis_config['public_times'],$area_id);
		
		if(empty($position)){
			return false;
		}
		
		$public = array();
		$public['member_id'] = $member_info['member_id'];
		$public['rootid'] = $position['rootid'];
		$public['area_id'] = $area_id;
		$public['inviterid'] = $member_info['inviter_id'];
		$public['parentpath'] = $position['parentpath'];
		$public['parentid'] = $position['parentid'];
		$public['distributor_y'] = $position['distributor_y'];
		$public['distributor_x'] = $position['distributor_x'];
		$public['status'] = 1;
		$public['addtime'] = time();
		
		$message_data = array();
		$message_data[] = array(
			'member_id'=>$member_info['member_id'],
			'text'=>'您已成功卡位，位置第'.$position['distributor_y'].'级第'.$position['distributor_x'].'个'
		);
		
		$flag = true;
		model()->beginTransaction();
		
		//插入排位记录
		$relate_id = $model_dis->addInfo('distributor_gp',$public);
		$flag = $flag && $relate_id;
		
		$commission_data = array();
		
		$result = $model_dis->getInfoOne('distributor_commission','','public_commission');
		$result['public_commission'] = empty($result['public_commission']) ? array() : json_decode($result['public_commission'],true);
		$commission = array();
		$commission = empty($result['public_commission'][$area_id]) ? array() : $result['public_commission'][$area_id];
		unset($result);
		
		//见点奖		
		if(!empty($commission['jiandian']) && !empty($position['parentpath'])){
			$parent = explode(',',trim($position['parentpath'],','));
			$parent = array_reverse($parent);
			if(count($parent) > $dis_config['public_bonus_level']){
				$parent = array_slice($parent,0,$dis_config['public_bonus_level']);
			}
			
			foreach($parent as $key=>$value){
				if(empty($commission['jiandian'][$key+1])){
					continue;
				}
				$commission_data[] = array(
					'area_id'=>$area_id,
					'record_id'=>$relate_id,
					'member_id'=>$value,
					'detail_type'=>'level',
					'detail_level'=>$key+1,
					'detail_bonus'=>$commission['jiandian'][$key+1],
					'detail_desc'=>'会员['.$member_info['member_name'].']排位到你的'.($key+1).'级，获得见点奖'.$commission['jiandian'][$key+1].'元',
					'detail_addtime'=>time()
				);
				if(!empty($rewardss[$key+1])){
					$message_data[] = array(
						'member_id'=>$value,
						'text'=>'会员['.$member_info['member_name'].']排位到你的'.($key+1).'级，获得见点奖'.$commission['jiandian'][$key+1].'元'
					);
				}
			}
		}
		
		//直接奖
		if(!empty($commission['inviter']) && $commission['inviter']>0 && !empty($member_info['inviter_id'])){
			$commission_data[] = array(
				'area_id'=>$area_id,
				'record_id'=>$relate_id,
				'member_id'=>$member_info['inviter_id'],
				'detail_type'=>'invite',
				'detail_level'=>0,
				'detail_bonus'=>$commission['inviter'],
				'detail_desc'=>'你推荐的会员['.$member_info['member_name'].']进行排位,获得直推奖'.$commission['inviter'].'元',
				'detail_addtime'=>time()
			);
			$message_data[] = array(
				'member_id'=>$member_info['inviter_id'],
				'text'=>'你推荐的会员['.$member_info['member_name'].']进行排位,获得直推奖'.$commission['inviter'].'元'
			);
		}
		
		//懒人奖
		if(!empty($commission['parent']) && $commission['parent']>0 && !empty($position['parentid'])){
			$commission_data[] = array(
				'area_id'=>$area_id,
				'record_id'=>$relate_id,
				'member_id'=>$position['parentid'],
				'detail_type'=>'parent',
				'detail_level'=>0,
				'detail_bonus'=>$commission['parent'],
				'detail_desc'=>'会员['.$member_info['member_name'].']排位到你的下级,获得懒人奖'.$commission['parent'].'元',
				'detail_addtime'=>time()
			);
			$message_data[] = array(
				'member_id'=>$position['parentid'],
				'text'=>'会员['.$member_info['member_name'].']排位到你的下级,获得懒人奖'.$commission['parent'].'元'
			);
		}
		
		//感恩奖
		if(!empty($dis_config['public_out_level'])){
			$parent = explode(',',trim($position['parentpath'],','));
			$parent = array_reverse($parent);
			if(count($parent) >= $dis_config['public_out_level']+1){
				//出局处理
				$y = $position['distributor_y'];
				$x = $position['distributor_x'];		
				$n = $dis_config['public_out_level'] + 1;				
				$m = pow($dis_config['public_times'],$n);
				$condition_1['distributor_y'] = $y - $n;
				if($condition_1['distributor_y']>1){//顶级不出局
					$condition_1['distributor_x'] = $x%$m==0 ? intval($x/$m) : intval($x/$m)+1;
					$condition_1['rootid'] = $position['rootid'];
					$condition_1['status'] = 1;
					$_gpinfo = $model_dis->getInfoOne('distributor_gp',$condition_1,'ralate_id');
					if(!empty($_gpinfo)){
						$result = $model_dis->editInfo('distributor_gp',array('status'=>0),array('ralate_id'=>$_gpinfo['ralate_id']));
						$flag = $flag && $result;
						$message_data[] = array(
							'member_id'=>$parent[$dis_config['public_out_level']],
							'text'=>'你已出局!欢迎复投,重新卡位'
						);
						$result = $model_member->getMemberInfo(array('member_id'=>$parent[$dis_config['public_out_level']]), 'member_name,inviter_id');
						if(!empty($result) && !empty($commission['thankful'])){
							$commission_data[] = array(
								'area_id'=>$area_id,
								'record_id'=>$relate_id,
								'member_id'=>$result['inviter_id'],
								'detail_type'=>'thankful',
								'detail_level'=>0,
								'detail_bonus'=>$commission['thankful'],
								'detail_desc'=>'你推荐的会员['.$result['member_name'].']出局,获得感恩奖'.$commission['thankful'].'元',
								'detail_addtime'=>time()
							);
							$message_data[] = array(
								'member_id'=>$result['inviter_id'],
								'text'=>'你推荐的会员['.$result['member_name'].']出局,获得感恩奖'.$commission['thankful'].'元'
							);
						}
					}					
				}
			}
		}
		
		
		if(!empty($commission_data)){
			$result = $model_dis->addAll('distributor_gp_detail',$commission_data);
			$flag = $flag && $result;
		}
		
		if($flag){
			$model_dis->commit();
			
			/*发送卡位相关消息*/
			$weixin_config = $model_dis->getInfoOne('weixin_wechat','');
			$access_token = logic('weixin_token')->get_access_token($weixin_config);
			if(!empty($message_data)){
				logic('weixin_message')->sendpublicmess($access_token, $weixin_config, $message_data);
			}
		}else{
			$model_dis->rollback();
		}
		return $flag;
	}
	
	/*
	*排位获取坐标、上级等信息
	*/
	public function get_position($member_id, $inviter_id, $times=2,$area_id){
		//第一级
		if(empty($inviter_id)){
			return array(
				'rootid'=>$member_id,
				'parentpath'=>'',
				'parentid'=>0,
				'distributor_y'=>1,
				'distributor_x'=>1
			);
		}
		
		$model_dis = model('distributor');
		
		$poistion = array();
		$poistion['rootid'] = empty($gp_info['rootid']) ? $member_id : $gp_info['rootid'];
		
		//获取最后一个公排信息
		$last = $model_dis->getInfoOne('distributor_gp',array('area_id'=>$area_id),'distributor_y,distributor_x,rootid','distributor_y desc,distributor_x desc');
		if($last['distributor_x']>=pow($times,($last['distributor_y']-1))){
			$poistion['distributor_y'] = $last['distributor_y']+1;
			$poistion['distributor_x'] = 1;
		}else{
			$poistion['distributor_y'] = $last['distributor_y'];
			$poistion['distributor_x'] = $last['distributor_x']+1;
		}
		$poistion['rootid'] = $last['rootid'];
		
		//获取上级坐标
		$condition['area_id'] = $area_id;
		$condition['distributor_y'] = $poistion['distributor_y']-1;
		if($poistion['distributor_x']%$times==0){
			$condition['distributor_x'] = intval($poistion['distributor_x']/$times);
		}else{
			$condition['distributor_x'] = intval($poistion['distributor_x']/$times)+1;
		}
		$parent = $model_dis->getInfoOne('distributor_gp',$condition,'parentpath,member_id');
		if(empty($parent)){
			return array();
		}
		
		$poistion['parentid'] = $parent['member_id'];
		$poistion['parentpath'] = empty($parent['parentpath']) ? ','.$parent['member_id'].',' : $parent['parentpath'].$parent['member_id'].',';
		return $poistion;
	}
	
	//分销商品获得佣金
	public function add_distributor_good_commission($order_goods,$ownerid,$buyerid,$orderid,$buyer_name,$type){
		$model_dis = model('distributor');
		$dis_config = $model_dis->getInfoOne('distributor_setting','');
		if($dis_config['dis_goods_open']==0){
			return true;
		}
		
		//获得上级ids
		$dis_account = $model_dis->getInfoOne('distributor_account',array('member_id'=>$ownerid),'dis_path');
		if($ownerid!=$buyerid){
			$dis_account['dis_path'] = empty($dis_account['dis_path']) ? $ownerid : $dis_account['dis_path'].$ownerid.',';
		}
		$parent = $dis_account['dis_path'] ? explode(',',trim($dis_account['dis_path'],',')) : array();
		$parent = array_reverse($parent);
		if(count($parent) > $dis_config['dis_bonus_level']){
			$parent = array_slice($parent,0,$dis_config['dis_bonus_level']);
		}
		
		if($ownerid==$buyerid && $dis_config['dis_bonus_self']==1){
			$parent[count($parent)] = $ownerid;
		}
		
		if(empty($parent)){
			return true;
		}
		
		//获取分销商的级别
		$distributor_levels = array();
		$result = model()->query('SELECT level_id,member_id FROM shop_distributor_account where member_id IN('.implode(',',$parent).')');
		foreach($result as $rr){
			$distributor_levels[$rr['member_id']] = $rr['level_id'];
		}
		
		$goods_info = array();
		foreach($order_goods as $good){
			$goods_info[$good['goods_id']] = array('price'=>$good['goods_price'],'num'=>$good['goods_num'],'name'=>$good['goods_name']);
		}
		
		//获取goods_commonid
		$common_info = array();
		$goods_list = model()->query('SELECT goods_commonid,goods_id FROM shop_goods WHERE goods_id IN('.implode(',',array_keys($goods_info)).')');
		foreach($goods_list as $g){
			$common_info[$g['goods_commonid']][] = $g['goods_id'];
		}
		
		//获得每个商品的供货价
		$supply_price = array();
		$result_123 = model()->query('SELECT goods_costprice,goods_commonid FROM shop_goods_common WHERE goods_commonid IN('.implode(',',array_keys($common_info)).')');
		foreach($result_123 as $r_123){
			$supply_price[$r_123['goods_commonid']] = $r_123['goods_costprice'];
		}
		
		//获得good_common分销信息
		$commission_result = array();
		$good_common_info = model()->query('SELECT good_profit,good_id,good_dis_commission FROM shop_distributor_goods WHERE good_id IN('.implode(',',array_keys($common_info)).') and good_status=1');
		if($good_common_info==null){
			return true;
		}
		foreach($good_common_info as $res){
			if($res['good_profit']>0 && !empty($res['good_dis_commission'])){
				$commission_result[$res['good_id']] = array('profit'=>$res['good_profit'],'commission'=>$res['good_dis_commission']);
			}
		}
		
		foreach($commission_result as $key=>$value){
			foreach($common_info[$key] as $goodid){
				$record_data = array(
					'buyer_id'=>$buyerid,
					'owner_id'=>$ownerid,
					'order_id'=>$orderid,
					'goods_id'=>$goodid,
					'order_type'=>$type,
					'goods_price'=>$goods_info[$goodid]['price'],
					'goods_num'=>$goods_info[$goodid]['num'],
					'record_addtime'=>time(),
					'record_status'=>10
				);
				$recordid = $model_dis->addInfo('distributor_goodsrecord',$record_data);
				if($recordid>0){
					$bonus_profit = ($goods_info[$goodid]['price']-$supply_price[$key]) * $value['profit'] * 0.01;
					$commission_detail = json_decode($value['commission'],true);
					$detail_data = array();
					foreach($parent as $k=>$mid){
						$my_bonus_price = 0;
						if($mid!=$buyerid){
							if(!empty($commission_detail[$distributor_levels[$mid]])){
								if(!empty($commission_detail[$distributor_levels[$mid]][$k])){
									$my_bonus_price = $bonus_profit * $commission_detail[$distributor_levels[$mid]][$k] * 0.01;
								}
							}
						}else{
							if(!empty($commission_detail[$distributor_levels[$mid]])){
								if(!empty($commission_detail[$distributor_levels[$mid]][$dis_config['dis_bonus_level']])){
									$my_bonus_price = $bonus_profit * $commission_detail[$distributor_levels[$mid]][$dis_config['dis_bonus_level']] * 0.01;
								}
							}
						}
						
						$detail_data[] = array(
							'record_id'=>$recordid,
							'good_id'=>$goodid,
							'order_id'=>$orderid,
							'member_id'=>$mid,
							'detail_level'=>($mid==$buyerid ? 0 : ($k+1)),
							'detail_bonus'=>$my_bonus_price==0 ? 0 : number_format(($my_bonus_price*$goods_info[$goodid]['num']),2,'.',''),
							'detail_price'=>$my_bonus_price==0 ? 0 : number_format($my_bonus_price,2,'.',''),
							'detail_num'=>$goods_info[$goodid]['num'],
							'detail_desc'=>($mid==$buyerid ? '自己销售自己购买' : '你的'.($k+1).'级会员 '.$buyer_name.' 购买').$goods_info[$goodid]['name'].'(单价：'.$goods_info[$goodid]['price'].'元,数量：'.$goods_info[$goodid]['num'].'),你获得'.$dis_config['dis_bonus_name'].number_format(($my_bonus_price*$goods_info[$goodid]['num']),2,'.','').'元',
							'detail_status'=>10,
							'detail_addtime'=>time()
						);
					}
					
					$res_456 = $model_dis->addAll('distributor_goodsrecord_detail',$detail_data);
				}
			}
		}
		
		return true;
	}
	
	//分销商，公派处理，订单付款时
	public function deal_dis_public($order_list=array(),$order_info=array()){
		$costone = 0;
		$member_id = 0;
		$order_ids = $common_ids = $goods_num = array();
		
		if(!empty($order_list)){
			foreach($order_list as $value){
				$costone = $costone + $value['order_amount'];
				$order_ids[] = $value['order_id'];
				if($member_id == 0){
					$member_id = $value['buyer_id'];
				}
			}
			$result = model()->query('SELECT g.goods_commonid,o.goods_num FROM shop_order_goods AS o LEFT JOIN shop_goods AS g ON o.goods_id=g.goods_id WHERE o.order_id IN('.implode(',',$order_ids).')');
		}else{
			$costone = $order_info['order_amount'];
			$order_ids[] = $order_info['order_id'];
			if($member_id == 0){
				$member_id = $order_info['buyer_id'];
			}
			if(!empty($order_info['goods_id'])){
				$result = model()->query('SELECT goods_commonid,goods_num FROM shop_goods WHERE goods_id IN('.$order_info['goods_id'].')');
			}else{
				$result = model()->query('SELECT g.goods_commonid,o.goods_num FROM shop_order_goods AS o LEFT JOIN shop_goods AS g ON o.goods_id=g.goods_id WHERE o.order_id IN('.implode(',',$order_ids).')');
			}
			
		}
		
		if($result==null){
			return '';
		}
		
		foreach($result as $r){
			$common_ids[] = $r['goods_commonid'];
			if(empty($goods_num[$r['goods_commonid']])){
				$goods_num[$r['goods_commonid']] = $r['goods_num'];
			}else{
				$goods_num[$r['goods_commonid']] = $goods_num[$r['goods_commonid']] + $r['goods_num'];
			}
		}
		
		$flag = $this->deal_distributor($member_id,array('register'=>0,'costone'=>$costone,'costall'=>0,'commonids'=>$common_ids,'goods_num'=>$goods_num));
	}
	
	//佣金状态
	public function deal_commission_state($order_list=array(),$order_info=array(),$state=20){
		$order_ids = array();
		if(!empty($order_list)){
			foreach($order_list as $value){
				$order_ids[] = $value['order_id'];
			}
		}else{
			$order_ids[] = $order_info['order_id'];
		}
		
		$model_dis = model('distributor');
		$condition['order_id'] = array('in',$order_ids);
		$flag = $model_dis->editInfo('distributor_goodsrecord',array('record_status'=>$state), $condition);
		$flag = $model_dis->editInfo('distributor_goodsrecord_detail',array('detail_status'=>$state), $condition);
	}
	
	//公排分区升级
	public function public_update($member_id){
		$model_dis = model('distributor');
		//获得推荐者信息
		$member_info = $model_dis->getInfoOne('member',array('member_id'=>$member_id),'*');
		if(empty($member_info)){
			return false;
		}
		//获得用户的公排最高级区域
		$result = $model_dis->getInfoOne('distributor_gp',array('member_id'=>$member_info['member_id']),'area_id','ralate_id desc');
		if(empty($result)){
			return false;
		}
		$current_area_id = $result['area_id'];
		unset($result);
		//获得下一级公排分区
		$result = $model_dis->getInfoOne('distributor_gp_area',array('item_id'=>array('gt',$current_area_id)),'item_id,item_condition','item_id asc');
		
		if(empty($result)){//已是最高级
			return false;
		}
		$next_area_info = $result;
		unset($result);		
		
		//下级人数是否达到升级条件判断
		$result = $model_dis->getInfoOne('distributor_gp',array('area_id'=>$current_area_id,'parentpath'=>array('like','%,'.$member_info['member_id'].',%')),'count(ralate_id) as num');
		if($result['num']<$next_area_info['item_condition']){
			return false;
		}
		unset($result);
		
		//提现金额判断
		if($current_area_id==1){
			$condition['member_id'] = $member_info['member_id'];
			$condition['detail_status'] = 0;
			$condition['area_id'] = array('in',$current_area_id);
			$result = $model_dis->getInfoOne('distributor_gp_detail', $condition, 'SUM(detail_bonus) as money');
			$bonus = empty($result['money']) ? 0 : $result['money'];
			unset($result);
			unset($condition);
			
			$condition = array(
				'member_id'=>$member_info['member_id'],
				'record_status'=>array('in',array(0,1))
			);
			$result = $model_dis->getInfoOne('withdraw_record',$condition,'SUM(record_total) as money');
			$withdraw = empty($result['money']) ? 0 : $result['money'];
			unset($result);
			unset($condition);
			
			if($withdraw<$bonus){
				return false;
			}
		}
			
		
		//达到升级条件
		$this->add_public_account($member_info,array(),$next_area_info['item_id']);
	}
}