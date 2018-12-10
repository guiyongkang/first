<?php
/**
 * 会员管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class member extends SystemControl
{
    const EXPORT_SIZE = 1000;
    public function __construct()
    {
        parent::__construct();
        core\language::read('member');
    }
    /**
     * 会员管理
     */
    public function memberOp()
    {
        $lang = core\language::getLangContent();
        $model_member = model('member');
        /**
         * 删除
         */
        if (chksubmit()) {
            /**
             * 判断是否是管理员，如果是，则不能删除
             */
            /**
             * 删除
             */
            if (!empty($_POST['del_id'])) {
                if (is_array($_POST['del_id'])) {
                    foreach ($_POST['del_id'] as $k => $v) {
                        $v = intval($v);
                        $rs = true;
                        //$model_member->del($v);
                        if ($rs) {
                            //删除该会员商品,店铺
                            //获得该会员店铺信息
                            $member = $model_member->getMemberInfo(array('member_id' => $v));
                            //删除用户
                            $model_member->del($v);
                        }
                    }
                }
                success($lang['nc_common_del_succ']);
            } else {
                error($lang['nc_common_del_fail']);
            }
        }
        //会员级别
        $member_grade = $model_member->getMemberGradeArr();
		$condition = array();
        if (!empty($_GET['search_field_value'])) {
            switch ($_GET['search_field_name']) {
                case 'member_name':
                    $condition['member_name'] = array('like', '%' . trim($_GET['search_field_value']) . '%');
                    break;
                case 'member_email':
                    $condition['member_email'] = array('like', '%' . trim($_GET['search_field_value']) . '%');
                    break;
                case 'member_mobile':
                    $condition['member_mobile'] = array('like', '%' . trim($_GET['search_field_value']) . '%');
                    break;
                case 'member_truename':
                    $condition['member_truename'] = array('like', '%' . trim($_GET['search_field_value']) . '%');
                    break;
            }
        }
		if (!empty($_GET['search_state'])) {
			switch ($_GET['search_state']) {
				case 'no_informallow':
					$condition['inform_allow'] = '2';
					break;
				case 'no_isbuy':
					$condition['is_buy'] = '0';
					break;
				case 'no_isallowtalk':
					$condition['is_allowtalk'] = '0';
					break;
				case 'no_memberstate':
					$condition['member_state'] = '0';
					break;
			}
		}
        //会员等级
        $search_grade = isset($_GET['search_grade']) ? intval($_GET['search_grade']) : 0;
        if ($search_grade >= 0 && $member_grade) {
            $condition['member_exppoints'] = array(array('egt', $member_grade[$search_grade]['exppoints']), array('lt', $member_grade[$search_grade + 1]['exppoints']), 'and');
        }
        //排序
        $order = isset($_GET['search_sort']) ? trim($_GET['search_sort']) : '';
        if (empty($order)) {
            $order = 'member_id desc';
        }
        $member_list = $model_member->getMemberList($condition, '*', 10, $order);
        //整理会员信息
        if (is_array($member_list)) {
            foreach ($member_list as $k => $v) {
                $member_list[$k]['member_time'] = $v['member_time'] ? date('Y-m-d H:i:s', $v['member_time']) : '';
                $member_list[$k]['member_login_time'] = $v['member_login_time'] ? date('Y-m-d H:i:s', $v['member_login_time']) : '';
                $member_list[$k]['member_grade'] = ($t = $model_member->getOneMemberGrade($v['member_exppoints'], false, $member_grade)) ? $t['level_name'] : '';
            }
        }
        core\tpl::output('member_grade', $member_grade);
        core\tpl::output('search_sort', isset($_GET['search_sort']) ? trim($_GET['search_sort']) : '');
        core\tpl::output('search_field_name', isset($_GET['search_field_name']) ? trim($_GET['search_field_name']) : '');
        core\tpl::output('search_field_value', isset($_GET['search_field_value']) ? trim($_GET['search_field_value']) : '');
        core\tpl::output('member_list', $member_list);
        core\tpl::output('page', $model_member->showpage());
        core\tpl::showpage('member.index');
    }
    /**
     * 会员修改
     */
    public function member_editOp()
    {
        $lang = core\language::getLangContent();
        $model_member = model('member');
        /**
         * 保存
         */
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["member_email"], "require" => "true", 'validator' => 'Email', "message" => $lang['member_edit_valid_email']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $update_array = array();
                $update_array['member_id'] = intval($_POST['member_id']);
                if (!empty($_POST['member_passwd'])) {
                    $update_array['member_passwd'] = md5($_POST['member_passwd']);
                }
                $update_array['member_email'] = $_POST['member_email'];
                $update_array['member_truename'] = $_POST['member_truename'];
                $update_array['member_sex'] = $_POST['member_sex'];
                $update_array['member_qq'] = $_POST['member_qq'];
                $update_array['member_ww'] = $_POST['member_ww'];
                $update_array['inform_allow'] = $_POST['inform_allow'];
                $update_array['is_buy'] = $_POST['isbuy'];
                $update_array['is_allowtalk'] = $_POST['allowtalk'];
                $update_array['member_state'] = $_POST['memberstate'];
                // 新增
                $update_array['member_cityid'] = $_POST['city_id'];
                $update_array['member_provinceid'] = $_POST['province_id'];
                $update_array['member_areainfo'] = $_POST['area_info'];
                $update_array['member_mobile'] = $_POST['member_mobile'];
                $update_array['member_email_bind'] = intval($_POST['memberemailbind']);
                $update_array['member_mobile_bind'] = intval($_POST['membermobilebind']);
                if (!empty($_POST['member_avatar'])) {
                    $update_array['member_avatar'] = $_POST['member_avatar'];
                }
                $result = $model_member->editMember(array('member_id' => intval($_POST['member_id'])), $update_array);
                if ($result) {
                    $url = 'index.php?act=member&op=member_edit&member_id=' . intval($_POST['member_id']);
                    $this->log(lang('nc_edit,member_index_name') . '[ID:' . $_POST['member_id'] . ']', 1);
                    success($lang['member_edit_succ'], $url);
                } else {
                    error($lang['member_edit_fail']);
                }
            }
        }
        $condition['member_id'] = intval($_GET['member_id']);
        $member_array = $model_member->getMemberInfo($condition);
        core\tpl::output('member_array', $member_array);
        core\tpl::showpage('member.edit');
    }
    /**
     * 新增会员
     */
    public function member_addOp()
    {
        $lang = core\language::getLangContent();
        $model_member = model('member');
        /**
         * 保存
         */
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["member_name"], "require" => "true", "message" => $lang['member_add_name_null']), array("input" => $_POST["member_passwd"], "require" => "true", "message" => '密码不能为空'), array("input" => $_POST["member_email"], "require" => "true", 'validator' => 'Email', "message" => $lang['member_edit_valid_email']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $insert_array = array();
                $insert_array['member_name'] = trim($_POST['member_name']);
                $insert_array['member_passwd'] = trim($_POST['member_passwd']);
                $insert_array['member_email'] = trim($_POST['member_email']);
                $insert_array['member_truename'] = trim($_POST['member_truename']);
                $insert_array['member_sex'] = trim($_POST['member_sex']);
                $insert_array['member_qq'] = trim($_POST['member_qq']);
                $insert_array['member_ww'] = trim($_POST['member_ww']);
                //默认允许举报商品
                $insert_array['inform_allow'] = '1';
                if (!empty($_POST['member_avatar'])) {
                    $insert_array['member_avatar'] = trim($_POST['member_avatar']);
                }
                $result = $model_member->addMember($insert_array);
                if ($result) {
                    $url = 'index.php?act=member&op=member';
                    $this->log(lang('nc_add,member_index_name') . '[	' . $_POST['member_name'] . ']', 1);
                    success($lang['member_add_succ'], $url);
                } else {
                    error($lang['member_add_fail']);
                }
            }
        }
        core\tpl::showpage('member.add');
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            /**
             * 验证会员是否重复
             */
            case 'check_user_name':
                $model_member = model('member');
                $condition['member_name'] = $_GET['member_name'];
                $condition['member_id'] = array('neq', intval($_GET['member_id']));
                $list = $model_member->getMemberInfo($condition);
                if (empty($list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
                /**
                 * 验证邮件是否重复
                 */
            /**
             * 验证邮件是否重复
             */
            case 'check_email':
                $model_member = model('member');
                $condition['member_email'] = $_GET['member_email'];
                $condition['member_id'] = array('neq', intval($_GET['member_id']));
                $list = $model_member->getMemberInfo($condition);
                if (empty($list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }
}