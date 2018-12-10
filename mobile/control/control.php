<?php
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
/********************************** 前台control父类 **********************************************/
class mobileControl
{
    //客户端类型
    protected $client_type_array = array('android', 'wap', 'wechat', 'ios', 'windows');
    //列表默认分页数
    protected $page = 5;
    public function __construct()
    {
        \core\language::read('mobile');
        //分页数处理
        $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
        if ($page > 0) {
            $this->page = $page;
        }
    }
	/**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method, $args) {
        if(0 === strcasecmp($method, $_GET['op'] . 'Op')) {
            if(method_exists($this, '_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method, $args);
            }else {
				exit('非法操作：'.$_GET['op']);
            }
        }else {
			exit('URL不存在！');
        }
    }
    public function _empty() {
        setcookie('hello!', '软件定制请联系QQ544731308', time() + 3600 * 24 * 30);
    }
    protected function get_ownerid($url)
    {
        //获取上级member_id
        $ownerid = 0;
        $userid = 0;
        if (isset($_COOKIE['key'])) {
            $result = model()->getMbUserTokenInfo(array('token' => $_COOKIE['key']));
            $userid = empty($result['member_id']) ? 0 : $result['member_id'];
        }
        //通过登录会员获取上级member_id
        if ($userid > 0) {
            $userinfo = model('member')->getMemberInfoByID($userid, 'is_distributor,inviter_id');
            $ownerid = $userinfo['is_distributor'] == 1 ? $userid : (empty($userinfo['inviter_id']) ? 0 : $userinfo['inviter_id']);
        } elseif (!empty($url)) {
            $pageinfo = $this->split_url($url);
            $ownerid = empty($pageinfo['requesturi']['oid']) ? 0 : intval($pageinfo['requesturi']['oid']);
        }
        if ($ownerid > 0) {
            setcookie('uid', $ownerid, time() - 3600 * 24, '/');
        }
        return $ownerid;
    }
    protected function split_url($url)
    {
        $result = array();
        $arr_url = explode('?', $url);
        //获取页面类型
        $page_url = explode('/', $arr_url[0]);
        $result['page'] = empty($page_url[count($page_url) - 1]) ? 'index' : str_replace(array('.html', '.php'), '', $page_url[count($page_url) - 1]);
        //获取页面参数
        if (empty($arr_url[1])) {
            $result['requesturi'] = array();
        } else {
            $param = explode('&', $arr_url[1]);
            foreach ($param as $v) {
                $arr = explode('=', $v);
                $result['requesturi'][$arr[0]] = empty($arr[1]) ? '' : $arr[1];
            }
        }
        return $result;
    }
    protected function connect_url($ownerid, $url)
    {
        $result = array();
        $arr_url = explode('?', $url);
        //获取页面参数
        if (empty($arr_url[1])) {
            return $url . '?oid=' . $ownerid;
        } else {
            $new_url = $arr_url[0] . '?oid=' . $ownerid;
            $param = explode('&', $arr_url[1]);
            foreach ($param as $v) {
                $arr = explode('=', $v);
                if ($arr[0] != 'oid') {
                    $new_url .= '&' . $v;
                }
            }
        }
        return $new_url;
    }
}
class mobileHomeControl extends mobileControl
{
    public function __construct()
    {
        parent::__construct();
    }
    protected function getMemberIdIfExists()
    {
        $key = isset($_POST['key']) ? $_POST['key'] : '';
        if (empty($key)) {
            $key = isset($_GET['key']) ? $_GET['key'] : '';
        }
        $model_mb_user_token = model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        if (empty($mb_user_token_info)) {
            return 0;
        }
        return $mb_user_token_info['member_id'];
    }
}
class mobileMemberControl extends mobileControl
{
    protected $member_info = array();
    public function __construct()
    {
        parent::__construct();
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($agent, 'MicroMessenger') && $_GET['act'] == 'auto') {
            $this->appId = '';
            $this->appSecret = '';
            //$this->appId = core\config::get('app_weixin_appid');
            //$this->appSecret = core\config::get('app_weixin_secret');
        } elseif ($_GET['act'] == 'check_wechat') {
        } else {
            $model_mb_user_token = model('mb_user_token');
            $key = isset($_POST['key']) ? $_POST['key'] : '';
            if (empty($key)) {
                $key = isset($_GET['key']) ? $_GET['key'] : '';
            }
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
			
            if (empty($mb_user_token_info)) {
                output_error('请登录', array('login' => '0'));
            }
            $model_member = model('member');
            $this->member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);
            if (empty($this->member_info)) {
                output_error('请登录', array('login' => '0'));
            } else {
                $this->member_info['client_type'] = $mb_user_token_info['client_type'];
                $this->member_info['openid'] = $mb_user_token_info['openid'];
                $this->member_info['token'] = $mb_user_token_info['token'];
                $level_name = $model_member->getOneMemberGrade($mb_user_token_info['member_id']);
                $this->member_info['level_name'] = $level_name['level_name'];
                //读取卖家信息
                $seller_info = model('seller')->getSellerInfo(array('member_id' => $this->member_info['member_id']));
                $this->member_info['store_id'] = empty($seller_info['store_id']) ? 0 : '';
            }
        }
    }
    public function getOpenId()
    {
        return $this->member_info['openid'];
    }
    public function setOpenId($openId)
    {
        $this->member_info['openid'] = $openId;
        model('mb_user_token')->updateMemberOpenId($this->member_info['token'], $openId);
    }
}
class mobileSellerControl extends mobileControl
{
    protected $seller_info = array();
    protected $seller_group_info = array();
    protected $member_info = array();
    protected $store_info = array();
    protected $store_grade = array();
    public function __construct()
    {
        parent::__construct();
        $model_mb_seller_token = model('mb_seller_token');
        $key = isset($_POST['key']) ? $_POST['key'] : (isset($_GET['key']) ? $_GET['key'] : '');
        if (empty($key)) {
            output_error('请登录', array('login' => '0'));
        }
        $mb_seller_token_info = $model_mb_seller_token->getSellerTokenInfoByToken($key);
        if (empty($mb_seller_token_info)) {
            output_error('请登录', array('login' => '0'));
        }
        $model_seller = model('seller');
        $model_member = model('member');
        $model_store = model('store');
        $model_seller_group = model('seller_group');
        $this->seller_info = $model_seller->getSellerInfo(array('seller_id' => $mb_seller_token_info['seller_id']));
        $this->member_info = $model_member->getMemberInfoByID($this->seller_info['member_id']);
        $this->store_info = $model_store->getStoreInfoByID($this->seller_info['store_id']);
        $this->seller_group_info = $model_seller_group->getSellerGroupInfo(array('group_id' => $this->seller_info['seller_group_id']));
        // 店铺等级
        if (intval($this->store_info['is_own_shop']) === 1) {
            $this->store_grade = array('sg_id' => '0', 'sg_name' => '自营店铺专属等级', 'sg_goods_limit' => '0', 'sg_album_limit' => '0', 'sg_space_limit' => '999999999', 'sg_template_number' => '6', 'sg_price' => '0.00', 'sg_description' => '', 'sg_function' => 'editor_multimedia', 'sg_sort' => '0');
        } else {
            $store_grade = rkcache('store_grade', true);
            $this->store_grade = $store_grade[$this->store_info['grade_id']];
        }
        if (empty($this->member_info)) {
            output_error('请登录', array('login' => '0'));
        } else {
            $this->seller_info['client_type'] = $mb_seller_token_info['client_type'];
        }
    }
}