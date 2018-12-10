<?php
/**
 * 积分管理
 *
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class points extends SystemControl
{
    const EXPORT_SIZE = 5000;
    public function __construct()
    {
        parent::__construct();
        core\language::read('points');
        //判断系统是否开启积分功能
        if (core\config::get('points_isuse') != 1) {
            error(core\language::get('admin_points_unavailable'), 'index.php?act=dashboard&op=welcome');
        }
    }
    /**
     * 积分添加
     */
    public function addpointsOp()
    {
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["member_id"], "require" => "true", "message" => core\language::get('admin_points_member_error_again')), array("input" => $_POST["pointsnum"], "require" => "true", 'validator' => 'Compare', 'operator' => ' >= ', 'to' => 1, "message" => core\language::get('admin_points_points_min_error')));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            }
            //查询会员信息
            $obj_member = model('member');
            $member_id = intval($_POST['member_id']);
            $member_info = $obj_member->getMemberInfo(array('member_id' => $member_id));
            if (!is_array($member_info) || count($member_info) <= 0) {
                error(core\language::get('admin_points_userrecord_error'), 'index.php?act=points&op=addpoints');
            }
            $pointsnum = intval($_POST['pointsnum']);
            if ($_POST['operatetype'] == 2 && $pointsnum > intval($member_info['member_points'])) {
                error(core\language::get('admin_points_points_short_error') . $member_info['member_points'], 'index.php?act=points&op=addpoints', '', 'error');
            }
            $obj_points = model('points');
            $insert_arr['pl_memberid'] = $member_info['member_id'];
            $insert_arr['pl_membername'] = $member_info['member_name'];
            $admininfo = $this->getAdminInfo();
            $insert_arr['pl_adminid'] = $admininfo['id'];
            $insert_arr['pl_adminname'] = $admininfo['name'];
            if ($_POST['operatetype'] == 2) {
                $insert_arr['pl_points'] = -$_POST['pointsnum'];
            } else {
                $insert_arr['pl_points'] = $_POST['pointsnum'];
            }
            if ($_POST['pointsdesc']) {
                $insert_arr['pl_desc'] = trim($_POST['pointsdesc']);
            } else {
                $insert_arr['pl_desc'] = core\language::get('admin_points_system_desc');
            }
            $result = $obj_points->savePointsLog('system', $insert_arr, true);
            if ($result) {
                $this->log(lang('admin_points_mod_tip') . $member_info['member_name'] . '[' . ($_POST['operatetype'] == 2 ? '' : '+') . strval($insert_arr['pl_points']) . ']', null);
                success(core\language::get('nc_common_save_succ'), 'index.php?act=points&op=addpoints');
            } else {
                error(core\language::get('nc_common_save_fail'), 'index.php?act=points&op=addpoints');
            }
        } else {
            core\tpl::showpage('points.add');
        }
    }
    public function checkmemberOp()
    {
        $name = trim($_GET['name']);
        if (!$name) {
            echo '';
            die;
        }
        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK') {
            $name = core\language::getGBK($name);
        }
        $obj_member = model('member');
        $member_info = $obj_member->getMemberInfo(array('member_name' => $name));
        if (is_array($member_info) && count($member_info) > 0) {
            if (strtoupper(CHARSET) == 'GBK') {
                $member_info['member_name'] = core\language::getUTF8($member_info['member_name']);
            }
            echo json_encode(array('id' => $member_info['member_id'], 'name' => $member_info['member_name'], 'points' => $member_info['member_points']));
        } else {
            echo '';
            die;
        }
    }
    /**
     * 积分日志列表
     */
    public function pointslogOp()
    {
        $condition_arr = array();
        $condition_arr['pl_membername_like'] = isset($_GET['mname']) ? trim($_GET['mname']) : '';
        $condition_arr['pl_adminname_like'] = isset($_GET['aname']) ? trim($_GET['aname']) : '';
        if (isset($_GET['stage'])) {
            $condition_arr['pl_stage'] = trim($_GET['stage']);
        }
        $condition_arr['saddtime'] = isset($_GET['stime']) ? strtotime($_GET['stime']) : '';
        $condition_arr['eaddtime'] = isset($_GET['etime']) ? strtotime($_GET['etime']) : '';
        if ($condition_arr['eaddtime'] > 0) {
            $condition_arr['eaddtime'] += 86400;
        }
        $condition_arr['pl_desc_like'] = isset($_GET['description']) ? trim($_GET['description']) : '';
        //分页
        $page = new lib\page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        //查询积分日志列表
        $points_model = model('points');
        $list_log = $points_model->getPointsLogList($condition_arr, $page, '*', '');
        //信息输出
        core\tpl::output('show_page', $page->show());
        core\tpl::output('list_log', $list_log);
        core\tpl::showpage('pointslog');
    }
    /**
     * 积分日志列表导出
     */
    public function export_step1Op()
    {
        $condition_arr = array();
        $condition_arr['pl_membername_like'] = isset($_GET['mname']) ? trim($_GET['mname']) : '';
        $condition_arr['pl_adminname_like'] = isset($_GET['aname']) ? trim($_GET['aname']) : '';
        if ($_GET['stage']) {
            $condition_arr['pl_stage'] = isset($_GET['stage']) ? trim($_GET['stage']) : '';
        }
        $condition_arr['saddtime'] = isset($_GET['stime']) ? strtotime($_GET['stime']) : '';
        $condition_arr['eaddtime'] = isset($_GET['etime']) ? strtotime($_GET['etime']) : '';
        if ($condition_arr['eaddtime'] > 0) {
            $condition_arr['eaddtime'] += 86400;
        }
        $condition_arr['pl_desc_like'] = isset($_GET['description']) ? trim($_GET['description']) : '';
        $page = new lib\page();
        $page->setEachNum(self::EXPORT_SIZE);
        $points_model = model('points');
        $list_log = $points_model->getPointsLogList($condition_arr, $page, '*', '');
        if (!is_numeric($_GET['curpage'])) {
            $count = $page->getTotalNum();
            $array = array();
            if ($count > self::EXPORT_SIZE) {
                //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                core\tpl::output('list', $array);
                core\tpl::output('murl', 'index.php?act=pointslog&op=pointslog');
                core\tpl::showpage('export.excel');
            } else {
                //如果数量小，直接下载
                $this->createExcel($list_log);
            }
        } else {
            //下载
            $this->createExcel($list_log);
        }
    }
    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array())
    {
        core\language::read('export');
        $excel_obj = new lib\excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_pi_member'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_pi_system'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_pi_point'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_pi_time'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_pi_jd'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_pi_ms'));
        $state_cn = array(core\language::get('admin_points_stage_regist'), core\language::get('admin_points_stage_login'), core\language::get('admin_points_stage_comments'), core\language::get('admin_points_stage_order'), core\language::get('admin_points_stage_system'), core\language::get('admin_points_stage_pointorder'), core\language::get('admin_points_stage_app'));
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['pl_membername']);
            $tmp[] = array('data' => $v['pl_adminname']);
            $tmp[] = array('format' => 'Number', 'data' => ncPriceFormat($v['pl_points']));
            $tmp[] = array('data' => date('Y-m-d H:i:s', $v['pl_addtime']));
            $tmp[] = array('data' => str_replace(array('regist', 'login', 'comments', 'order', 'system', 'pointorder', 'app'), $state_cn, $v['pl_stage']));
            $tmp[] = array('data' => $v['pl_desc']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_pi_jfmx'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_pi_jfmx'), CHARSET) . $_GET['curpage'] . '-' . date('Y-m-d-H', time()));
    }
}