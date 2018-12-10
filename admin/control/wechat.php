<?php
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class wechat extends SystemControl
{

	public function __construct(){
		parent::__construct();
		core\language::read('wechat');
	}

	/**
	 * 模块设置
	 */
	public function setting_manageOp() {
		$lang = core\language::getLangContent();
        $model_setting = model('setting');
		$model_wechat = model('wechat');
		if (chksubmit()){
			$update_array = array();
			$update_array['wechat_isuse'] = intval($_POST['isuse']);
			$result = $model_setting->updateSetting($update_array);
			
			$update_array = array();
			if(empty($_POST['wid'])){
				error($lang['nc_common_save_fail']);
			}
			$wechatid = intval($_POST['wid']);
			$update_array = array(
				'wechat_share_title'=>htmlspecialchars($_POST['sharetitle'], ENT_QUOTES),
				'wechat_share_logo'=>str_replace(UPLOAD_SITE_URL,'',trim($_POST['thumb'])),
				'wechat_share_desc'=>htmlspecialchars($_POST['sharedesc'], ENT_QUOTES)
			);
			
			$condition = array('wechat_id'=>$wechatid);
			
			$result = $model_wechat->editInfo('weixin_wechat',$update_array,$condition);
			
			if ($result === true){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			$setting_list = $model_setting->getListSetting();
			
			$api_account = $model_wechat->getInfoOne('weixin_wechat','','wechat_share_title,wechat_share_logo,wechat_share_desc,wechat_id');
			if(empty($api_account)){
				$api_account = array(
					'wechat_share_title'=>'',
					'wechat_share_logo'=>'',
					'wechat_share_desc'=>'',
					'admin_id'=>'',
					'wechat_token'=>strtolower(random(10)),
					'wechat_sn'=>strtolower(random(10)),
					'wechat_type'=>3,
					'wechat_appid'=>'',
					'wechat_appsecret'=>'',
					'wechat_name'=>'',
					'wechat_email'=>'',
					'wechat_preid'=>'',
					'wechat_account'=>'',
					'wechat_encodingtype'=>0,
					'wechat_encoding'=>''
				);
				$wechat_id = $model_wechat->addInfo('weixin_wechat',$api_account);
				$api_account['wechat_id'] = $wechat_id;
			}			
			core\tpl::output('api_account',$api_account);
			
			core\tpl::output('setting',$setting_list);
			core\tpl::showpage('wechat_setting');
		}
	}

    /**
     * 接口设置
     **/
    public function api_manageOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		if (chksubmit()){
			if(empty($_POST['wid'])){
				error($lang['nc_common_save_fail']);
			}
			$wechatid = intval($_POST['wid']);
			$update_array = array(
				'wechat_type'=>intval($_POST['type']),
				'wechat_appid'=>trim($_POST['appid']),
				'wechat_appsecret'=>trim($_POST['appsecret']),
				'wechat_name'=>trim($_POST['name']),
				'wechat_email'=>trim($_POST['email']),
				'wechat_preid'=>trim($_POST['preid']),
				'wechat_account'=>trim($_POST['account']),
				'wechat_encodingtype'=>intval($_POST['encodingtype']),
				'wechat_encoding'=>trim($_POST['encoding']),
			);
			
			$condition = array('wechat_id'=>$wechatid);
			
			$result = $model_wechat->editInfo('weixin_wechat',$update_array,$condition);
			if ($result === true){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			$api_account = $model_wechat->getInfoOne('weixin_wechat','');
			if(empty($api_account)){
				
				$api_account = array(
					'admin_id'=>'',
					'wechat_token'=>strtolower(random(10)),
					'wechat_sn'=>strtolower(random(10)),
					'wechat_type'=>3,
					'wechat_appid'=>'',
					'wechat_appsecret'=>'',
					'wechat_name'=>'',
					'wechat_email'=>'',
					'wechat_preid'=>'',
					'wechat_account'=>'',
					'wechat_encodingtype'=>0,
					'wechat_encoding'=>''					
				);
				$wechat_id = $model_wechat->addInfo('weixin_wechat',$api_account);
				$api_account['wechat_id'] = $wechat_id;
			}
			core\tpl::output('api_account',$api_account);
			core\tpl::showpage('wechat_api');
		}
    }

    /**
     * 素材管理
     **/
    public function material_manageOp() {
		$lang = core\language::getLangContent();
		$model_wechat = model('wechat');
        $links = array(
			array('url'=>'act=wechat&op=material_manage','lang'=>'material_all'),
			array('url'=>'act=wechat&op=material_edit','lang'=>'material_add')
		);
		
		$condition = array();
        if (!empty($_GET['material_type'])) {
            $condition['material_type'] = intval($_GET['material_type']);
        }else{
			$_GET['material_type'] = '';
		}
        $result = $model_wechat->getInfoList('weixin_material',$condition,8,'material_addtime desc');
		$material_list = array();
		if(!empty($result)){
			foreach($result as $key=>$value){
				$value['material_content'] = unserialize($value['material_content']);
				$material_list[] = $value;
			}
		}
		
		core\tpl::output('material_list',$material_list);
		core\tpl::output('page',$model_wechat->showpage('2'));
		core\tpl::output('top_link',$this->sublink($links,'material_manage'));
		core\tpl::showpage('wechat_material');
    }
	
	/*
	弹框获取素材列表
	*/
	public function material_listOp() {
		$lang = core\language::getLangContent();
		$model_wechat = model('wechat');
        
		$condition = array();
        if (!empty($_GET['type'])) {
            $condition['material_type'] = intval($_GET['type']);
        }else{
			$_GET['type'] = '';
		}
        $result = $model_wechat->getInfoList('weixin_material',$condition,8,'material_addtime desc');
		$material_list = array();
		if(!empty($result)){
			foreach($result as $key=>$value){
				$value['material_content'] = unserialize($value['material_content']);
				$material_list[] = $value;
			}
		}
		
		core\tpl::output('material_list',$material_list);
		core\tpl::output('show_page',$model_wechat->showpage('2'));
		core\tpl::showpage('wechat_material_dialog','null_layout');
    }
	
	/**
     * 素材编辑
     **/
    public function material_editOp() {
		$lang = core\language::getLangContent();
		$model_wechat = model('wechat');
		if(chksubmit()){
			if(empty($_POST['ImgPath'])){
				error($lang['material_not_null']);
			}
			$submit_content = array();
			foreach($_POST['ImgPath'] as $key=>$value){
				$_POST['TextContents'][$key] = str_replace('\"','',$_POST['TextContents'][$key]);
				$_POST['TextContents'][$key] = str_replace("\'","",$_POST['TextContents'][$key]);
				if(empty($value)){
					continue;
				}
				$submit_content[] = array(
					'ImgPath'=>str_replace(UPLOAD_SITE_URL,'',trim($value)),
					'Title'=>trim($_POST['Title'][$key]),
					'Url'=>trim($_POST['Url'][$key]),
					'TextContents'=>trim($_POST['TextContents'][$key])
				);
			}
			
			if(empty($submit_content)){
				error($lang['material_not_null']);
			}
			$update_array = array();
			$update_array['material_type'] = count($submit_content)==1 ? 1 : 2;
			$update_array['material_content'] = serialize($submit_content);
			
			if(!empty($_POST['mid'])){
				$condition = array('material_id'=>intval($_POST['mid']));
				$result = $model_wechat->editInfo('weixin_material',$update_array,$condition);
			}else{
				$update_array['material_addtime'] = time();
				$result = $model_wechat->addInfo('weixin_material',$update_array);
			}
			
			if ($result){
				success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=material_manage');
			}else {
				error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=material_manage');
			}
		}else{
			if(!empty($_GET['mid'])){
				$material_info = $model_wechat->getInfoOne('weixin_material',array('material_id'=>intval($_GET['mid'])));
				if (empty($material_info)){
					error($lang['info_not_exist']);
				}
				
				$material_info['items'] = unserialize($material_info['material_content']);
			}else{
				$material_info = array();
				$material_info['material_addtime'] = time();
			}
			
			core\tpl::output('material',$material_info);
			core\tpl::showpage('wechat_material_edit');
		}
    }
	
	public function material_delOp(){
		$lang = core\language::getLangContent();
		
		if(empty($_GET['mid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=material_manage');
		}
		
		if (intval($_GET['mid']) > 0){
			$model_wechat = model('wechat');
			$condition = array('material_id'=>intval($_GET['mid']));
			$material_info = $model_wechat->getInfoOne('weixin_material',$condition);
			if(empty($material_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=material_manage');
			}
			
			$result = $model_wechat->delInfo('weixin_material',$condition);
			
			//delete images
			$material_info['items'] = unserialize($material_info['material_content']);
			foreach($material_info['items'] as $key=>$value){
				@unlink(BASE_UPLOAD_PATH.$value['ImgPath']);
			}	
			success($lang['nc_common_del_succ'],'index.php?act='.$_GET['act'].'&op=material_manage');
		}else {
			error($lang['nc_common_del_fail'],'index.php?act='.$_GET['act'].'&op=material_manage');
		}
	}
	
	/**
     * 首次关注设置
     **/
    public function subcribe_manageOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		if (chksubmit()){
			if(empty($_POST['rid'])){
				error($lang['nc_common_save_fail']);
			}
			$rid = intval($_POST['rid']);
			$update_array = array(
				'reply_msgtype'=>intval($_POST['msgtype']),
				'reply_textcontents'=>trim($_POST['textcontents']),
				'reply_materialid'=>empty($_POST['materialid']) ? 0 : intval($_POST['materialid']),
				'reply_subscribe'=>empty($_POST['subscribe']) ? 0 : intval($_POST['subscribe']),
				'reply_membernotice'=>empty($_POST['membernotice']) ? 0 : intval($_POST['membernotice'])
			);
			
			$condition = array('reply_id'=>$rid);
			
			$result = $model_wechat->editInfo('weixin_attention',$update_array,$condition);
			if ($result === true){
				success($lang['nc_common_save_succ']);
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			$attention_account = $model_wechat->getInfoOne('weixin_attention','');
			if(empty($attention_account)){
				$attention_account = array(
					'admin_id'=>'',
					'reply_msgtype'=>0,
					'reply_textcontents'=>'很高兴认识你，新朋友！',
					'reply_materialid'=>0,
					'reply_subscribe'=>1,
					'reply_membernotice'=>1	
				);
				$reply_id = $model_wechat->addInfo('weixin_attention',$attention_account);
				$attention_account['reply_id'] = $reply_id;
			}
			
			$material_info = array();
			if(!empty($attention_account['reply_materialid'])){
				$material_info = $model_wechat->getInfoOne('weixin_material',array('material_id'=>intval($attention_account['reply_materialid'])));
				if (!empty($material_info)){
					$material_info['items'] = unserialize($material_info['material_content']);
				}
			}
			core\tpl::output('material_info',$material_info);
			core\tpl::output('attention_account',$attention_account);
			core\tpl::showpage('wechat_attention');
		}
    }
	
	/*关键词列表*/
	public function keyword_manageOp(){
		$lang = core\language::getLangContent();
		$model_wechat = model('wechat');
        
		$condition = array();
        if (!empty($_GET['type'])) {
            $condition['reply_msgtype'] = intval($_GET['type'])-1;
        }else{
			$_GET['type'] = 0;
		}
		if (!empty($_GET['keywords'])) {
            $condition['reply_keywords'] = array('like', '%' . trim($_GET['keywords']) . '%');
        }else{
			$_GET['keywords'] = '';
		}
        $reply_list = $model_wechat->getInfoList('weixin_reply',$condition,10,'reply_addtime desc');
		
		core\tpl::output('reply_list',$reply_list);
		core\tpl::output('show_page',$model_wechat->showpage('2'));
		core\tpl::showpage('wechat_keyword_list');
	}
	
	/**
     * 关键词添加
     **/
    public function keyword_addOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		if (chksubmit()){
			
			if(!empty($_POST['keywords'])){
				$_POST['keywords'] = trim($_POST['keywords'],'|');
				$_POST['keywords'] = str_replace('||','|',$_POST['keywords']);
			}
			
			if(empty($_POST['keywords'])){
				error($lang['not_info_keywords']);
			}
			
			$array = explode('|',$_POST['keywords']);
			foreach($array as $a){
				if(trim($a)=='') continue;
				$condition['reply_keywords'] = array('like', '%|' . trim($a) . '|%');
				$reply_info = $model_wechat->getInfoOne('weixin_reply',$condition);
				if(!empty($reply_info)){
					error($lang['info_keywords_exits'].'：'.$a);
				}
			}
			
			$update_array = array(
				'reply_keywords'=>intval($_POST['patternmethod']) == 0 ? trim($_POST['keywords']) : '|'.trim($_POST['keywords'],'|').'|',
				'admin_id'=>0,
				'reply_patternmethod'=>intval($_POST['patternmethod']),
				'reply_msgtype'=>intval($_POST['msgtype']),
				'reply_textcontents'=>trim($_POST['textcontents']),
				'reply_materialid'=>intval($_POST['materialid']),
				'reply_addtime'=>time()
			);
			
			$result = $model_wechat->addInfo('weixin_reply',$update_array);
			if ($result){
				success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=keyword_manage');
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			core\tpl::showpage('wechat_keyword_add');
		}
    }
	
	/**
     * 关键词修改
     **/
    public function keyword_editOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		if (chksubmit()){
			if(empty($_POST['rid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=keyword_manage');
			}
			
			if(!empty($_POST['keywords'])){
				$_POST['keywords'] = trim($_POST['keywords'],'|');
				$_POST['keywords'] = str_replace('||','|',$_POST['keywords']);
			}
			
			if(empty($_POST['keywords'])){
				error($lang['not_info_keywords']);
			}
			
			$array = explode('|',$_POST['keywords']);
			foreach($array as $a){
				if(trim($a)=='') continue;
				$condition['reply_keywords'] = array('like', '%|' . trim($a) . '|%');
				$condition['reply_id'] = array('neq',$_POST['rid']);
				
				$reply_info = $model_wechat->getInfoOne('weixin_reply',$condition);
				if(!empty($reply_info)){
					error($lang['info_keywords_exits'].'：'.$a);
				}
			}
			
			$update_array = array(
				'reply_keywords'=>intval($_POST['patternmethod']) == 0 ? trim($_POST['keywords']) : '|'.trim($_POST['keywords'],'|').'|',
				'reply_patternmethod'=>intval($_POST['patternmethod']),
				'reply_msgtype'=>intval($_POST['msgtype']),
				'reply_textcontents'=>trim($_POST['textcontents']),
				'reply_materialid'=>intval($_POST['materialid'])
			);
			
			$condition = array('reply_id'=>intval($_POST['rid']));
			
			$result = $model_wechat->editInfo('weixin_reply',$update_array,$condition);
			if ($result === true){
				success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=keyword_manage');
			}else {
				error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=keyword_manage');
			}
		}else{
			$reply_info = $material_info = array();
			if(empty($_GET['rid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=keyword_manage');
			}
			$reply_info = $model_wechat->getInfoOne('weixin_reply',array('reply_id'=>intval($_GET['rid'])));
			if(empty($reply_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=keyword_manage');
			}
			
			if(!empty($reply_info['reply_materialid'])){
				$material_info = $model_wechat->getInfoOne('weixin_material',array('material_id'=>intval($reply_info['reply_materialid'])));
				if (!empty($material_info)){
					$material_info['items'] = unserialize($material_info['material_content']);
				}
			}
			core\tpl::output('reply_info',$reply_info);
			core\tpl::output('material_info',$material_info);
			core\tpl::showpage('wechat_keyword_edit');
		}
    }
	
	/**
     * 关键词删除
     **/
    public function keyword_delOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		
		if(empty($_GET['rid']) && empty($_POST['rid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=keyword_manage');
		}
		
		$result = false;
		if(!empty($_GET['rid'])){
			$result = $model_wechat->delInfo('weixin_reply',array('reply_id'=>intval($_GET['rid'])));
		}
		
		if(!empty($_POST['rid'])){
			$where['reply_id'] = array('in', $_POST['rid']);
			$result = $model_wechat->delInfo('weixin_reply',$where);
		}
		
		if($result){
			success($lang['nc_common_del_succ'],'index.php?act='.$_GET['act'].'&op=keyword_manage');
		}else{
			error($lang['nc_common_del_fail'],'index.php?act='.$_GET['act'].'&op=keyword_manage');
		}
    }
	
	/*URL列表*/
	public function url_manageOp(){
		$lang = core\language::getLangContent();
		$model_wechat = model('wechat');
		$condition = array();
        if(!empty($_GET['classid'])) {
            $condition['url_classid'] = intval($_GET['classid']);
        }else{
			$_GET['classid'] = '';
		}
		
		if (!empty($_GET['keywords'])) {
            $condition['url_'.$_GET['fields']] = array('like', '%' . trim($_GET['keywords']) . '%');
        }else{
			$_GET['keywords'] = '';
		}
		
        $url_list = $model_wechat->getInfoList('weixin_url',$condition,10,'url_addtime desc');
		$url_class = $model_wechat->getInfoList('weixin_url_class',array());
		core\tpl::output('url_class',$url_class);
		core\tpl::output('url_list',$url_list);
		core\tpl::output('show_page',$model_wechat->showpage('2'));
		core\tpl::showpage('wechat_url_list');
	}
	
	/**
     * URL添加
     **/
    public function url_addOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		if (chksubmit()){
			if(empty($_POST['name'])){
				error($lang['not_info_url_name']);
			}
			if(empty($_POST['urllink'])){
				error($lang['not_info_url_link']);
			}
			$_POST['urllink'] = strpos($_POST['urllink'],'http://')>0 ? trim($_POST['urllink']) : 'http://'.trim($_POST['urllink']);
			$update_array = array(
				'url_name'=>trim($_POST['name']),
				'admin_id'=>0,
				'url_link'=>$_POST['urllink'],
				'url_classid'=>1,
				'url_addtime'=>time()
			);
			
			$result = $model_wechat->addInfo('weixin_url',$update_array);
			if ($result){
				success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=url_manage');
			}else {
				error($lang['nc_common_save_fail']);
			}
		}else{
			core\tpl::showpage('wechat_url_add');
		}
    }
	
	/**
     * URL修改
     **/
    public function url_editOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		if (chksubmit()){
			if(empty($_POST['urlid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=url_manage');
			}
			
			if(empty($_POST['name'])){
				error($lang['not_info_url_name']);
			}
			
			if(empty($_POST['urllink'])){
				error($lang['not_info_url_link']);
			}
			$_POST['urllink'] = strpos($_POST['urllink'],'http://')>-1 ? trim($_POST['urllink']) : 'http://'.trim($_POST['urllink']);
			$update_array = array(
				'url_name'=>trim($_POST['name']),
				'url_link'=>$_POST['urllink']
			);
			
			$condition = array('url_id'=>intval($_POST['urlid']));
			
			$result = $model_wechat->editInfo('weixin_url',$update_array,$condition);
			if ($result === true){
				success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=url_manage');
			}else {
				error($lang['nc_common_save_fail'],'index.php?act='.$_GET['act'].'&op=url_manage');
			}
		}else{
			$url_info = array();
			if(empty($_GET['urlid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=url_manage');
			}
			$url_info = $model_wechat->getInfoOne('weixin_url',array('url_id'=>intval($_GET['urlid'])));
			if(empty($url_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=url_manage');
			}
			
			core\tpl::output('url_info',$url_info);
			core\tpl::showpage('wechat_url_edit');
		}
    }
	
	/**
     * URL删除
     **/
    public function url_delOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		
		if(empty($_GET['urlid']) && empty($_POST['urlid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=url_manage');
		}
		
		$result = false;
		if(!empty($_GET['urlid'])){
			$result = $model_wechat->delInfo('weixin_url',array('url_id'=>intval($_GET['urlid'])));
		}
		
		if(!empty($_POST['urlid'])){
			$where['url_id'] = array('in', $_POST['urlid']);
			$result = $model_wechat->delInfo('weixin_url',$where);
		}
		
		if($result){
			success($lang['nc_common_del_succ'],'index.php?act='.$_GET['act'].'&op=url_manage');
		}else{
			error($lang['nc_common_del_fail'],'index.php?act='.$_GET['act'].'&op=url_manage');
		}
    }
	
	/*自定义菜单列表*/
	public function menu_manageOp(){
		$lang = core\language::getLangContent();
		$model_wechat = model('wechat');
		$condition = array();
        $menu_list = $model_wechat->getInfoList('weixin_menu',$condition,10,'menu_addtime desc');
		core\tpl::output('menu_list',$menu_list);
		core\tpl::output('show_page',$model_wechat->showpage('2'));
		core\tpl::showpage('wechat_menu_list');
	}
	
	/**
     * 自定义菜单添加
     **/
    public function menu_addOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		if (chksubmit()){
			if(empty(trim($_POST['MenuTitle']))){
				error($lang['wechat_not_title']);
			}
			
			if(empty($_POST['Title'])){
				error($lang['wechat_not_menu']);
			}
			$flag = true;
			$model_wechat->beginTransaction();
			$menu = array(
				'menu_name'=>trim($_POST['MenuTitle']),
				'menu_addtime'=>time()
			);
			$menuid = $model_wechat->addInfo('weixin_menu',$menu);
			$flag = $flag && $menuid;
			$i = 0;
			foreach($_POST['Title'] as $key=>$value){
				if(empty($_POST['Title'][$key][0])){
					continue;
				}
				$i++;
				if(!empty($_POST['Url'][$key][0])){
					$_POST['Url'][$key][0] = strpos($_POST['Url'][$key][0],'http://')>-1 ? trim($_POST['Url'][$key][0]) : 'http://'.trim($_POST['Url'][$key][0]);
				}
				$first = array(
					'detail_name'=>$_POST['Title'][$key][0],
					'menu_id'=>$menuid,
					'detail_msgtype'=>$_POST['MsgType'][$key][0],
					'detail_textcontents'=>$_POST['TextContents'][$key][0],
					'detail_materialid'=>$_POST['MaterialID'][$key][0],
					'detail_url'=>$_POST['Url'][$key][0],
					'detail_sort'=>$i
				);
				$parentid = $model_wechat->addInfo('weixin_menu_detail',$first);
				$flag = $flag && $parentid;
				$j=0;
				$detail = array();
				ksort($value);
				foreach($value as $k=>$v){
					if(empty($_POST['Title'][$key][$k]) || $k==0){
						continue;
					}
					$j++;
					if(!empty($_POST['Url'][$key][$k])){
						$_POST['Url'][$key][$k] = strpos($_POST['Url'][$key][$k],'http://')>-1 ? trim($_POST['Url'][$key][$k]) : 'http://'.trim($_POST['Url'][$key][$k]);
					}
					$detail[] = array(
						'detail_name'=>$_POST['Title'][$key][$k],
						'menu_id'=>$menuid,
						'detail_msgtype'=>$_POST['MsgType'][$key][$k],
						'detail_textcontents'=>$_POST['TextContents'][$key][$k],
						'detail_materialid'=>$_POST['MaterialID'][$key][$k],
						'detail_url'=>$_POST['Url'][$key][$k],
						'detail_sort'=>$j,
						'parent_id'=>$parentid
					);
				}
				if(!empty($detail)){
					$child = $model_wechat->addAll('weixin_menu_detail',$detail);
					$flag = $flag && $child;
				}
				
			}
			
			if($flag){
				$model_wechat->commit();
				success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=menu_manage');
			}else{
				$model_wechat->rollback();
				error($lang['nc_common_save_fail']);
			}
			
		}else{
			core\tpl::showpage('wechat_menu_add');
		}
    }
	
	/**
     * 自定义菜单删除
     **/
    public function menu_delOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		$result = false;

		if(empty($_GET['mid']) && empty($_POST['mid'])){
			error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=menu_manage');
		}
		
		$mid = array();
		if(!empty($_GET['mid'])){
			$mid[] = $_GET['mid'];
		}
		
		if(!empty($_POST['mid'])){
			$mid = $_POST['mid'];
		}
		
		$where['menu_id'] = array('in', $mid);
		$where['menu_status'] = 1;
		$menu_info = $model_wechat->getInfoOne('weixin_menu',$where);
		
		$condition['menu_id'] = array('in', $mid);
		$result = $model_wechat->delInfo('weixin_menu_detail',$condition);
		$result = $model_wechat->delInfo('weixin_menu',$condition);
		if(!empty($menu_info)){
			$response = $this->deletemenu();
			if($response['status']==0){
				error($lang[$response['msg']],'index.php?act='.$_GET['act'].'&op=menu_manage');
			}else{
				success($lang[$response['msg']],'index.php?act='.$_GET['act'].'&op=menu_manage');
			}
		}else{
			if($result){
				success($lang['nc_common_del_succ'],'index.php?act='.$_GET['act'].'&op=menu_manage');
			}else{
				success($lang['nc_common_del_fail'],'index.php?act='.$_GET['act'].'&op=menu_manage');
			}
		}
    }
	
	/**
     * 自定义菜单添加
     **/
    public function menu_editOp() {
		$lang = core\language::getLangContent();
        $model_wechat = model('wechat');
		if (chksubmit()){
			if(empty(trim($_POST['MenuTitle']))){
				error($lang['wechat_not_title']);
			}
			
			if(empty($_POST['Title'])){
				error($lang['wechat_not_menu']);
			}
			
			if(empty($_POST['mid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=menu_manage');
			}
			
			$menu_info = $model_wechat->getInfoOne('weixin_menu',array('menu_id'=>intval($_POST['mid'])),'menu_status');
			if(empty($menu_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=menu_manage');
			}
			
			$flag = true;
			$model_wechat->beginTransaction();
			
			$result = $model_wechat->delInfo('weixin_menu_detail',array('menu_id'=>intval($_POST['mid'])));
			$flag = $flag && $result;
			
			$menu = array(
				'menu_name'=>trim($_POST['MenuTitle'])
			);
			
			$result = $model_wechat->editInfo('weixin_menu',$menu,array('menu_id'=>intval($_POST['mid'])));
			$flag = $flag && $result;
			
			$i = 0;
			foreach($_POST['Title'] as $key=>$value){
				if(empty($_POST['Title'][$key][0])){
					continue;
				}
				$i++;
				if(!empty($_POST['Url'][$key][0])){
					$_POST['Url'][$key][0] = strpos($_POST['Url'][$key][0],'http://')>-1 ? trim($_POST['Url'][$key][0]) : 'http://'.trim($_POST['Url'][$key][0]);
				}
				$first = array(
					'detail_name'=>$_POST['Title'][$key][0],
					'menu_id'=>$_POST['mid'],
					'detail_msgtype'=>$_POST['MsgType'][$key][0],
					'detail_textcontents'=>$_POST['TextContents'][$key][0],
					'detail_materialid'=>$_POST['MaterialID'][$key][0],
					'detail_url'=>$_POST['Url'][$key][0],
					'detail_sort'=>$i
				);
				$parentid = $model_wechat->addInfo('weixin_menu_detail',$first);
				$flag = $flag && $parentid;
				$j=0;
				$detail = array();
				ksort($value);
				foreach($value as $k=>$v){
					if(empty($_POST['Title'][$key][$k]) || $k==0){
						continue;
					}
					$j++;
					if(!empty($_POST['Url'][$key][$k])){
						$_POST['Url'][$key][$k] = strpos($_POST['Url'][$key][$k],'http://')>-1 ? trim($_POST['Url'][$key][$k]) : 'http://'.trim($_POST['Url'][$key][$k]);
					}
					$detail[] = array(
						'detail_name'=>$_POST['Title'][$key][$k],
						'menu_id'=>$_POST['mid'],
						'detail_msgtype'=>$_POST['MsgType'][$key][$k],
						'detail_textcontents'=>$_POST['TextContents'][$key][$k],
						'detail_materialid'=>$_POST['MaterialID'][$key][$k],
						'detail_url'=>$_POST['Url'][$key][$k],
						'detail_sort'=>$j,
						'parent_id'=>$parentid
					);
				}
				if(!empty($detail)){
					$child = $model_wechat->addAll('weixin_menu_detail',$detail);
					$flag = $flag && $child;
				}
				
			}
			
			if($flag){
				$model_wechat->commit();
				if($menu_info['menu_status']==1){
					$response = $this->publish($_POST['mid']);
					if($response['status']==0){
						error($lang[$response['msg']],'index.php?act='.$_GET['act'].'&op=menu_manage');
					}else{
						success($lang[$response['msg']],'index.php?act='.$_GET['act'].'&op=menu_manage');
					}
				}else{
					success($lang['nc_common_save_succ'],'index.php?act='.$_GET['act'].'&op=menu_manage');
				}				
			}else{
				$model_wechat->rollback();
				error($lang['nc_common_save_fail']);
			}
			
		}else{
			$menu_info = array();
			if(empty($_GET['mid'])){
				error($lang['not_info_id'],'index.php?act='.$_GET['act'].'&op=menu_manage');
			}
			$menu_info = $model_wechat->getInfoOne('weixin_menu',array('menu_id'=>intval($_GET['mid'])));
			if(empty($menu_info)){
				error($lang['info_not_exist'],'index.php?act='.$_GET['act'].'&op=menu_manage');
			}
			
			$first_info = $second_info = array();
			$result = $model_wechat->getInfoList('weixin_menu_detail',array('menu_id'=>intval($_GET['mid'])),'','parent_id asc, detail_sort asc');
			$i = $j = 0;
			$child_info = array();
			if(!empty($result)){
				foreach($result as $key=>$value){
					if($value['parent_id']==0){
						$i++;
						$child_info[$value['detail_id']] = 0;
						$first_info[$i] = $value;
					}else{
						$j++;
						$child_info[$value['parent_id']] = $child_info[$value['parent_id']] + 1;
						$second_info[$value['parent_id']]['child'][$j] = $value;
					}
				}
			}
			
			core\tpl::output('firstnum',count($first_info));
			core\tpl::output('menu_info',$menu_info);
			core\tpl::output('child_info',$child_info);
			core\tpl::output('first_info',$first_info);
			core\tpl::output('j',$j);
			core\tpl::output('second_info',$second_info);
			core\tpl::showpage('wechat_menu_edit');
		}
    }
	
	public function menu_publishOp(){
		$lang = core\language::getLangContent();
        $response = $this->publish($_GET['mid']);
		if($response['status']==0){
			error($lang[$response['msg']],'index.php?act='.$_GET['act'].'&op=menu_manage');
		}else{
			success($lang[$response['msg']],'index.php?act='.$_GET['act'].'&op=menu_manage');
		}
	}
	
	/**
	 * ajax操作
	 */
	public function ajaxOp(){
		$lang = core\language::getLangContent();
		$model_wechat = model('wechat');
		switch ($_GET['branch']){
			case 'check_keywords':
				$keywords = trim($_GET['keywords'],'|');
				$rid = empty($_GET['rid']) ? 0 : intval($_GET['rid']);
				$array = explode('|',$keywords);
				foreach($array as $a){
					if(trim($a)=='') continue;
					
					$condition['reply_keywords'] = array('like', '%|' . trim($a) . '|%');
					if($rid>0){
						$condition['reply_id'] = array('neq',$rid);
					}
					
					$reply_info = $model_wechat->getInfoOne('weixin_reply',$condition);
					if(!empty($reply_info)){
						echo 'false';
						exit;
					}
				}
				
				echo 'true';
				exit;
			break;
			case 'get_material':
				if(empty($_GET['mid'])){
					$data['msg'] = '<div class="item"></div>';
					echo json_encode($data);
					exit;
				}
				$material_info = $model_wechat->getInfoOne('weixin_material',array('material_id'=>intval($_GET['mid'])));
				if (empty($material_info)){
					$data['msg'] = '<div class="item"></div>';
					echo json_encode($data);
					exit;
				}
				
				$items = unserialize($material_info['material_content']);
				if(!is_array($items)){
					$data['msg'] = '<div class="item"></div>';
					echo json_encode($data);
					exit;
				}
				
				$html = '';
				if($material_info['material_type'] == 1){
					$html .= '<div class="item one">';
					foreach($items as $k=>$v){
                  		$html .= '<div class="title">'.$v['Title'].'</div><div>'.date("Y-m-d",$material_info['material_addtime']).'</div><div class="img"><img src="'.UPLOAD_SITE_URL.$v['ImgPath'].'" /></div><div class="txt">'.str_replace(array("\r\n", "\r", "\n"), "<br />",$v['TextContents']).'</div>';
                 	}
					$html .= '</div>';
				}else{
					$html .= '<div class="item multi">';
					$html .= '<div class="time">'.date("Y-m-d",$material_info['material_addtime']).'</div>';
                  	foreach($items as $k=>$v){
                  		$html .= '<div class="'.($k>0 ? "list" : "first").'"><div class="info"><div class="img"><img src="'.UPLOAD_SITE_URL.$v['ImgPath'].'" /></div><div class="title">'.$v['Title'].'</div></div></div>';
                  	}
					$html .= '</div>';
				}
				$data['msg'] = $html;
				echo json_encode($data);
				exit;
			break;
		}
	}
	
	private function publish($menuid){
		$model_wechat = model('wechat');
		
		if(empty($menuid)){
			return array('status'=>0,'msg'=>'not_info_id');
		}
		
		$api_account = $model_wechat->getInfoOne('weixin_wechat','');
	
		if(empty($api_account["wechat_appid"]) || empty($api_account["wechat_appsecret"])){
			return array('status'=>0,'msg'=>'not_appid');
		}
		
		$result = $model_wechat->getInfoList('weixin_menu_detail',array('menu_id'=>intval($menuid)),'','parent_id asc, detail_sort asc');
		if(empty($result)){
			return array('status'=>0,'msg'=>'not_menu_data');
		}
		
		$ACCESS_TOKEN = logic('weixin_token')->get_access_token();
		if(!$ACCESS_TOKEN){
			return array('status'=>0,'msg'=>'get_access_token_fail');
		}
		
		$first_menu = $child_menu = array();
		foreach($result as $key=>$value){
			if($value['parent_id']==0){
				$first_menu[] = $value;
			}else{
				$child_menu[$value['parent_id']][] = $value;
			}
		}
		
		$Menu=array();
		foreach($first_menu as $key=>$value){
			if(!empty($child_menu[$value['detail_id']])){
				$Data=array(
					"name"=>$value["detail_name"],
					"sub_button"=>array()
				);
				$sub_button = array_reverse($child_menu[$value['detail_id']]);
				foreach($sub_button as $k=>$v){
					if($v["detail_msgtype"]==0){
						$Data["sub_button"][]=array(
							"type"=>"click",
							"name"=>$v["detail_name"],
							"key"=>strlen($v["detail_textcontents"])>=120 ? "changwenben_".$v["detail_id"] : $v["detail_textcontents"]
						);
					}elseif($v["detail_msgtype"]==1){
						$Data["sub_button"][]=array(
							"type"=>"click",
							"name"=>$v["detail_name"],
							"key"=>"MaterialID_".$v["detail_materialid"]
						);
					}elseif($v["detail_msgtype"]==2){
						$Data["sub_button"][]=array(
							"type"=>"view",
							"name"=>$v["detail_name"],
							"url"=>$v["detail_url"]
						);
					}
				}
			}else{
				if($value["detail_msgtype"]==0){
					$Data=array(
						"type"=>"click",
						"name"=>$value["detail_name"],
						"key"=>strlen($value["detail_textcontents"])>=120 ? "changwenben_".$value["detail_id"] : $value["detail_textcontents"]
					);
				}elseif($value["detail_msgtype"]==1){
					$Data=array(
						"type"=>"click",
						"name"=>$value["detail_name"],
						"key"=>"MaterialID_".$value["detail_materialid"]
					);
				}elseif($value["detail_msgtype"]==2){
					$Data=array(
						"type"=>"view",
						"name"=>$value["detail_name"],
						"url"=>$value["detail_url"]
					);
				}
			}
			$Menu["button"][]=$Data;
		}
		$response = logic('weixin_token')->curl_post('https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$ACCESS_TOKEN,$Menu);
		
		if(empty($response['errcode'])){
			$model_wechat->editInfo('weixin_menu',array('menu_status'=>0),'');
			$model_wechat->editInfo('weixin_menu',array('menu_status'=>1),array('menu_id'=>$menuid));
			return array('status'=>1,'msg'=>'menu_publish_success');
			
		}else{
			return array('status'=>0,'msg'=>'menu_publish_fail');
		}
	}
	
	private function deletemenu(){
		$model_wechat = model('wechat');
		
		$api_account = $model_wechat->getInfoOne('weixin_wechat','');
	
		if(empty($api_account["wechat_appid"]) || empty($api_account["wechat_appsecret"])){
			return array('status'=>0,'msg'=>'not_appid');
		}
		
		$ACCESS_TOKEN = logic('weixin_token')->get_access_token();
		if(!$ACCESS_TOKEN){
			return array('status'=>0,'msg'=>'get_access_token_fail');
		}
		
		$response = logic('weixin_token')->curl_get('https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$ACCESS_TOKEN);
		
		if(empty($response['errcode'])){
			return array('status'=>1,'msg'=>'menu_delete_success');			
		}else{
			return array('status'=>0,'msg'=>'menu_delete_fail');
		}
	}
}

