<?php
namespace common\model;

use core;
use lib;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class member extends core\model
{
    public function __construct()
    {
        parent::__construct('member');
    }
    /**
     * 会员详细信息（查库）
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getMemberInfo($condition, $field = '*', $master = false)
    {
        return $this->table('member')->field($field)->where($condition)->master($master)->find();
    }
    /**
     * 取得会员详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @param int $member_id
     * @param string $field 需要取得的缓存键值, 例如：'*','member_name,member_sex'
     * @return array
     */
    public function getMemberInfoByID($member_id, $fields = '*')
    {
        $member_info = rcache($member_id, 'member', $fields);
        if (empty($member_info)) {
            $member_info = $this->getMemberInfo(array('member_id' => $member_id), '*', true);
            wcache($member_id, $member_info, 'member');
        }
        return $member_info;
    }
    /**
     * 会员列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getMemberList($condition = array(), $field = '*', $page = 0, $order = 'member_id desc', $limit = '')
    {
        return $this->table('member')->where($condition)->page($page)->order($order)->limit($limit)->select();
    }
    /**
     * 会员数量
     * @param array $condition
     * @return int
     */
    public function getMemberCount($condition)
    {
        return $this->table('member')->where($condition)->count();
    }
    /**
     * 编辑会员
     * @param array $condition
     * @param array $data
     */
    public function editMember($condition, $data)
    {
        $update = $this->table('member')->where($condition)->update($data);
        if ($update && !empty($condition['member_id'])) {
            dcache($condition['member_id'], 'member');
        }
        return $update;
    }
    /**
     * 登录时创建会话SESSION
     *
     * @param array $member_info 会员信息
     */
    public function createSession($member_info = array(), $reg = false)
    {
        if (empty($member_info) || !is_array($member_info)) {
            return;
        }
        core\session::set('is_login', '1');
		core\session::set('member_id', $member_info['member_id']);
		core\session::set('member_name', $member_info['member_name']);
		core\session::set('member_email', $member_info['member_email']);
		core\session::set('is_buy', isset($member_info['is_buy']) ? $member_info['is_buy'] : 1);
		core\session::set('avatar', $member_info['member_avatar']);
        // 头衔COOKIE
        $this->set_avatar_cookie();
        $seller_info = model('seller')->getSellerInfo(array('member_id' => core\session::get('member_id')));
        if($seller_info){
			core\session::set('store_id', $seller_info['store_id']);
		}
        if (trim($member_info['member_qqopenid'])) {
			core\session::set('openid', $member_info['member_qqopenid']);
        }
        if (trim($member_info['member_sinaopenid'])) {
			core\session::set('slast_key.uid', $member_info['member_sinaopenid']);
        }
        if (trim($member_info['member_wxopenid'])) {
			core\session::set('wxopenid', $member_info['member_wxopenid']);
        }
        if (trim($member_info['weixin_unionid'])) {
			core\session::set('wxunionid', $member_info['weixin_unionid']);
        }
        if (!$reg) {
            //添加会员元
            $this->addPoint($member_info);
            //添加会员经验值
            $this->addExppoint($member_info);
        }
        if (!empty($member_info['member_login_time'])) {
            $update_info = array('member_login_num' => $member_info['member_login_num'] + 1, 'member_login_time' => TIMESTAMP, 'member_old_login_time' => $member_info['member_login_time'], 'member_login_ip' => getIp(), 'member_old_login_ip' => $member_info['member_login_ip']);
            $this->editMember(array('member_id' => $member_info['member_id']), $update_info);
        }
        setNcCookie('cart_goods_num', '', -3600);
        // cookie中的cart存入数据库
        model('cart')->mergecart($member_info, core\session::get('store_id'));
        // cookie中的浏览记录存入数据库
        model('goods_browse')->mergebrowse(core\session::get('member_id'), core\session::get('store_id'));
        // 自动登录
        if ($member_info['auto_login'] == 1) {
            $this->auto_login();
        }
    }
    /**
     * 7天内自动登录
     */
    public function auto_login()
    {
        // 自动登录标记 保存7天
        setNcCookie('auto_login', encrypt(core\session::get('member_id'), MD5_KEY), 7 * 24 * 60 * 60);
    }
    public function set_avatar_cookie()
    {
        setNcCookie('member_avatar', core\session::get('avatar'), 365 * 24 * 60 * 60);
    }
    /**
     * 获取会员信息
     *
     * @param	array $param 会员条件
     * @param	string $field 显示字段
     * @return	array 数组格式的返回结果
     */
    public function infoMember($param, $field = '*')
    {
        if (empty($param)) {
            return false;
        }
        //得到条件语句
        $condition_str = $this->getCondition($param);
        $param = array();
        $param['table'] = 'member';
        $param['where'] = $condition_str;
        $param['field'] = $field;
        $param['limit'] = 1;
        $member_list = db\mysqli::select($param);
        $member_info = $member_list[0];
        if (intval($member_info['store_id']) > 0) {
            $param = array();
            $param['table'] = 'store';
            $param['field'] = 'store_id';
            $param['value'] = $member_info['store_id'];
            $field = 'store_id,store_name,grade_id';
            $store_info = db\mysqli::getRow($param, $field);
            if (!empty($store_info) && is_array($store_info)) {
                $member_info['store_name'] = $store_info['store_name'];
                $member_info['grade_id'] = $store_info['grade_id'];
            }
        }
        return $member_info;
    }
    /**
     * 注册
     */
    public function register($register_info)
    {
		$register_info['inviter_id'] = isset($register_info['inviter_id']) ? $register_info['inviter_id'] : 0;	
		if($register_info['inviter_id'] == 0){
			$dis_setting = model('distributor_setting')->field('member_inviter')->find();
			if($dis_setting['member_inviter'] == 1){
				return array('error' => '必须通过邀请人才能成为会员');
			}
		}
        // 注册验证
        $obj_validate = new lib\validate();
        $obj_validate->validateparam = array(array("input" => $register_info["username"], "require" => "true", "message" => '用户名不能为空'), array("input" => $register_info["password"], "require" => "true", "message" => '密码不能为空'), array("input" => $register_info["password_confirm"], "require" => "true", "validator" => "Compare", "operator" => "==", "to" => $register_info["password"], "message" => '密码与确认密码不相同'));
        $error = $obj_validate->validate();
        if ($error != '') {
            return array('error' => $error);
        }
        // 验证用户名是否重复
        $check_member_name = $this->getMemberInfo(array('member_name' => $register_info['username']));
        if (is_array($check_member_name) and count($check_member_name) > 0) {
            return array('error' => '用户名已存在');
        }
        // 会员添加
        $member_info = array();
        $member_info['member_name'] = $register_info['username'];
        $member_info['member_passwd'] = $register_info['password'];
        $member_info['member_email'] = $register_info['email'];
        //添加邀请人(推荐人)会员元
        $member_info['inviter_id'] = $register_info['inviter_id'];
        $insert_id = $this->addMember($member_info);
        if ($insert_id) {
            //添加会员元
            if (core\config::get('points_isuse')) {
                model('points')->savePointsLog('regist', array('pl_memberid' => $insert_id, 'pl_membername' => $register_info['username']), false);
                //添加邀请人(推荐人)会员元 by
                $inviter_name = model('member')->table('member')->getfby_member_id($member_info['inviter_id'], 'member_name');
                model('points')->savePointsLog('inviter', array('pl_memberid' => $register_info['inviter_id'], 'pl_membername' => $inviter_name, 'invited' => $member_info['member_name']));
            }
            // 添加默认相册
            $insert['ac_name'] = '买家秀';
            $insert['member_id'] = $insert_id;
            $insert['ac_des'] = '买家秀默认相册';
            $insert['ac_sort'] = 1;
            $insert['is_default'] = 1;
            $insert['upload_time'] = TIMESTAMP;
            $this->table('sns_albumclass')->insert($insert);
            $member_info['member_id'] = $insert_id;
            $member_info['is_buy'] = 1;
            return $member_info;
        } else {
            return array('error' => '注册失败');
        }
    }
    /**
     * 注册商城会员
     *
     * @param	array $param 会员信息
     * @return	array 数组格式的返回结果
     */
    public function addMember($param)
    {
        if (empty($param)) {
            return false;
        }
        try {
            $this->beginTransaction();
            $member_info = array();
            $member_info['member_id'] = isset($param['member_id']) ? $param['member_id'] : 0;
            $member_info['member_name'] = isset($param['member_name']) ? $param['member_name'] : '';
            $member_info['member_passwd'] = md5(trim($param['member_passwd']));
            $member_info['member_email'] = isset($param['member_email']) ? $param['member_email'] : '';
            $member_info['member_time'] = TIMESTAMP;
            $member_info['member_login_time'] = TIMESTAMP;
            $member_info['member_old_login_time'] = TIMESTAMP;
            $member_info['member_login_ip'] = getIp();
            $member_info['member_old_login_ip'] = $member_info['member_login_ip'];
            $member_info['member_truename'] = isset($param['member_truename']) ? $param['member_truename'] : '';
            $member_info['member_qq'] = isset($param['member_qq']) ? $param['member_qq'] : '';
            $member_info['member_sex'] = isset($param['member_sex']) ? $param['member_sex'] : '';
            $member_info['member_avatar'] = isset($param['member_avatar']) ? $param['member_avatar'] : '';
            $member_info['member_qqopenid'] = isset($param['member_qqopenid']) ? $param['member_qqopenid'] : '';
            $member_info['member_qqinfo'] = isset($param['member_qqinfo']) ? $param['member_qqinfo'] : '';
            $member_info['member_sinaopenid'] = isset($param['member_sinaopenid']) ? $param['member_sinaopenid'] : '';
            $member_info['member_sinainfo'] = isset($param['member_sinainfo']) ? $param['member_sinainfo'] : '';
            //添加邀请人(推荐人)
            $member_info['inviter_id'] = isset($param['inviter_id']) ? $param['inviter_id'] : 0;
		
            // 手机注册登录绑定
            if (!empty($param['member_mobile_bind'])) {
                $member_info['member_mobile'] = $param['member_mobile'];
                $member_info['member_mobile_bind'] = $param['member_mobile_bind'];
            }
            if (!empty($param['weixin_unionid'])) {
                $member_info['weixin_unionid'] = $param['weixin_unionid'];
                $member_info['weixin_info'] = $param['weixin_info'];
                $member_info['member_wxopenid'] = $param['member_wxopenid'];
            }
            $insert_id = $this->table('member')->insert($member_info);
            if (!$insert_id) {
                throw new \Exception();
            }
            $insert = $this->addMemberCommon(array('member_id' => $insert_id));
            if (!$insert) {
                throw new \Exception();
            }
            // 添加默认相册
            $insert = array();
            $insert['ac_name'] = '买家秀';
            $insert['member_id'] = $insert_id;
            $insert['ac_des'] = '买家秀默认相册';
            $insert['ac_sort'] = 1;
            $insert['is_default'] = 1;
            $insert['upload_time'] = TIMESTAMP;
            $rs = $this->table('sns_albumclass')->insert($insert);
            //添加会员元
            if (core\config::get('points_isuse')) {
                model('points')->savePointsLog('regist', array('pl_memberid' => $insert_id, 'pl_membername' => $param['member_name']), false);
            }
            $this->commit();
            return $insert_id;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }
    /**
     * 会员登录检查
     *
     */
    public function checkloginMember()
    {
        if (core\session::get('is_login') == '1') {
            header("Location: index.php");
            exit;
        }
    }
    /**
     * 检查会员是否允许举报商品
     *
     */
    public function isMemberAllowInform($member_id)
    {
        $condition = array();
        $condition['member_id'] = $member_id;
        $member_info = $this->getMemberInfo($condition, 'inform_allow');
        if (intval($member_info['inform_allow']) === 1) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 取单条信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getMemberCommonInfo($condition = array(), $fields = '*')
    {
        return $this->table('member_common')->where($condition)->field($fields)->find();
    }
    /**
     * 插入扩展表信息
     * @param unknown $data
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function addMemberCommon($data)
    {
        return $this->table('member_common')->insert($data);
    }
    /**
     * 编辑会员扩展表
     * @param unknown $data
     * @param unknown $condition
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function editMemberCommon($data, $condition)
    {
        return $this->table('member_common')->where($condition)->update($data);
    }
    /**
     * 添加会员元
     * @param unknown $member_info
     */
    public function addPoint($member_info)
    {
        if (!core\config::get('points_isuse') || empty($member_info)) {
            return;
        }
        //一天内只有第一次登录赠送元
        if (trim(date('Y-m-d', $member_info['member_login_time'])) == trim(date('Y-m-d'))) {
            return;
        }
        //加入队列
        $queue_content = array();
        $queue_content['member_id'] = $member_info['member_id'];
        $queue_content['member_name'] = $member_info['member_name'];
        lib\queue::push('addPoint', $queue_content);
    }
    /**
     * 添加会员经验值
     * @param unknown $member_info
     */
    public function addExppoint($member_info)
    {
        if (empty($member_info)) {
            return;
        }
        //一天内只有第一次登录赠送经验值
        if (trim(date('Y-m-d', $member_info['member_login_time'])) == trim(date('Y-m-d'))) {
            return;
        }
        //加入队列
        $queue_content = array();
        $queue_content['member_id'] = $member_info['member_id'];
        $queue_content['member_name'] = $member_info['member_name'];
        lib\queue::push('addExppoint', $queue_content);
    }
    /**
     * 取得会员安全级别
     * @param unknown $member_info
     */
    public function getMemberSecurityLevel($member_info = array())
    {
        $tmp_level = 0;
        if ($member_info['member_email_bind'] == '1') {
            $tmp_level += 1;
        }
        if ($member_info['member_mobile_bind'] == '1') {
            $tmp_level += 1;
        }
        if ($member_info['member_paypwd'] != '') {
            $tmp_level += 1;
        }
        return $tmp_level;
    }
    /**
     * 获得会员等级
     * @param bool $show_progress 是否计算其当前等级进度
     * @param int $exppoints  会员经验值
     * @param array $cur_level 会员当前等级
     */
    public function getMemberGradeArr($show_progress = false, $exppoints = 0, $cur_level = '')
    {
        $member_grade = core\config::get('member_grade') ? unserialize(core\config::get('member_grade')) : array();
        //处理会员等级进度
        if ($member_grade && $show_progress) {
            $is_max = false;
            if ($cur_level === '') {
                $cur_gradearr = $this->getOneMemberGrade($exppoints, false, $member_grade);
                $cur_level = $cur_gradearr['level'];
            }
            foreach ($member_grade as $k => $v) {
                if ($cur_level == $v['level']) {
                    $v['is_cur'] = true;
                }
                $member_grade[$k] = $v;
            }
        }
        return $member_grade;
    }
    /**
     * 将条件数组组合为SQL语句的条件部分
     *
     * @param	array $conditon_array
     * @return	string
     */
    private function getCondition($conditon_array)
    {
        $condition_sql = '';
        if (!empty($conditon_array['member_id'])) {
            $condition_sql .= " and member_id= '" . intval($conditon_array['member_id']) . "'";
        }
        if (!empty($conditon_array['member_name'])) {
            $condition_sql .= " and member_name='" . $conditon_array['member_name'] . "'";
        }
        if (!empty($conditon_array['member_passwd'])) {
            $condition_sql .= " and member_passwd='" . $conditon_array['member_passwd'] . "'";
        }
        //是否允许举报
        if (!empty($conditon_array['inform_allow'])) {
            $condition_sql .= " and inform_allow='{$conditon_array['inform_allow']}'";
        }
        //是否允许购买
        if (!empty($conditon_array['is_buy'])) {
            $condition_sql .= " and is_buy='{$conditon_array['is_buy']}'";
        }
        //是否允许发言
        if (!empty($conditon_array['is_allowtalk'])) {
            $condition_sql .= " and is_allowtalk='{$conditon_array['is_allowtalk']}'";
        }
        //是否允许登录
        if (!empty($conditon_array['member_state'])) {
            $condition_sql .= " and member_state='{$conditon_array['member_state']}'";
        }
        if (!empty($conditon_array['friend_list'])) {
            $condition_sql .= " and member_name IN (" . $conditon_array['friend_list'] . ")";
        }
        if (!empty($conditon_array['member_email'])) {
            $condition_sql .= " and member_email='" . $conditon_array['member_email'] . "'";
        }
        if (!empty($conditon_array['no_member_id'])) {
            $condition_sql .= " and member_id != '" . $conditon_array['no_member_id'] . "'";
        }
        if (!empty($conditon_array['like_member_name'])) {
            $condition_sql .= " and member_name like '%" . $conditon_array['like_member_name'] . "%'";
        }
        if (!empty($conditon_array['like_member_email'])) {
            $condition_sql .= " and member_email like '%" . $conditon_array['like_member_email'] . "%'";
        }
        if (!empty($conditon_array['like_member_truename'])) {
            $condition_sql .= " and member_truename like '%" . $conditon_array['like_member_truename'] . "%'";
        }
        if (!empty($conditon_array['in_member_id'])) {
            $condition_sql .= " and member_id IN (" . $conditon_array['in_member_id'] . ")";
        }
        if (!empty($conditon_array['in_member_name'])) {
            $condition_sql .= " and member_name IN (" . $conditon_array['in_member_name'] . ")";
        }
        if (!empty($conditon_array['member_qqopenid'])) {
            $condition_sql .= " and member_qqopenid = '{$conditon_array['member_qqopenid']}'";
        }
        if (!empty($conditon_array['member_sinaopenid'])) {
            $condition_sql .= " and member_sinaopenid = '{$conditon_array['member_sinaopenid']}'";
        }
        if (!empty($conditon_array['weixin_unionid'])) {
            $condition_sql .= " and member_wxunionid = '{$conditon_array['member_wxunionid']}'";
        }
        if (!empty($conditon_array['member_wxopenid'])) {
            $condition_sql .= " and member_wxopenid = '{$conditon_array['member_wxopenid']}'";
        }
        return $condition_sql;
    }
    /**
     * 删除会员
     *
     * @param int $id 记录ID
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function del($id)
    {
        if (intval($id) > 0) {
            $where = ' member_id = \'' . intval($id) . '\'';
            $result = db\mysqli::delete('member', $where);
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 获得某一会员等级
     * @param int $exppoints
     * @param bool $show_progress 是否计算其当前等级进度
     * @param array $member_grade 会员等级
     */
    public function getOneMemberGrade($exppoints, $show_progress = false, $member_grade = array())
    {
        if (!$member_grade) {
            $member_grade = core\config::get('member_grade') ? unserialize(core\config::get('member_grade')) : array();
        }
        if (empty($member_grade)) {
            //如果会员等级设置为空
            $grade_arr['level'] = -1;
            $grade_arr['level_name'] = '暂无等级';
            return $grade_arr;
        }
        $exppoints = intval($exppoints);
        $grade_arr = array();
        if ($member_grade) {
            foreach ($member_grade as $k => $v) {
                if ($exppoints >= $v['exppoints']) {
                    $grade_arr = $v;
                }
            }
        }
        //计算提升进度
        if ($show_progress == true) {
            if (intval($grade_arr['level']) >= count($member_grade) - 1) {
                //如果已达到顶级会员
                $grade_arr['downgrade'] = $grade_arr['level'] - 1;
                //下一级会员等级
                $grade_arr['downgrade_name'] = $member_grade[$grade_arr['downgrade']]['level_name'];
                $grade_arr['downgrade_exppoints'] = $member_grade[$grade_arr['downgrade']]['exppoints'];
                $grade_arr['upgrade'] = $grade_arr['level'];
                //上一级会员等级
                $grade_arr['upgrade_name'] = $member_grade[$grade_arr['upgrade']]['level_name'];
                $grade_arr['upgrade_exppoints'] = $member_grade[$grade_arr['upgrade']]['exppoints'];
                $grade_arr['less_exppoints'] = 0;
                $grade_arr['exppoints_rate'] = 100;
            } else {
                $grade_arr['downgrade'] = $grade_arr['level'];
                //下一级会员等级
                $grade_arr['downgrade_name'] = $member_grade[$grade_arr['downgrade']]['level_name'];
                $grade_arr['downgrade_exppoints'] = $member_grade[$grade_arr['downgrade']]['exppoints'];
                $grade_arr['upgrade'] = $member_grade[$grade_arr['level'] + 1]['level'];
                //上一级会员等级
                $grade_arr['upgrade_name'] = $member_grade[$grade_arr['upgrade']]['level_name'];
                $grade_arr['upgrade_exppoints'] = $member_grade[$grade_arr['upgrade']]['exppoints'];
                $grade_arr['less_exppoints'] = $grade_arr['upgrade_exppoints'] - $exppoints;
                $grade_arr['exppoints_rate'] = round(($exppoints - $member_grade[$grade_arr['level']]['exppoints']) / ($grade_arr['upgrade_exppoints'] - $member_grade[$grade_arr['level']]['exppoints']) * 100, 2);
            }
        }
        return $grade_arr;
    }
}