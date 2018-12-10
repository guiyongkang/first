<?php
/**
 * 微信相关接口功能
**/
namespace api\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class weixin_bind extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function subscribeOp()
    {
		$postdata = json_decode(file_get_contents("php://input"),true);
		$config = $postdata['config'];
		$openid = $postdata['openid'];
		$member_id = $postdata['member_id'];
		$ownerid = $postdata['ownerid'];
		
		$access_token = logic('weixin_token')->get_access_token($config);
		if(!empty($access_token) && $member_id){
			
			//获取用户信息
			$url = 	"https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
			$curl = new lib\curl();
			$weixin_info = $curl->curl_get($url);
			
			if(empty($weixin_info['errcode'])){
				$model_member = model('member');
				$model_distributor = model('distributor');
				$headimgurl = $weixin_info['headimgurl'];
				//用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像）
				$headimgurl = substr($headimgurl, 0, -1) . '132';
				$avatar = copy($headimgurl, BASE_UPLOAD_PATH . '/' . ATTACH_AVATAR . '/avatar_' . $member_id . '.jpg');
				$member_name = $weixin_info["nickname"];
				if($avatar && $weixin_info["nickname"]){
					$model_member->editMember(array('member_id' => $member_id), array('member_name'=>$weixin_info["nickname"],'member_avatar' => 'avatar_' . $member_id . '.jpg'));
				}
				
				//发送微信短信
				$disconfig = $model_distributor->getInfoOne('distributor_setting','','dis_bonus_level,dis_bonus_name');
				$flag = logic('weixin_message')->addmemberself($access_token, $disconfig ,$config, $member_name, $openid, $member_id);
				if($ownerid>0){				
					$flag = logic('weixin_message')->addmember($access_token, $disconfig ,$config, $member_name, $ownerid);
				}
			}
		}
	}
}