<?php
/**
 * Circle Inform
 *
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class circle_inform extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('circle_inform');
    }
    /**
     * inform list
     */
    public function inform_listOp()
    {
        $model = model();
        if (chksubmit()) {
            if (empty($_POST['i_id'])) {
                error(lang('wrong_argument'));
            }
            // check
            foreach ($_POST['i_id'] as $key => $val) {
                if (!is_numeric($val)) {
                    unset($_POST[$key]);
                }
            }
            $rs = $model->table('circle_inform')->where(array('inform_id' => array('in', $_POST['i_id'])))->delete();
            if ($rs) {
                success(lang('nc_common_op_succ'));
            } else {
                error(lang('nc_common_op_fail'));
            }
        }
        $where = array();
        if (!empty($_GET['searchname'])) {
            $where['member_name'] = array('like', '%' . $_GET['searchname'] . '%');
        }
        if (!empty($_GET['circlename'])) {
            $where['circle_name'] = array('like', '%' . $_GET['circlename'] . '%');
        }
        if (!empty($_GET['searchstate'])) {
            $where['inform_state'] = intval($_GET['searchstate']);
        }
        $inform_list = $model->table('circle_inform')->where($where)->page(10)->order('inform_id desc')->select();
        // tidy
        if (!empty($inform_list)) {
            foreach ($inform_list as $key => $val) {
                $inform_list[$key]['url'] = $this->spellInformUrl($val);
                $inform_list[$key]['title'] = lang('circle_theme,nc_quote1') . $val['theme_name'] . lang('nc_quote2');
                $inform_list[$key]['state'] = $this->informStatr(intval($val['inform_state']));
                if ($val['reply_id'] != 0) {
                    $inform_list[$key]['title'] .= lang('circle_inform_reply_title');
                }
            }
        }
        core\tpl::output('inform_list', $inform_list);
        core\tpl::output('show_page', $model->showpage(2));
        core\tpl::showpage('circle_inform');
    }
    /**
     * Inform delete
     */
    public function inform_delOp()
    {
        $i_id = intval($_GET['i_id']);
        if ($i_id <= 0) {
            error(lang('wrong_argument'));
        }
        $rs = model()->table('circle_inform')->delete($i_id);
        if ($rs) {
            success(lang('nc_common_op_succ'));
        } else {
            error(lang('nc_common_op_fail'));
        }
    }
    /**
     * Inform Url link
     */
    public function spellInformUrl($param)
    {
        if ($param['reply_id'] == 0) {
            return $url = 'index.php?act=theme&op=theme_detail&c_id=' . $param['circle_id'] . '&t_id=' . $param['theme_id'];
        }
        $where = array();
        $where['circle_id'] = $param['circle_id'];
        $where['theme_id'] = $param['theme_id'];
        $where['reply_id'] = array('elt', $param['reply_id']);
        $count = model()->table('circle_threply')->where($where)->count();
        $page = ceil($count / 15);
        return $url = 'index.php?act=theme&op=theme_detail&c_id=' . $param['circle_id'] . '&t_id=' . $param['theme_id'] . '&curpage=' . $page . '#f' . $param['reply_id'];
    }
    /**
     * Inform state
     */
    private function informStatr($state)
    {
        switch ($state) {
            case 0:
                return lang('circle_inform_untreated');
                break;
            case 1:
                return lang('circle_inform_treated');
                break;
        }
    }
}