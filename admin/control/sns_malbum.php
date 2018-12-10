<?php
/**
 * 会员相册管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class sns_malbum extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('sns_malbum');
    }
    /**
     * 相册设置
     */
    public function settingOp()
    {
        $model_setting = model('setting');
        if (chksubmit()) {
            //构造更新数据数组
            $update_array = array();
            $update_array['malbum_max_sum'] = intval($_POST['malbum_max_sum']);
            $result = $model_setting->updateSetting($update_array);
            if ($result === true) {
                success(core\language::get('nc_common_save_succ'));
            } else {
                error(core\language::get('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        core\tpl::output('list_setting', $list_setting);
        core\tpl::showpage('sns_malbum.setting');
    }
    /**
     * 相册列表
     */
    public function class_listOp()
    {
        $model = model();
        // 相册总数量
        $where = array();
        if (!empty($_GET['class_name'])) {
            $where['ac_name'] = array('like', '%' . trim($_GET['class_name']) . '%');
        }
        if (!empty($_GET['user_name'])) {
            $where['member_name'] = array('like', '%' . trim($_GET['user_name']) . '%');
        }
        $ac_list = $model->table('sns_albumclass,member')->field('sns_albumclass.*,member.member_name')->on('sns_albumclass.member_id = member.member_id')->join('left')->where($where)->page('10')->select();
        if (!empty($ac_list)) {
            $acid_array = array();
            foreach ($ac_list as $val) {
                $acid_array[] = $val['ac_id'];
            }
            // 相册中商品数量
            $ap_count = $model->cls()->table('sns_albumpic')->field('count(ap_id) as count,ac_id')->where(array('ac_id' => array('in', $acid_array)))->group('ac_id')->select();
            $ap_count = array_under_reset($ap_count, 'ac_id', 1);
            foreach ($ac_list as $key => $val) {
                if (isset($ap_count[$val['ac_id']])) {
                    $ac_list[$key]['count'] = $ap_count[$val['ac_id']]['count'];
                } else {
                    $ac_list[$key]['count'] = 0;
                }
            }
        }
        core\tpl::output('showpage', $model->showpage(2));
        core\tpl::output('ac_list', $ac_list);
        core\tpl::showpage('sns_malbum.classlist');
    }
    /**
     * 图片列表
     */
    public function pic_listOp()
    {
        $model = model();
        // 删除图片
        if (chksubmit()) {
            $where = array('ap_id' => array('in', $_POST['id']));
            $ap_list = $model->table('sns_albumpic')->where($where)->select();
            if (empty($ap_list)) {
                error(core\language::get('snsalbum_choose_need_del_img'));
            }
            foreach ($ap_list as $val) {
                unlink(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . $val['member_id'] . DS . $val['ap_cover']);
                unlink(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . $val['member_id'] . DS . str_ireplace('.', '_240.', $val['ap_cover']));
                unlink(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . $val['member_id'] . DS . str_ireplace('.', '_1280.', $val['ap_cover']));
            }
            $model->table('sns_albumpic')->where($where)->delete();
            $this->log(lang('nc_del,nc_member_album_manage') . '[ID:' . implode(',', $_POST['id']) . ']', 1);
            success(core\language::get('nc_common_del_succ'));
        }
        $id = intval($_GET['id']);
        if ($id <= 0) {
            error(core\language::get('param_error'));
        }
        $where = array();
        $where['ac_id'] = $id;
        if (!empty($_GET['pic_name'])) {
            $where['ap_name|ap_cover'] = array('like', '%' . $_GET['pic_name'] . '%');
        }
        $pic_list = $model->table('sns_albumpic')->where($where)->page(33)->select();
        core\tpl::output('id', $id);
        core\tpl::output('showpage', $model->showpage(2));
        core\tpl::output('pic_list', $pic_list);
        core\tpl::showpage('sns_malbum.piclist');
    }
    /**
     * 删除图片
     */
    public function del_picOp()
    {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            error(core\language::get('param_error'));
        }
        $model = model();
        $ap_info = $model->table('sns_albumpic')->find($id);
        if (!empty($ap_info)) {
            unlink(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . $ap_info['member_id'] . DS . $ap_info['ap_cover']);
            unlink(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . $ap_info['member_id'] . DS . str_ireplace('.', '_240.', $ap_info['ap_cover']));
            unlink(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . $ap_info['member_id'] . DS . str_ireplace('.', '_1280.', $ap_info['ap_cover']));
            $model->table('sns_albumpic')->delete($id);
        }
        success(core\language::get('nc_common_del_succ'));
    }
}