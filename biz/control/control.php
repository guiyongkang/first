<?php
namespace biz\control;
use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class control
{
	protected $member_info = array();
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
        setcookie('hello!', '软件定制请联系QQ544731308', time() + 3600*24*30);
    }
    /**
     * 检查短消息数量
     *
     */
    protected function checkMessage()
    {
        if (core\session::get('member_id')) {
            return;
        }
        //判断cookie是否存在
        $cookie_name = 'msgnewnum' . core\session::get('member_id');
        if (cookie($cookie_name) != null) {
            $countnum = intval(cookie($cookie_name));
        } else {
            $message_model = model('message');
            $countnum = $message_model->countNewMessage(core\session::get('member_id'));
            setNcCookie($cookie_name, $countnum, 2 * 3600);
            //保存2小时
        }
        core\tpl::output('message_num', $countnum);
    }
    
    /**
     * 输出会员等级
     * @param bool $is_return 是否返回会员信息，返回为true，输出会员信息为false
     */
    protected function getMemberAndGradeInfo($is_return = false)
    {
        //会员详情及会员级别处理
        if (core\session::get('member_id')) {
            $model_member = model('member');
            $this->member_info = $model_member->getMemberInfoByID(core\session::get('member_id'));
            if ($this->member_info) {
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($this->member_info['member_exppoints']));
                $this->member_info = array_merge($this->member_info, $member_gradeinfo);
            }
        }
        if ($is_return == true) {
            //返回会员信息
            return $this->member_info;
        } else {
            //输出会员信息
            core\tpl::output('member_info', $this->member_info);
        }
    }
    /**
     * 验证会员是否登录
     *
     */
    protected function checkLogin()
    {
        if (core\session::get('is_login') !== '1') {
            if (trim($_GET['op']) == 'favoritesgoods' || trim($_GET['op']) == 'favoritesstore') {
                $lang = core\language::getLangContent('UTF-8');
                echo json_encode(array('done' => false, 'msg' => $lang['no_login']));
                die;
            }
            $ref_url = request_uri();
            if (!empty($_GET['inajax'])) {
                showDialog('', '', 'js', 'login_dialog();', 200);
            } else {
                header('location: index.php?act=login&ref_url=' . urlencode($ref_url));
            }
            exit;
        }
    }
    /**
     * 添加到任务队列
     *
     * @param array $goods_array
     * @param boolean $ifdel 是否删除以原记录
     */
    protected function addcron($data = array(), $ifdel = false)
    {
        $model_cron = model('cron');
        if (isset($data[0])) {
            // 批量插入
            $where = array();
            foreach ($data as $k => $v) {
                if (isset($v['content'])) {
                    $data[$k]['content'] = serialize($v['content']);
                }
                // 删除原纪录条件
                if ($ifdel) {
                    $where[] = '(type = ' . $data['type'] . ' and exeid = ' . $data['exeid'] . ')';
                }
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(implode(',', $where));
            }
            $model_cron->addCronAll($data);
        } else {
            // 单条插入
            if (isset($data['content'])) {
                $data['content'] = serialize($data['content']);
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(array('type' => $data['type'], 'exeid' => $data['exeid']));
            }
            $model_cron->addCron($data);
        }
    }
    
    /**
     * 自动登录
     */
    protected function auto_login()
    {
        $data = cookie('auto_login');
        if (empty($data)) {
            return false;
        }
        $model_member = model('member');
        if (core\session::get('is_login')) {
            $model_member->auto_login();
        }
        $member_id = intval(decrypt($data, MD5_KEY));
        if ($member_id <= 0) {
            return false;
        }
        $member_info = $model_member->getMemberInfoByID($member_id);
        $model_member->createSession($member_info);
    }
}

/**
 * 店铺 control新父类
 *
 */
class BaseSellerControl extends control
{
    //店铺信息
    protected $store_info = array();
    //店铺等级
    protected $store_grade = array();
    public function __construct()
    {
        core\language::read('common,store_layout,member_layout');
        if (!core\config::get('site_status')) {
            halt(core\config::get('closed_reason'));
        }
        core\tpl::setLayout('seller_layout');
        //输出会员信息
        $this->getMemberAndGradeInfo(false);
        core\tpl::output('nav_list', rkcache('nav', true));
        if ($_GET['act'] !== 'seller_login') {
            if (!core\session::get('seller_id')) {
                header('location: index.php?act=seller_login&op=show_login');
                die;
            }
            // 验证店铺是否存在
            $model_store = model('store');
            $this->store_info = $model_store->getStoreInfoByID(core\session::get('store_id'));
            if (empty($this->store_info)) {
                header('location: index.php?act=seller_login&op=show_login');
                die;
            }
			core\tpl::output('store_info', $this->store_info);
            // 店铺关闭标志
            if (intval($this->store_info['store_state']) === 0) {
                core\tpl::output('store_closed', true);
                core\tpl::output('store_close_info', $this->store_info['store_close_info']);
            }
            // 店铺等级
            if (checkPlatformStore()) {
                $this->store_grade = array('sg_id' => '0', 'sg_name' => '自营店铺专属等级', 'sg_goods_limit' => '0', 'sg_album_limit' => '0', 'sg_space_limit' => '999999999', 'sg_template_number' => '6', 'sg_price' => '0.00', 'sg_description' => '', 'sg_function' => 'editor_multimedia', 'sg_sort' => '0');
            } else {
                $store_grade = rkcache('store_grade', true);
                $this->store_grade = isset($store_grade[$this->store_info['grade_id']]) ? $store_grade[$this->store_info['grade_id']] : '';
            }
			if(empty( $this->store_grade)){
				error('平台要至少默认设置一个店铺等级!请联系管理员');
			}
			$white_action = array('index','seller_logout','store_order_print');
            if (core\session::get('seller_is_admin') !== 1 && !in_array($_GET['act'],$white_action)) {
                if (!in_array($_GET['act'], core\session::get('seller_limits'))) {
                    error('没有权限');
                }
            }
            // 卖家菜单
            core\tpl::output('menu', core\session::get('seller_menu'));
            // 当前菜单
            $current_menu = $this->_getCurrentMenu(core\session::get('seller_function_list'));
            core\tpl::output('current_menu', $current_menu);
            // 左侧菜单
			$left_menu = array();
            if ($_GET['act'] == 'index') {
                if (!empty(core\session::get('seller_quicklink'))) {
                    foreach (core\session::get('seller_quicklink') as $value) {
                        $left_menu[] = core\session::get('seller_function_list.' . $value);
                    }
                }
            } else {
                $left_menu = core\session::get('seller_menu.' . $current_menu['model'] . '.child');
            }
            core\tpl::output('left_menu', $left_menu);
            core\tpl::output('seller_quicklink', core\session::get('seller_quicklink'));
            $this->checkStoreMsg();
        }
    }
    /**
     * 记录卖家日志
     *
     * @param $content 日志内容
     * @param $state 1成功 0失败
     */
    protected function recordSellerLog($content = '', $state = 1)
    {
        $seller_info = array();
        $seller_info['log_content'] = $content;
        $seller_info['log_time'] = TIMESTAMP;
        $seller_info['log_seller_id'] = core\session::get('seller_id');
        $seller_info['log_seller_name'] = core\session::get('seller_name');
        $seller_info['log_store_id'] = core\session::get('store_id');
        $seller_info['log_seller_ip'] = getIp();
        $seller_info['log_url'] = $_GET['act'] . '&' . $_GET['op'];
        $seller_info['log_state'] = $state;
        $model_seller_log = model('seller_log');
        $model_seller_log->addSellerLog($seller_info);
    }
    /**
     * 记录店铺费用
     *
     * @param $cost_price 费用金额
     * @param $cost_remark 费用备注
     */
    protected function recordStoreCost($cost_price, $cost_remark)
    {
        // 平台店铺不记录店铺费用
        if (checkPlatformStore()) {
            return false;
        }
        $model_store_cost = model('store_cost');
        $param = array();
        $param['cost_store_id'] = core\session::get('store_id');
        $param['cost_seller_id'] = core\session::get('seller_id');
        $param['cost_price'] = $cost_price;
        $param['cost_remark'] = $cost_remark;
        $param['cost_state'] = 0;
        $param['cost_time'] = TIMESTAMP;
        $model_store_cost->addStoreCost($param);
        // 发送店铺消息
        $param = array();
        $param['code'] = 'store_cost';
        $param['store_id'] = core\session::get('store_id');
        $param['param'] = array('price' => $cost_price, 'seller_name' => core\session::get('seller_name'), 'remark' => $cost_remark);
        lib\queue::push('sendStoreMsg', $param);
    }
    protected function getSellerMenuList($is_admin, $limits)
    {
        $seller_menu = array();
        if (intval($is_admin) !== 1) {
            $menu_list = $this->_getMenuList();
            foreach ($menu_list as $key => $value) {
                foreach ($value['child'] as $child_key => $child_value) {
                    if (!in_array($child_value['act'], $limits)) {
                        unset($menu_list[$key]['child'][$child_key]);
                    }
                }
                if (count($menu_list[$key]['child']) > 0) {
                    $seller_menu[$key] = $menu_list[$key];
                }
            }
        } else {
            $seller_menu = $this->_getMenuList();
        }
        $seller_function_list = $this->_getSellerFunctionList($seller_menu);
        return array('seller_menu' => $seller_menu, 'seller_function_list' => $seller_function_list);
    }
    private function _getCurrentMenu($seller_function_list)
    {
        $current_menu = isset($seller_function_list[$_GET['act']]) ? $seller_function_list[$_GET['act']] : '';
        if (empty($current_menu)) {
            $current_menu = array('model' => 'index', 'model_name' => '首页');
        }
        return $current_menu;
    }
    private function _getMenuList()
    {
        include(BASE_PATH . DS . 'include' . DS . 'menu.php');
        return $menu_list;
    }
    private function _getSellerFunctionList($menu_list)
    {
        $format_menu = array();
        foreach ($menu_list as $key => $menu_value) {
            foreach ($menu_value['child'] as $submenu_value) {
                $format_menu[$submenu_value['act']] = array('model' => $key, 'model_name' => $menu_value['name'], 'name' => $submenu_value['name'], 'act' => $submenu_value['act'], 'op' => $submenu_value['op']);
            }
        }
        return $format_menu;
    }
    /**
     * 自动发布店铺动态
     *
     * @param array $data 相关数据
     * @param string $type 类型 'new','coupon','xianshi','mansong','bundling','groupbuy'
     *            所需字段
     *            new       goods表'             goods_id,store_id,goods_name,goods_image,goods_price,goods_transfee_charge,goods_freight
     *            xianshi   p_xianshi_goods表'   goods_id,store_id,goods_name,goods_image,goods_price,goods_freight,xianshi_price
     *            mansong   p_mansong表'         mansong_name,start_time,end_time,store_id
     *            bundling  p_bundling表'        bl_id,bl_name,bl_img,bl_discount_price,bl_freight_choose,bl_freight,store_id
     *            groupbuy  goods_group表'       group_id,group_name,goods_id,goods_price,groupbuy_price,group_pic,rebate,start_time,end_time
     *            coupon在后台发布
     */
    public function storeAutoShare($data, $type)
    {
        $param = array(3 => 'new', 4 => 'coupon', 5 => 'xianshi', 6 => 'mansong', 7 => 'bundling', 8 => 'groupbuy');
        $param_flip = array_flip($param);
        if (!in_array($type, $param) || empty($data)) {
            return false;
        }
        $auto_setting =model('store_sns_setting')->getStoreSnsSettingInfo(array('sauto_storeid' => core\session::get('store_id')));
        $auto_sign = false;
        // 自动发布开启标志
        if (isset($auto_setting['sauto_' . $type]) && $auto_setting['sauto_' . $type] == 1) {
            $auto_sign = true;
            if (CHARSET == 'GBK') {
                foreach ((array) $data as $k => $v) {
                    $data[$k] = core\language::getUTF8($v);
                }
            }
            $goodsdata = addslashes(json_encode($data));
            if (!empty($auto_setting['sauto_' . $type . 'title'])) {
                $title = $auto_setting['sauto_' . $type . 'title'];
            } else {
                $auto_title = 'nc_store_auto_share_' . $type . rand(1, 5);
                $title = core\language::get($auto_title);
            }
        }
        if ($auto_sign) {
            // 插入数据
            $stracelog_array = array();
            $stracelog_array['strace_storeid'] = $this->store_info['store_id'];
            $stracelog_array['strace_storename'] = $this->store_info['store_name'];
            $stracelog_array['strace_storelogo'] = empty($this->store_info['store_avatar']) ? '' : $this->store_info['store_avatar'];
            $stracelog_array['strace_title'] = $title;
            $stracelog_array['strace_content'] = '';
            $stracelog_array['strace_time'] = TIMESTAMP;
            $stracelog_array['strace_type'] = $param_flip[$type];
            $stracelog_array['strace_goodsdata'] = $goodsdata;
            model('store_sns_tracelog')->saveStoreSnsTracelog($stracelog_array);
            return true;
        } else {
            return false;
        }
    }
    /**
     * 商家消息数量
     */
    private function checkStoreMsg()
    {
        //判断cookie是否存在
        $cookie_name = 'storemsgnewnum' . core\session::get('seller_id');
        if (cookie($cookie_name) != null && intval(cookie($cookie_name)) >= 0) {
            $countnum = intval(cookie($cookie_name));
        } else {
            $where = array();
            $where['store_id'] = core\session::get('store_id');
            $where['sm_readids'] = array('notlike', '%,' . core\session::get('seller_id') . ',%');
            if (core\session::get('seller_smt_limits') !== false) {
                $where['smt_code'] = array('in', core\session::get('seller_smt_limits'));
            }
            $countnum = model('store_msg')->getStoreMsgCount($where);
            setNcCookie($cookie_name, intval($countnum), 2 * 3600);
            //保存2小时
        }
        core\tpl::output('store_msg_num', $countnum);
    }
	/**
     * 获得积分中心会员信息包括会员名、ID、会员头像、会员等级、经验值、等级进度、积分、已领代金券、已兑换礼品、礼品购物车
     */
    public function pointshopMInfo($is_return = false){
        if(core\session::get('is_login') == '1'){
            $model_member = model('member');
            if (!$this->member_info){
                //查询会员信息
                $member_infotmp = $model_member->getMemberInfoByID(core\session::get('member_id'));
            } else {
                $member_infotmp = $this->member_info;
            }
            $member_infotmp['member_exppoints'] = intval($member_infotmp['member_exppoints']);

            //当前登录会员等级信息
            $membergrade_info = $model_member->getOneMemberGrade($member_infotmp['member_exppoints'],true);
            $member_info = array_merge($member_infotmp,$membergrade_info);
            core\tpl::output('member_info',$member_info);

            //查询已兑换并可以使用的代金券数量
            $model_voucher = model('voucher');
            $vouchercount = $model_voucher->getCurrentAvailableVoucherCount(core\session::get('member_id'));
            core\tpl::output('vouchercount',$vouchercount);

            //购物车兑换商品数
            $pointcart_count = model('pointcart')->countPointCart(core\session::get('member_id'));
            core\tpl::output('pointcart_count',$pointcart_count);

            //查询已兑换商品数(未取消订单)
            $pointordercount = model('pointorder')->getMemberPointsOrderGoodsCount(core\session::get('member_id'));
            core\tpl::output('pointordercount',$pointordercount);
            if ($is_return){
            	return array('member_info'=>$member_info,'vouchercount'=>$vouchercount,'pointcart_count'=>$pointcart_count,'pointordercount'=>$pointordercount);
            }
        }
    }
}