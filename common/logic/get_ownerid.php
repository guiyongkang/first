<?php
namespace common\logic;
use core;
defined('SAFE_CONST') or exit('Access Invalid!');
class get_ownerid
{
	public function get_ownerid($url){//获取上级member_id
		$ownerid = 0;
		$userid = 0;
		if(isset($_COOKIE['key'])){
			$result = model('mb_user_token')->getMbUserTokenInfo(array('token'=>$_COOKIE['key']));
    		$userid = empty($result['member_id']) ? 0 : $result['member_id'];
		}
		//通过登录会员获取上级member_id
		if($userid>0){
			$userinfo = model('member')->getMemberInfoByID($userid,'is_distributor,inviter_id');
			$ownerid = (isset($userinfo['is_distributor']) && $userinfo['is_distributor']==1) ? $userid : (empty($userinfo['inviter_id']) ? 0 : $userinfo['inviter_id']);
		}elseif(!empty($url)){
			$pageinfo = $this->split_url($url);
			$ownerid = empty($pageinfo['requesturi']['oid']) ? 0 : intval($pageinfo['requesturi']['oid']);
		}
		
		if($ownerid>0){
			core\session::set('uid', $ownerid);
		}
		
		return $ownerid;
	}
	
	public function split_url($url){
		$result = array();
		$arr_url = explode('?',$url);
		
		//获取页面类型
		$page_url = explode("/",$arr_url[0]);
		$result['page'] = empty($page_url[count($page_url)-1]) ? 'index' : str_replace(array(".html",".php"),"",$page_url[count($page_url)-1]);
		
		//获取页面参数
		if(empty($arr_url[1])){
			$result['requesturi'] = array();
		}else{
			$param = explode('&',$arr_url[1]);
			foreach($param as $v){
				$arr = explode('=',$v);
				$result['requesturi'][$arr[0]] = empty($arr[1]) ? '' : $arr[1];
			}
		}
		
		return $result;
	}
	
	public function connect_url($ownerid,$url){
		$result = array();
		$arr_url = explode('?',$url);
		
		//获取页面参数
		if(empty($arr_url[1])){
			return $url.'?oid='.$ownerid;
		}else{
			$new_url = $arr_url[0].'?oid='.$ownerid;
			$param = explode('&',$arr_url[1]);
			foreach($param as $v){
				$arr = explode('=',$v);
				if($arr[0]!='oid'){
					$new_url .= '&'.$v;
				}
			}
		}
		return $new_url;
	}
}