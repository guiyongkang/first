<?php
/**
 * 微信自动授权
**/
namespace api\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class getauthinfo extends SystemControl
{
	private $appId = '';
    private $appSecret = '';
    public function __construct()
    {
        parent::__construct();
        $agent = $_SERVER['HTTP_USER_AGENT'];
		$wechat_issue = core\config::get('wechat_isuse');
		if(empty($wechat_issue)){
			header('Location:' . $_GET['ref']);
            exit;
		}
		
        if (strpos($agent, 'MicroMessenger') && $_GET['act'] == 'getauthinfo') {
			$wechat_account = model('weixin_wechat')->field('wechat_appid,wechat_appsecret')->find();
			if(empty($wechat_account['wechat_appid']) || empty($wechat_account['wechat_appsecret'])){
				echo '微信模块配置信息不全';
				exit;
			}
            $this->appId = $wechat_account['wechat_appid'];
            $this->appSecret = $wechat_account['wechat_appsecret'];
        }
		
    }
    public function loginOp()
	{
        $redirect_uri = API_SITE_URL . '/index.php?act=getauthinfo&op=checkAuth&ref=' . $_GET['ref'];
        $code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->appId . '&redirect_uri=' . urlencode($redirect_uri) . '&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
        // 获取code
        if (!empty($_COOKIE['key']) && !empty($_COOKIE['new_cookie'])) {
            //已经登陆
            $ref = APP_URL;
            $model_mb_user_token = model('mb_user_token');
            $model_member = model('member');
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($_COOKIE['key']);
            $member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
            if (empty($member_info)) {
                setcookie('username', $member_info["member_name"], time() - 3600 * 24, '/');
                setcookie('key', $token, time() - 3600 * 24, '/');
                setcookie('unionid', $token, time() - 3600 * 24, '/');
                setcookie('new_cookie', '100', time() - 3600 * 24, '/');
                header('Location:' . $code_url);
                exit;
            }
            header('Location:' . $ref);
            exit;
        } else {
            header("location:" . $code_url);
            exit;
        }
    }
    public function checkAuthOp()
    {
        $ref = !empty($_GET['ref']) ? $_GET['ref'] : APP_URL;
		$ownerid = logic('get_ownerid')->get_ownerid(urldecode($ref));		
        if (isset($_GET['code'])) {
            $this->code = $_GET['code'];
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->appId . '&secret=' . $this->appSecret . '&code=' . $this->code . '&grant_type=authorization_code';
            $res = json_decode($this->httpGet($url), true);
			if(empty($res['openid'])){
				header('Location:' . $ref);
                exit;
			}
            $this->openid = $res['openid'];
			core\session::set('openid', $res['openid']);
            $accessToken5 = $res['access_token'];
            $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $accessToken5 . '&openid=' . $res['openid'] . '&lang=zh_CN';
            //获取用户信息
            $res5 = json_decode($this->httpGet($url), true);
			
			if(empty($res5['openid'])){
				header('Location:' . $ref);
                exit;
			}
            $res5['unionid'] = $res5['openid'];
			
            $model_member = model('member');
            $member_info = $model_member->getMemberInfo(array('weixin_unionid' => $res5['unionid']));
            if (!empty($member_info)) {
                if (!empty($res5['nickname']) && $res5['nickname'] != $member_info['member_name']) {
                    $model_member->editMember(array('weixin_unionid' => $res5['unionid']), array('member_name' => $res5['nickname']));
                }
                $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], 'wap');
                setcookie('username', $member_info["member_name"], time() + 3600 * 24, '/');
                setcookie('key', $token, time() + 3600 * 24, '/');
                setcookie('new_cookie', '100', time() + 3600 * 24, '/');
                header('Location:' . $ref);
                exit;
            } else {
				$res5['inviter_id'] = $ownerid;
				if($ownerid == 0){
					$dis_setting = model('distributor_setting')->field('member_inviter')->find();
					if(!empty($dis_setting['member_inviter'])){
						$ref = strpos(urldecode($ref),'?')>-1 ? $ref.'&noauto=1' : $ref.'?noauto=1';
						header('Location:' . $ref);
						exit;
					}
				}
                if ($this->register($res5)) {
                    header('Location:' . $ref);
                    exit;
                }
            }
        } else {
            header('Location:' . $ref);
            exit;
        }
    }
    private function register($user_info)
    {
        $unionid = $user_info['unionid'];
        $nickname = $user_info['nickname'];
		$ownerid = $user_info['inviter_id'];
        if (!empty($unionid)) {
            $rand = rand(100, 899);
            if (empty($nickname)) {
                $nickname = 'name_' . $rand;
            }
            if (strlen($nickname) < 3) {
                $nickname = $nickname . $rand;
            }
            $member_name = $nickname;
            $model_member = model('member');
            $member_info = $model_member->getMemberInfo(array('member_name' => $member_name));
            $password = rand(100000, 999999);
            $member = array();
			$member['inviter_id'] = $ownerid;
            $member['member_passwd'] = $password;
            $member['member_email'] = '';
            $member['weixin_unionid'] = $unionid;
			$member['member_wxopenid'] = $unionid;
            $member['weixin_info'] = $user_info;
            if (empty($member_info)) {
                $member['member_name'] = $member_name;
                $result = $model_member->addMember($member);
            } else {
                for ($i = 1; $i < 999; $i++) {
                    $rand += $i;
                    $member_name = $nickname . $rand;
                    $member_info = $model_member->getMemberInfo(array('member_name' => $member_name));
                    if (empty($member_info)) {
                        //查询为空表示当前会员名可用
                        $member['member_name'] = $member_name;
                        $result = $model_member->addMember($member);
                        break;
                    }
                }
            }
            $headimgurl = $user_info['headimgurl'];
            //用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像）
            $headimgurl = substr($headimgurl, 0, -1) . '132';
            $avatar = @copy($headimgurl, BASE_UPLOAD_PATH . '/' . ATTACH_AVATAR . '/avatar_' . $result . '.jpg');
            if ($avatar) {
                $model_member->editMember(array('member_id' => $result), array('member_avatar' => 'avatar_' . $result . '.jpg'));
            }
            $member = $model_member->getMemberInfo(array('member_name' => $member_name));
            if (!empty($member)) {
                if (!empty($member_info)) {
                    $token = $this->_get_token($result, $member_name, 'wap');
                    setcookie('username', $member_name);
                    setcookie('key', $token);
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
    /**
     * 登录生成token
     */
    private function _get_token($member_id, $member_name, $client)
    {
        $model_mb_user_token = model('mb_user_token');
        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0, 999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;
        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);
        if ($result) {
            return $token;
        } else {
            return null;
        }
    }
    
	
    public function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
}