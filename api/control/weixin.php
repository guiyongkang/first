<?php
namespace api\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class weixin extends SystemControl
{
	protected $wechatsn = '';
	protected $rsconfig = array();
    public function __construct()
    {
        parent::__construct();
		
		core\language::read('weixin');
		$lang = core\language::getLangContent();
		if(!empty($_GET['wsn'])) {
			$this->wechatsn = trim($_GET['wsn']);
		}else{
			
			exit($lang['url_not_param']);
		}
		$this->rsconfig = model('wechat')->getInfoOne('weixin_wechat',array('wechat_sn'=>$this->wechatsn));
        
    }
	public function indexOp(){
		//调试
        try{
			
            $appid = isset($this->rsconfig['wechat_appid']) ? $this->rsconfig['wechat_appid'] : ''; //AppID(应用ID)
            $token = isset($this->rsconfig['wechat_token']) ? $this->rsconfig['wechat_token'] : ''; //微信后台填写的TOKEN
            $crypt = isset($this->rsconfig['wechat_encoding']) ? $this->rsconfig['wechat_encoding'] : ''; //消息加密KEY（EncodingAESKey）
            
            /* 加载微信SDK */
            $wechat = new lib\wxSDk\Wechat($token, $appid, $crypt);
            
            /* 获取请求信息 */
            $data = $wechat->request();

            if($data && is_array($data)){
                /**
                 * 你可以在这里分析数据，决定要返回给用户什么样的信息
                 * 接受到的信息类型有10种，分别使用下面10个常量标识
                 * lib\wxSDk\Wechat::MSG_TYPE_TEXT       //文本消息
                 * lib\wxSDk\Wechat::MSG_TYPE_IMAGE      //图片消息
                 * lib\wxSDk\Wechat::MSG_TYPE_VOICE      //音频消息
                 * lib\wxSDk\Wechat::MSG_TYPE_VIDEO      //视频消息
                 * lib\wxSDk\Wechat::MSG_TYPE_SHORTVIDEO //视频消息
                 * lib\wxSDk\Wechat::MSG_TYPE_MUSIC      //音乐消息
                 * lib\wxSDk\Wechat::MSG_TYPE_NEWS       //图文消息（推送过来的应该不存在这种类型，但是可以给用户回复该类型消息）
                 * lib\wxSDk\Wechat::MSG_TYPE_LOCATION   //位置消息
                 * lib\wxSDk\Wechat::MSG_TYPE_LINK       //连接消息
                 * lib\wxSDk\Wechat::MSG_TYPE_EVENT      //事件消息
                 *
                 * 事件消息又分为下面五种
                 * lib\wxSDk\Wechat::MSG_EVENT_SUBSCRIBE    //订阅
                 * lib\wxSDk\Wechat::MSG_EVENT_UNSUBSCRIBE  //取消订阅
                 * lib\wxSDk\Wechat::MSG_EVENT_SCAN         //二维码扫描
                 * lib\wxSDk\Wechat::MSG_EVENT_LOCATION     //报告位置
                 * lib\wxSDk\Wechat::MSG_EVENT_CLICK        //菜单点击
                 */

                //记录微信推送过来的数据
                file_put_contents('./data.json', json_encode($data));

                /* 响应当前请求(自动回复) */
                //$wechat->response($content, $type);

                /**
                 * 响应当前请求还有以下方法可以使用
                 * 具体参数格式说明请参考文档
                 * 
                 * $wechat->replyText($text); //回复文本消息
                 * $wechat->replyImage($media_id); //回复图片消息
                 * $wechat->replyVoice($media_id); //回复音频消息
                 * $wechat->replyVideo($media_id, $title, $discription); //回复视频消息
                 * $wechat->replyMusic($title, $discription, $musicurl, $hqmusicurl, $thumb_media_id); //回复音乐消息
                 * $wechat->replyNews($news, $news1, $news2, $news3); //回复多条图文消息
                 * $wechat->replyNewsOnce($title, $discription, $url, $picurl); //回复单条图文消息
                 * 
                 */
                
                //执行Demo
                $this->demo($wechat, $data);
				//$Data = array(
				//	'HTTP_RAW_POST_DATA' => json_encode($data),
				//	'CreateTime' => time()
				//);
				//model('HTTP_RAW_POST_DATA')->insert($Data);
            }
        } catch(\Exception $e){
            file_put_contents('./error.json', json_encode($e->getMessage()));
        }
	}
	/**
     * DEMO
     * @param  Object $wechat Wechat对象
     * @param  array  $data   接受到微信推送的消息
     */
    private function demo($wechat, $data){
		$model_wechat = model('wechat');
        switch ($data['MsgType']) {
            case lib\wxSDk\Wechat::MSG_TYPE_EVENT:
                switch ($data['Event']) {
                    case lib\wxSDk\Wechat::MSG_EVENT_SUBSCRIBE:
												
						$rsReply = $model_wechat->getInfoOne('weixin_attention','');
						if($rsReply){
							if($rsReply['reply_msgtype']){
								$array = $this->get_material($rsReply['reply_materialid']);								
								$wechat->replyNews($array);
							}else{
								$contentStr=$rsReply['reply_textcontents'];								
							}
						}else{
							$contentStr = 'I have not decided yet';
						}
                        if(isset($contentStr)){
							$wechat->replyText($contentStr);
						}
						
						//会员处理
						if(!empty($data['EventKey'])){//扫码
							$ownerid = intval(str_replace('qrscene_','',$data['EventKey']));
						}else{
							$ownerid = 0;
						}
						$user_info = $this->register($data['FromUserName'],$ownerid);
							
						if(!empty($user_info['member_id'])){//注册成功
							$subscribe_data = array(
								'member_id'=>$user_info['member_id'],
								'open_id'=>$data['FromUserName'],
								'action'=>'subscribe',
								'addtime'=>time()
							);
							$flag = model('wechat')->addInfo('weixin_member_action',$subscribe_data);										
						}
						exit;
                        break;

                    case lib\wxSDk\Wechat::MSG_EVENT_UNSUBSCRIBE:
                        //取消关注，记录日志
						$member_info = model('member')->getMemberInfo(array('weixin_unionid' => $data['FromUserName']));
						if(!empty($member_info)){
							$unsubscribe_data = array(
								'member_id'=>$member_info['member_id'],
								'open_id'=>$data['FromUserName'],
								'action'=>'unsubscribe',
								'addtime'=>time()
							);
							model('wechat')->addInfo('weixin_member_action',$unsubscribe_data);
						}
						exit;
                        break;
					case lib\wxSDk\Wechat::MSG_EVENT_SCAN://二维码扫描
					    
					    break;
					case lib\wxSDk\Wechat::MSG_EVENT_LOCATION://报告位置
					    
					    break;
					case lib\wxSDk\Wechat::MSG_EVENT_CLICK://菜单点击
					    $EventKey = explode('_', $data['EventKey']);
						if($EventKey[0] == 'MenuID'){
							$rsMenu = $model_wechat->getInfoOne('weixin_menu_detail',array('detail_id'=>$EventKey[1]),'detail_textcontents');
							if($rsMenu){
								$contentStr = $rsMenu['detail_textcontents'];						
							}
						}elseif($EventKey[0] == 'MaterialID'){
							$array = $this->get_material($EventKey[1]);
							$wechat->replyNews($array);
						}elseif($EventKey[0]=='changwenben'){
							$rsMenu = $model_wechat->getInfoOne('weixin_menu_detail',array('detail_id'=>$EventKey[1]),'detail_textcontents');
							if($rsMenu){
								$contentStr = $rsMenu['detail_textcontents'];				
							}else{
								$contentStr = $EventKey[0];
							}
						}else{
							$contentStr = $EventKey[0];
						}
						if(isset($contentStr)){
							$wechat->replyText($contentStr);
						}
						exit;
					    break;
					case lib\wxSDk\Wechat::MSG_EVENT_VIEW:// ...
					    
					    break;
                    default:
                        $wechat->replyText('您的事件类型：' . $data['Event'] . '，EventKey：' . $data['EventKey']);
						exit;
                        break;
                }
                break;

            case lib\wxSDk\Wechat::MSG_TYPE_TEXT://关键词自动回复
                if(empty($data['Content'])){
					$contentStr = '请说些什么...';
				}else{
					$rsReply = model()->table('weixin_reply')->field('reply_msgtype,reply_textcontents,reply_materialid')->where(array('reply_patternmethod'=>0,'reply_keywords'=>$data['Content']))->order('reply_addtime desc')->find();
					if(!$rsReply){
						$rsReply = model()->table('weixin_reply')->field('reply_msgtype,reply_textcontents,reply_materialid')->where(array('reply_patternmethod'=>1,'reply_keywords'=>'%|' . $data['Content'] . '|%'))->order('reply_addtime desc')->find();
						if(!$rsReply){
							$rsReply = model()->table('weixin_attention')->field('reply_msgtype,reply_textcontents,reply_materialid')->where(array('reply_subscribe'=>1))->find();
						}
					}
					if($rsReply){
						if($rsReply['reply_msgtype']){
							$array = $this->get_material($rsReply['reply_materialid']);
                            $wechat->replyNews($array);
						}else{
							$contentStr = $rsReply['reply_textcontents']; 
						}										  									  
					}else{
						$contentStr = 'I have not decided yet';
					}
				}
				if(isset($contentStr)){
					$wechat->replyText($contentStr);
				}
				exit;
                break;
            
            default:
                //other code
                break;
        }
    }
	private function get_material($id){
		$rsMaterial = model()->table('weixin_material')->field('material_type,material_content')->where(array('material_id'=>$id))->find();
		$Material_Json = unserialize($rsMaterial['material_content']);
		$array = array();
		
		foreach($Material_Json as $key=>$value){
			$array[] = array(
				$value['Title'],
				preg_replace('/<br\\s*?\/??>/i', chr(13), $value['TextContents']),
				strpos($value['Url'], 'http://') > -1 ? $value['Url'] : 'http://' . $_SERVER['HTTP_HOST'] . $value['Url'],
				strpos($value['ImgPath'],'http://')>-1 ? $value['ImgPath'] : UPLOAD_SITE_URL . $value['ImgPath']
			);
		}
		return $array;
	}
	
	private function register($openid,$ownerid=0)
    {	
		$model_member = model('member');
		$member_info_second = $model_member->getMemberInfo(array('weixin_unionid' => $openid));
		
		if(empty($member_info_second)){
			if($ownerid == 0){
				$dis_setting = model('distributor_setting')->field('member_inviter')->find();
				if($dis_setting['member_inviter']==1){
					return array('register'=>0,'member_id'=>0);
				}
			}
			
			$member_id = 0;
			$member_name = 'name_' . rand(100, 899);
			$member_info = $model_member->getMemberInfo(array('member_name' => $member_name));
			$password = rand(100000, 999999);
			$member = array();
			$member['inviter_id'] = $ownerid;
			$member['member_passwd'] = $password;
			$member['member_email'] = '';
			$member['weixin_unionid'] = $openid;
			$member['member_wxopenid'] = $openid;
			$member['weixin_info'] = array();
			if (empty($member_info)) {
				$member['member_name'] = $member_name;
				$result = $model_member->addMember($member);
			} else {
				for ($i = 1; $i < 999; $i++) {
					$rand += $i;
					$member_name = 'name_' . rand(100, 899);
					$member_info = $model_member->getMemberInfo(array('member_name' => $member_name));
					if (empty($member_info)) {
						//查询为空表示当前会员名可用
						$member['member_name'] = $member_name;
						$result = $model_member->addMember($member);
						break;
					}
				}
			}
			
			//异步更新会员微信相关信息
			
			$postdata = array(
				'config'=>$this->rsconfig,
				'openid'=>$openid,
				'member_id'=>$result,
				'ownerid'=>$ownerid
			);
			$url = API_SITE_URL . DS . 'index.php?act=weixin_bind&op=subscribe';
			$curl = new lib\curl(3);
			$curl->curl_post($url,$postdata);
			
			return array('register'=>1,'member_id'=>$result);
		}else{
			return array('register'=>0,'member_id'=>$member_info_second['member_id']);
		}
    }
}