<?php
namespace common\logic;
use core;
use lib;
class weixin_message{
	
	//会员本人关注提醒
	public function addmemberself($access_token, $disconfig ,$weixin_config, $member_name, $openid, $member_id){
		$wechat = new lib\wxSDk\WechatAuth($weixin_config['wechat_appid'], $weixin_config['wechat_appsecret'], $access_token);
		$wechat->sendText($openid, '您好，['.$member_name.']！您已成为第'.$member_id.'位会员');		
		return true;
	}
	
	//会员上级关注提醒
	public function addmember($access_token, $disconfig ,$weixin_config, $member_name, $owner_id){
		$distributor = model('distributor')->getInfoOne('distributor_account',array('member_id'=>$owner_id),'dis_path');
		if(empty($distributor)){
			return false;
		}
		
		$parentpath = $distributor['dis_path'].$owner_id.',';
		$parent = explode(',',trim($parentpath,','));
		$parent = array_reverse($parent);
		if(count($parent) > $disconfig['dis_bonus_level']){
			$parent = array_slice($parent,0,$disconfig['dis_bonus_level']);
		}
		
		$condition['member_id'] = array('in',$parent);
		$result = model('member')->getMemberList($condition, $field = 'weixin_unionid,member_id');
		if(empty($result)){
			return false;
		}
		foreach($result as $r){
			$member_list[$r['member_id']] = $r['weixin_unionid'];
		}
		
		$level_lang = array('一','二','三','四','五','六','七','八','九','十','十一','十二','十三','十四','十五');
		$wechat = new lib\wxSDk\WechatAuth($weixin_config['wechat_appid'], $weixin_config['wechat_appsecret'], $access_token);
		foreach($parent as $key=>$pid){
			if(!empty($member_list[$pid])){
				$wechat->sendText($member_list[$pid], '您的'.$level_lang[$key].'级会员['.$member_name.']关注了本公众号');
			}
		}
		return true;
	}
	
	//提交订单和支付订单提醒
	public function addorder($access_token, $disconfig ,$weixin_config, $member_name, $amount, $order_id, $type=0){
		$result = model()->query('SELECT SUM(detail_bonus) as bonus,detail_level,member_id from shop_distributor_goodsrecord_detail where order_id='.$order_id.' group by member_id');
		if(empty($result)){
			return false;
		}
		
		foreach($result as $rr){
			$commission_list[$rr['member_id']] = $rr;
		}
		
		$condition['member_id'] = array('in',array_keys($commission_list));
		$result = model('member')->getMemberList($condition, $field = 'weixin_unionid,member_id');
		if(empty($result)){
			return false;
		}
		foreach($result as $r){
			$member_list[$r['member_id']] = $r['weixin_unionid'];
		}
		
		$level_lang = array('','一','二','三','四','五','六','七','八','九','十','十一','十二','十三','十四','十五');
		$wechat = new lib\wxSDk\WechatAuth($weixin_config['wechat_appid'], $weixin_config['wechat_appsecret'], $access_token);
		foreach($commission_list as $key => $value){
			if(empty($member_list[$key])){
				continue;
			}
			
			if($type==0){
				$text = $value['detail_level'] > 0 ? '您的'.$level_lang[$value['detail_level']].'级会员['.$member_name.']提交了订单，总额'.$amount.'元，您将获得'.$value['bonus'].'元'.$disconfig['dis_bonus_name'] : '您提交了订单，总额'.$amount.'元，您将获得'.$value['bonus'].'元'.$disconfig['dis_bonus_name'];
			}elseif($type==1){
				$text = $value['detail_level'] > 0 ? '您的'.$level_lang[$value['detail_level']].'级会员['.$member_name.']支付了订单，总额'.$amount.'元，您获得的'.$value['bonus'].'元'.$disconfig['dis_bonus_name'].'在路上' : '您支付了订单，总额'.$amount.'元，您获得的'.$value['bonus'].'元'.$disconfig['dis_bonus_name'].'在路上';
			}elseif($type==2){
				$text = $value['detail_level'] > 0 ? '您的'.$level_lang[$value['detail_level']].'级会员['.$member_name.']购买的订单已发货，总额'.$amount.'元，您获得的'.$value['bonus'].'元'.$disconfig['dis_bonus_name'].'离您更近了一步' : '您购买的订单已发货，总额'.$amount.'元，您获得的'.$value['bonus'].'元'.$disconfig['dis_bonus_name'].'离您更近了一步';
			}elseif($type==3){
				$text = $value['detail_level'] > 0 ? '您的'.$level_lang[$value['detail_level']].'级会员['.$member_name.']购买的订单已完成，总额'.$amount.'元，您获得的'.$value['bonus'].'元'.$disconfig['dis_bonus_name'].'已到账' : '您购买的订单已完成，总额'.$amount.'元，您获得的'.$value['bonus'].'元'.$disconfig['dis_bonus_name'].'已到账';
			}
			
			$wechat->sendText($member_list[$key], $text);
		}
		return true;
	}
	
	//公排系统佣金提醒
	public function addgp($access_token, $disconfig ,$weixin_config, $member_name, $record_id){
		$result = model()->query('SELECT detail_bonus,detail_type,detail_level,member_id from shop_distributor_gp_detail where record_id='.$record_id);
		if(empty($result)){
			return false;
		}
		
		foreach($result as $rr){
			$commission_list[$rr['member_id']] = $rr;
		}
		
		$condition['member_id'] = array('in',array_keys($commission_list));
		$result = model('member')->getMemberList($condition, $field = 'weixin_unionid,member_id');
		if(empty($result)){
			return false;
		}
		foreach($result as $r){
			$member_list[$r['member_id']] = $r['weixin_unionid'];
		}
		
		$level_lang = array('','一','二','三','四','五','六','七','八','九','十','十一','十二','十三','十四','十五');
		$wechat = new lib\wxSDk\WechatAuth($weixin_config['wechat_appid'], $weixin_config['wechat_appsecret'], $access_token);
		foreach($commission_list as $key => $value){
			if(empty($member_list[$key])){
				continue;
			}
			
			if($value['detail_type']=='invite'){
				$text = '你推荐的会员['.$member_name.']进行排位,获得直接推荐奖红包'.$value['detail_bonus'].'元';
			}elseif($value['detail_type']=='parent'){
				$text = '会员['.$member_name.']排位到你的下级,获得见点奖红包'.$value['detail_bonus'].'元';
			}elseif($value['detail_type']=='thinkfull'){
				$text = '你推荐的会员['.$member_name.']出局,获得感恩奖红包'.$value['detail_bonus'].'元';
			}else{
				$text = '会员['.$member_name.'排位到你的'.$value['detail_level'].'级，获得红包'.$value['detail_bonus'].'元';
			}
			
			$wechat->sendText($member_list[$key], $text);
		}
		return true;
	}
	
	//公排系统相关提醒
	public function sendpublicmess($access_token ,$weixin_config, $message_data){
		$member_ids = array();
		foreach($message_data as $va){
			if(!in_array($va['member_id'],$member_ids)){
				$member_ids[] = $va['member_id'];
			}
		}
		$condition['member_id'] = array('in',$member_ids);
		$result = model('member')->getMemberList($condition, $field = 'weixin_unionid,member_id');
		if(empty($result)){
			return false;
		}
		foreach($result as $r){
			$member_list[$r['member_id']] = $r['weixin_unionid'];
		}
		
		$wechat = new lib\wxSDk\WechatAuth($weixin_config['wechat_appid'], $weixin_config['wechat_appsecret'], $access_token);
		foreach($message_data as $key => $value){
			if(empty($member_list[$value['member_id']])){
				continue;
			}
			
			$wechat->sendText($member_list[$value['member_id']], $value['text']);
		}
		return true;
	}
}
?>