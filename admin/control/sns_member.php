<?php
/**
 * SNS动态
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class sns_member extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('sns_member');
    }
    public function indexOp()
    {
        $this->member_tagOp();
    }
    /**
     * 标签列表
     */
    public function member_tagOp()
    {
        // 实例化模型
        $model = model();
        if (chksubmit()) {
            switch ($_POST['submit_type']) {
                case 'del':
                    $result = $model->table('sns_membertag')->delete(implode(',', $_POST['id']));
                    if ($result) {
                        success(core\language::get('nc_common_op_succ'));
                    } else {
                        error(core\language::get('nc_common_op_fail'));
                    }
                    break;
            }
        }
        $tag_list = $model->table('sns_membertag')->order('mtag_sort asc')->page(10)->select();
        core\tpl::output('showpage', $model->showpage(2));
        core\tpl::output('tag_list', $tag_list);
        core\tpl::showpage('sns_membertag.index');
    }
    /**
     * 添加标签
     */
    public function tag_addOp()
    {
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["membertag_name"], "require" => "true", "message" => core\language::get('sns_member_tag_name_not_null')), array("input" => $_POST["membertag_sort"], "require" => "true", 'validator' => 'Number', "message" => core\language::get('sns_member_tag_sort_is_number')));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                /**
                 * 上传图片
                 */
                $upload = new lib\uploadfile();
                $upload->set('default_dir', ATTACH_PATH . '/membertag');
                $input = '';
                if (!empty($_FILES['membertag_img']['name'])) {
                    $result = $upload->upfile('membertag_img');
                    if (!$result) {
                        error($upload->error);
                    } else {
                        $input = $upload->file_name;
                    }
                }
                $insert = array('mtag_name' => $_POST['membertag_name'], 'mtag_sort' => intval($_POST['membertag_sort']), 'mtag_recommend' => intval($_POST['membertag_recommend']), 'mtag_desc' => trim($_POST['membertag_desc']), 'mtag_img' => $input);
                $model = model();
                $result = $model->table('sns_membertag')->insert($insert);
                if ($result) {
                    $url = array(array('url' => 'index.php?act=sns_member&op=tag_add', 'msg' => core\language::get('sns_member_add_once_more')), array('url' => 'index.php?act=sns_member&op=member_tag', 'msg' => core\language::get('sns_memner_return_list')));
                    $this->log(lang('nc_add,sns_member_tag') . '[' . $_POST['membertag_name'] . ']', 1);
                    success(core\language::get('nc_common_op_succ'), $url);
                } else {
                    error(core\language::get('nc_common_op_fail'));
                }
            }
        }
        core\tpl::showpage('sns_membertag.add');
    }
    /**
     * 编辑标签
     */
    public function tag_editOp()
    {
        // 实例化模型
        $model = model();
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["membertag_name"], "require" => "true", "message" => core\language::get('sns_member_tag_name_not_null')), array("input" => $_POST["membertag_sort"], "require" => "true", 'validator' => 'Number', "message" => core\language::get('sns_member_tag_sort_is_number')));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                /**
                 * 上传图片
                 */
                $upload = new lib\uploadfile();
                $upload->set('default_dir', ATTACH_PATH . '/membertag');
                if ($_POST['old_membertag_name'] != '') {
                    $upload->set('file_name', $_POST['old_membertag_name']);
                }
                $input = $_POST['old_membertag_name'];
                if (!empty($_FILES['membertag_img']['name'])) {
                    $result = $upload->upfile('membertag_img');
                    if (!$result) {
                        error($upload->error);
                    } else {
                        $input = $upload->file_name;
                    }
                }
                $update = array();
                $update['mtag_id'] = intval($_POST['id']);
                $update['mtag_name'] = trim($_POST['membertag_name']);
                $update['mtag_sort'] = intval($_POST['membertag_sort']);
                $update['mtag_recommend'] = intval($_POST['membertag_recommend']);
                $update['mtag_desc'] = trim($_POST['membertag_desc']);
                $update['mtag_img'] = $input;
                $result = $model->table('sns_membertag')->update($update);
                if ($result) {
                    $this->log(lang('nc_edit,sns_member_tag') . '[' . $_POST['membertag_name'] . ']', 1);
                    success(core\language::get('nc_common_op_succ'), 'index.php?act=sns_member&op=member_tag');
                } else {
                    error(core\language::get('nc_common_op_fail'));
                }
            }
        }
        // 验证
        $mtag_id = intval($_GET['id']);
        if ($mtag_id <= 0) {
            error(core\language::get('param_error'));
        }
        $mtag_info = $model->table('sns_membertag')->find($mtag_id);
        if (empty($mtag_info)) {
            error(core\language::get('param_error'));
        }
        core\tpl::output('mtag_info', $mtag_info);
        core\tpl::showpage('sns_membertag.edit');
    }
    /**
     * 删除标签
     */
    public function tag_delOp()
    {
        // 验证
        $mtag_id = intval($_GET['id']);
        if ($mtag_id <= 0) {
            error(core\language::get('param_error'));
        }
        $model = model();
        $result = $model->table('sns_membertag')->delete($mtag_id);
        if ($result) {
            $this->log(lang('nc_del,sns_member_tag') . '[ID:' . $mtag_id . ']', 1);
            success(core\language::get('nc_common_del_succ'));
        } else {
            error(core\language::get('nc_common_del_fail'));
        }
    }
    /**
     * 标签所属会员列表
     */
    public function tag_memberOp()
    {
        // 验证
        $mtag_id = intval($_GET['id']);
        if ($mtag_id <= 0) {
            error(core\language::get('param_error'));
        }
        $model = model();
        $count = $model->table('sns_mtagmember')->where(array('mtag_id' => $mtag_id))->count();
        $tagmember_list = $model->table('sns_mtagmember,member')->field('sns_mtagmember.*,member.member_avatar,member.member_name')->join('left')->on('sns_mtagmember.member_id=member.member_id')->where(array('sns_mtagmember.mtag_id' => $mtag_id))->page(10, $count)->order('sns_mtagmember.recommend desc, sns_mtagmember.member_id asc')->select();
        core\tpl::output('tagmember_list', $tagmember_list);
        core\tpl::output('showpage', $model->showpage(2));
        core\tpl::showpage('sns_membertag.memberlist');
    }
    /**
     * 删除添加标签会员
     */
    public function mtag_delOp()
    {
        $mtag_id = intval($_GET['id']);
        $member_id = intval($_GET['mid']);
        if ($mtag_id <= 0 || $member_id <= 0) {
            error(core\language::get('miss_argument'));
        }
        // 条件
        $where = array('mtag_id' => $mtag_id, 'member_id' => $member_id);
        $result = model()->table('sns_mtagmember')->where($where)->delete();
        if ($result) {
            $this->log(lang('nc_del,sns_member_tag') . '[ID:' . $mtag_id . ']', 1);
            success(core\language::get('nc_common_del_succ'));
        } else {
            error(core\language::get('nc_common_del_fail'));
        }
    }
    /**
     * ajax修改
     */
    public function ajaxOp()
    {
        // 实例化模型
        $model = model();
        switch ($_GET['branch']) {
            /**
             * 更新名称、排序、推荐
             */
            case 'membertag_name':
            case 'membertag_sort':
            case 'membertag_recommend':
                $update = array('mtag_id' => intval($_GET['id']), $_GET['column'] => $_GET['value']);
                $model->table('sns_membertag')->update($update);
                echo 'true';
                break;
                /**
                 * sns_mtagmember表推荐
                 */
            /**
             * sns_mtagmember表推荐
             */
            case 'mtagmember_recommend':
                list($where['mtag_id'], $where['member_id']) = explode(',', $_GET['id']);
                $update = array($_GET['column'] => $_GET['value']);
                $model->table('sns_mtagmember')->where($where)->update($update);
                echo 'true';
                break;
        }
    }
}