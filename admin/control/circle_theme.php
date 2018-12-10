<?php
/**
 * 圈子话题管理
 *
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class circle_theme extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('circle');
    }
    /**
     * 话题列表
     */
    public function theme_listOp()
    {
        $model = model();
        if (chksubmit()) {
            if (!empty($_POST['check_theme_id']) && is_array($_POST['check_theme_id'])) {
                foreach ($_POST['check_theme_id'] as $t_id) {
                    $theme_info = $model->table('circle_theme')->where(array('theme_id' => $t_id))->find();
                    if (empty($theme_info)) {
                        continue;
                    }
                    // 删除附件
                    $affix_list = $model->table('circle_affix')->where(array('theme_id' => $t_id))->select();
                    if (!empty($affix_list)) {
                        foreach ($affix_list as $val) {
                            unlink(themeImagePath($val['affix_filename']));
                            unlink(themeImagePath($val['affix_filethumb']));
                        }
                        $model->table('circle_affix')->where(array('theme_id' => $t_id))->delete();
                    }
                    // 删除商品
                    $model->table('circle_thg')->where(array('theme_id' => $t_id))->delete();
                    // 删除赞表相关
                    $model->table('circle_like')->where(array('theme_id' => $t_id))->delete();
                    // 删除回复
                    $model->table('circle_threply')->where(array('theme_id' => $t_id))->delete();
                    // 更新圈子主题数量
                    $model->table('circle')->update(array('circle_id' => $theme_info['circle_id'], 'circle_thcount' => array('exp', 'circle_thcount-1')));
                    // The recycle bin add delete records
                    $param = array();
                    $param['theme_id'] = $t_id;
                    $param['op_id'] = 0;
                    $param['op_name'] = lang('cirlce_administrator');
                    $param['type'] = 'admintheme';
                    model('circle_recycle')->saveRecycle($param, $theme_info);
                    // 删除话题
                    $model->table('circle_theme')->delete($t_id);
                    // Experience
                    if (intval($theme_info['theme_exp']) > 0) {
                        $param = array();
                        $param['member_id'] = $theme_info['member_id'];
                        $param['member_name'] = $theme_info['member_name'];
                        $param['circle_id'] = $theme_info['circle_id'];
                        $param['itemid'] = $t_id;
                        $param['type'] = 'delRelease';
                        $param['exp'] = $theme_info['theme_exp'];
                        model('circle_exp')->saveExp($param);
                    }
                }
            }
            success(lang('nc_common_op_succ'), 'index.php?act=circle_theme&op=theme_list');
        }
        $where = array();
        if (!empty($_GET['searchname'])) {
            $where['theme_name'] = array('like', '%' . $_GET['searchname'] . '%');
        }
        if (!empty($_GET['classname'])) {
            $where['circle_name'] = array('like', '%' . $_GET['classname'] . '%');
        }
        if (!empty($_GET['searchtop']) && in_array($_GET['searchtop'], array(0, 1))) {
            $where['is_stick'] = intval($_GET['searchtop']);
        }
        if (!empty($_GET['searchcream']) && in_array($_GET['searchcream'], array(0, 1))) {
            $where['is_digest'] = intval($_GET['searchcream']);
        }
        if (!empty($_GET['searchrecommend']) && in_array($_GET['searchrecommend'], array(0, 1))) {
            $where['is_recommend'] = intval($_GET['searchrecommend']);
        }
        $theme_list = $model->table('circle_theme')->where($where)->order('theme_id desc')->page(10)->select();
        if (!empty($theme_list)) {
            $theme_list = array_under_reset($theme_list, 'theme_id');
            $themeid_array = array_keys($theme_list);
            // 附件
            $affix_list = $model->table('circle_affix')->where(array('theme_id' => array('in', $themeid_array), 'affix_type' => 1))->group('theme_id')->select();
            if (!empty($affix_list)) {
                $affix_list = array_under_reset($affix_list, 'theme_id');
            }
            foreach ($theme_list as $key => $val) {
                if (isset($affix_list[$val['theme_id']])) {
                    $theme_list[$key]['affix'] = themeImageUrl($affix_list[$val['theme_id']]['affix_filethumb']);
                }
            }
        }
        core\tpl::output('theme_list', $theme_list);
        core\tpl::output('page', $model->showpage(2));
        core\tpl::showpage('circle_theme.list');
    }
    /**
     * 话题详细
     */
    public function theme_infoOp()
    {
        $model = model();
        $t_id = intval($_GET['t_id']);
        $theme_info = $model->table('circle_theme')->find($t_id);
        core\tpl::output('theme_info', $theme_info);
        if ($theme_info['theme_special'] == 1) {
            $poll_info = $model->table('circle_thpoll')->find($t_id);
            $option_list = $model->table('circle_thpolloption')->where(array('theme_id' => $t_id))->order('pollop_sort asc')->select();
            core\tpl::output('poll_info', $poll_info);
            core\tpl::output('option_list', $option_list);
        }
        core\tpl::showpage('circle_theme.info');
    }
    /**
     * 删除话题
     */
    public function theme_delOp()
    {
        $model = model();
        // 验证话题
        $t_id = intval($_GET['t_id']);
        $c_id = intval($_GET['c_id']);
        $theme_info = $model->table('circle_theme')->where(array('theme_id' => $t_id, 'circle_id' => $c_id))->find();
        if (empty($theme_info)) {
            error(lang('param_error'));
        }
        // 删除附件
        $affix_list = $model->table('circle_affix')->where(array('theme_id' => $t_id))->select();
        if (!empty($affix_list)) {
            foreach ($affix_list as $val) {
                unlink(themeImagePath($val['affix_filename']));
                unlink(themeImagePath($val['affix_filethumb']));
            }
            $model->table('circle_affix')->where(array('theme_id' => $t_id))->delete();
        }
        // 删除商品
        $model->table('circle_thg')->where(array('theme_id' => $t_id))->delete();
        // 删除赞表相关
        $model->table('circle_like')->where(array('theme_id' => $t_id))->delete();
        // 删除回复
        $model->table('circle_threply')->where(array('theme_id' => $t_id))->delete();
        // The recycle bin add delete records
        $param = array();
        $param['theme_id'] = $t_id;
        $param['op_id'] = 0;
        $param['op_name'] = lang('cirlce_administrator');
        $param['type'] = 'admintheme';
        model('circle_recycle')->saveRecycle($param, $theme_info);
        // 删除话题
        $model->table('circle_theme')->delete($t_id);
        // 更新圈子主题数量
        $model->table('circle')->update(array('circle_id' => $c_id, 'circle_thcount' => array('exp', 'circle_thcount-1')));
        // Experience
        if (intval($theme_info['theme_exp']) > 0) {
            $param = array();
            $param['member_id'] = $theme_info['member_id'];
            $param['member_name'] = $theme_info['member_name'];
            $param['circle_id'] = $theme_info['circle_id'];
            $param['itemid'] = $t_id;
            $param['type'] = 'delRelease';
            $param['exp'] = $theme_info['theme_exp'];
            model('circle_exp')->saveExp($param);
        }
        success(lang('nc_common_op_succ'), 'index.php?act=circle_theme&op=theme_list');
    }
    /**
     * 话题回复
     */
    public function theme_replyOp()
    {
        $model = model();
        if (chksubmit()) {
            $t_id = intval($_POST['t_id']);
            if (!empty($_POST['check_reply_id']) && is_array($_POST['check_reply_id'])) {
                foreach ($_POST['check_reply_id'] as $r_id) {
                    // 验证回复
                    $reply_info = $model->table('circle_threply')->where(array('theme_id' => $t_id, 'reply_id' => $r_id))->find();
                    if (empty($reply_info)) {
                        error(lang('param_error'));
                    }
                    // 删除附件
                    $affix_list = $model->table('circle_affix')->where(array('affix_type' => 2, 'theme_id' => $t_id, 'reply_id' => $r_id))->select();
                    if (!empty($affix_list)) {
                        foreach ($affix_list as $val) {
                            unlink(themeImagePath($val['affix_filename']));
                            unlink(themeImagePath($val['affix_filethumb']));
                        }
                        $model->table('circle_affix')->where(array('affix_type' => 2, 'theme_id' => $t_id, 'reply_id' => $r_id))->delete();
                    }
                    // 删除商品
                    $model->table('circle_thg')->where(array('theme_id' => $t_id, 'reply_id' => $r_id))->delete();
                    // The recycle bin add delete records
                    $param = array();
                    $param['theme_id'] = $t_id;
                    $param['reply_id'] = $r_id;
                    $param['op_id'] = 0;
                    $param['op_name'] = lang('cirlce_administrator');
                    $param['type'] = 'adminreply';
                    model('circle_recycle')->saveRecycle($param, $reply_info);
                    // 删除回复
                    $model->table('circle_threply')->where(array('theme_id' => $t_id, 'reply_id' => $r_id))->delete();
                    // 更新话题回复数
                    $model->table('circle_theme')->update(array('theme_id' => $t_id, 'theme_commentcount' => array('exp', 'theme_commentcount-1')));
                    // Experience
                    if (intval($reply_info['reply_exp']) > 0) {
                        $param = array();
                        $param['member_id'] = $reply_info['member_id'];
                        $param['member_name'] = $reply_info['member_name'];
                        $param['circle_id'] = $reply_info['circle_id'];
                        $param['itemid'] = $t_id . ',' . $r_id;
                        $param['type'] = 'delReplied';
                        $param['exp'] = $reply_info['reply_exp'];
                        model('circle_exp')->saveExp($param);
                    }
                }
            }
            success(lang('nc_common_op_succ'));
        }
        $t_id = intval($_GET['t_id']);
        $reply_list = $model->table('circle_threply')->where(array('theme_id' => $t_id))->page(10)->select();
        core\tpl::output('t_id', $t_id);
        core\tpl::output('page', $model->showpage(2));
        core\tpl::output('reply_list', $reply_list);
        core\tpl::showpage('circle_theme.reply');
    }
    /**
     * 话题回复删除
     */
    public function theme_replydelOp()
    {
        $t_id = intval($_GET['t_id']);
        $r_id = intval($_GET['r_id']);
        $model = model();
        // 验证回复
        $reply_info = $model->table('circle_threply')->where(array('theme_id' => $t_id, 'reply_id' => $r_id))->find();
        if (empty($reply_info)) {
            error(lang('param_error'));
        }
        // 删除附件
        $affix_list = $model->table('circle_affix')->where(array('affix_type' => 2, 'theme_id' => $t_id, 'reply_id' => $r_id))->select();
        if (!empty($affix_list)) {
            foreach ($affix_list as $val) {
                unlink(themeImagePath($val['affix_filename']));
                unlink(themeImagePath($val['affix_filethumb']));
            }
            $model->table('circle_affix')->where(array('affix_type' => 2, 'theme_id' => $t_id, 'reply_id' => $r_id))->delete();
        }
        // 删除商品
        $model->table('circle_thg')->where(array('theme_id' => $t_id, 'reply_id' => $r_id))->delete();
        // The recycle bin add delete records
        $param = array();
        $param['theme_id'] = $t_id;
        $param['reply_id'] = $r_id;
        $param['op_id'] = 0;
        $param['op_name'] = lang('cirlce_administrator');
        $param['type'] = 'adminreply';
        model('circle_recycle')->saveRecycle($param, $reply_info);
        // 删除回复
        $model->table('circle_threply')->where(array('theme_id' => $t_id, 'reply_id' => $r_id))->delete();
        // 更新话题回复数
        $model->table('circle_theme')->update(array('theme_id' => $t_id, 'theme_commentcount' => array('exp', 'theme_commentcount-1')));
        // Experience
        if (intval($reply_info['reply_exp']) > 0) {
            $param = array();
            $param['member_id'] = $reply_info['member_id'];
            $param['member_name'] = $reply_info['member_name'];
            $param['circle_id'] = $reply_info['circle_id'];
            $param['itemid'] = $t_id . ',' . $r_id;
            $param['type'] = 'delReplied';
            $param['exp'] = $reply_info['reply_exp'];
            model('circle_exp')->saveExp($param);
        }
        success(lang('nc_common_op_succ'));
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            case 'recommend':
                $update = array('theme_id' => intval($_GET['id']), $_GET['column'] => $_GET['value']);
                model()->table('circle_theme')->update($update);
                echo 'true';
                break;
        }
    }
}