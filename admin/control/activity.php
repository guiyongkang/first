<?php
/**
 * 活动管理
 *
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class activity extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('activity');
    }
    /**
     * 活动列表
     */
    public function indexOp()
    {
        $this->activityOp();
    }
    /**
     * 活动列表/删除活动
     */
    public function activityOp()
    {
        $activity = model('activity');
        //条件
        $condition_arr = array();
        $condition_arr['activity_type'] = '1';
        //只显示商品活动
        //状态
        if (!empty($_GET['searchstate'])) {
            $state = intval($_GET['searchstate']) - 1;
            $condition_arr['activity_state'] = "{$state}";
        }
        //标题
        if (!empty($_GET['searchtitle'])) {
            $condition_arr['activity_title'] = $_GET['searchtitle'];
        }
        //有效期范围
        if (!empty($_GET['searchstartdate']) && !empty($_GET['searchenddate'])) {
            $condition_arr['activity_daterange']['startdate'] = strtotime($_GET['searchstartdate']);
            $condition_arr['activity_daterange']['enddate'] = strtotime($_GET['searchenddate']);
            if ($condition_arr['activity_daterange']['enddate'] > 0) {
                $condition_arr['activity_daterange']['enddate'] += 86400;
            }
        }
        $condition_arr['order'] = 'activity_sort asc';
        //活动列表
        $page = new lib\page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        $list = $activity->getList($condition_arr, $page);
        //输出
        core\tpl::output('show_page', $page->show());
        core\tpl::output('list', $list);
        core\tpl::showpage('activity.index');
    }
    /**
     * 新建活动/保存新建活动
     */
    public function newOp()
    {
        //新建处理
        if (!chksubmit()) {
            core\tpl::showpage('activity.add');
            exit;
        }
        //提交表单
        $obj_validate = new lib\validate();
        $validate_arr[] = array("input" => $_POST["activity_title"], "require" => "true", "message" => core\language::get('activity_new_title_null'));
        $validate_arr[] = array("input" => $_POST["activity_start_date"], "require" => "true", "message" => core\language::get('activity_new_startdate_null'));
        $validate_arr[] = array("input" => $_POST["activity_end_date"], "require" => "true", 'validator' => 'Compare', 'operator' => '>', 'to' => "{$_POST['activity_start_date']}", "message" => core\language::get('activity_new_enddate_null'));
        $validate_arr[] = array("input" => $_POST["activity_style"], "require" => "true", "message" => core\language::get('activity_new_style_null'));
        $validate_arr[] = array('input' => $_POST['activity_type'], 'require' => 'true', 'message' => core\language::get('activity_new_type_null'));
        $validate_arr[] = array('input' => $_FILES['activity_banner']['name'], 'require' => 'true', 'message' => core\language::get('activity_new_banner_null'));
        $validate_arr[] = array('input' => $_POST['activity_sort'], 'require' => 'true', 'validator' => 'Range', 'min' => 0, 'max' => 255, 'message' => core\language::get('activity_new_sort_error'));
        $obj_validate->validateparam = $validate_arr;
        $error = $obj_validate->validate();
        if ($error != '') {
            error(core\language::get('error') . $error, '', '', 'error');
        }
        $upload = new lib\uploadfile();
        $upload->set('default_dir', ATTACH_ACTIVITY);
        $result = $upload->upfile('activity_banner');
        if (!$result) {
            error($upload->error);
        }
        //保存
        $input = array();
        $input['activity_title'] = trim($_POST['activity_title']);
        //$input['activity_type']		= trim($_POST['activity_type']);
        $input['activity_type'] = '1';
        $input['activity_banner'] = $upload->file_name;
        $input['activity_style'] = trim($_POST['activity_style']);
        $input['activity_desc'] = trim($_POST['activity_desc']);
        $input['activity_sort'] = intval(trim($_POST['activity_sort']));
        $input['activity_start_date'] = strtotime(trim($_POST['activity_start_date']));
        $input['activity_end_date'] = strtotime(trim($_POST['activity_end_date']));
        $input['activity_state'] = intval($_POST['activity_state']);
        $activity = model('activity');
        $result = $activity->add($input);
        if ($result) {
            $this->log(lang('nc_add,activity_index') . '[' . $_POST['activity_title'] . ']', null);
            success(core\language::get('nc_common_op_succ'), 'index.php?act=activity&op=activity');
        } else {
            //添加失败则删除刚刚上传的图片,节省空间资源
            unlink(BASE_UPLOAD_PATH . DS . ATTACH_ACTIVITY . DS . $upload->file_name);
            error(core\language::get('nc_common_op_fail'));
        }
    }
    /**
     * 异步修改
     */
    public function ajaxOp()
    {
        if (in_array($_GET['branch'], array('activity_title', 'activity_sort'))) {
            $activity = model('activity');
            $update_array = array();
            switch ($_GET['branch']) {
                /**
                 * 活动主题
                 */
                case 'activity_title':
                    if (trim($_GET['value']) == '') {
                        exit;
                    }
                    break;
                    /**
                     * 排序
                     */
                /**
                 * 排序
                 */
                case 'activity_sort':
                    if (preg_match('/^\\d+$/', trim($_GET['value'])) <= 0 or intval(trim($_GET['value'])) < 0 or intval(trim($_GET['value'])) > 255) {
                        exit;
                    }
                    break;
                default:
                    exit;
            }
            $update_array[$_GET['column']] = trim($_GET['value']);
            if ($activity->update($update_array, intval($_GET['id']))) {
                echo 'true';
            }
        } elseif (in_array($_GET['branch'], array('activity_detail_sort'))) {
            $activity_detail = model('activity_detail');
            $update_array = array();
            switch ($_GET['branch']) {
                /**
                 * 排序
                 */
                case 'activity_detail_sort':
                    if (preg_match('/^\\d+$/', trim($_GET['value'])) <= 0 or intval(trim($_GET['value'])) < 0 or intval(trim($_GET['value'])) > 255) {
                        exit;
                    }
                    break;
                default:
                    exit;
            }
            $update_array[$_GET['column']] = trim($_GET['value']);
            if ($activity_detail->update($update_array, intval($_GET['id']))) {
                echo 'true';
            }
        }
    }
    /**
     * 删除活动
     */
    public function delOp()
    {
        $id = '';
        if (empty($_REQUEST['activity_id'])) {
            error(core\language::get('activity_del_choose_activity'));
        }
        if (is_array($_POST['activity_id'])) {
            try {
                //删除数据先删除横幅图片，节省空间资源
                foreach ($_POST['activity_id'] as $v) {
                    $this->delBanner(intval($v));
                }
            } catch (\Exception $e) {
                error($e->getMessage());
            }
            $id = "'" . implode("','", $_POST['activity_id']) . "'";
        } else {
            //删除数据先删除横幅图片，节省空间资源
            $this->delBanner(intval($_GET['activity_id']));
            $id = intval($_GET['activity_id']);
        }
        $activity = model('activity');
        $activity_detail = model('activity_detail');
        //获取可以删除的数据
        $condition_arr = array();
        $condition_arr['activity_state'] = '0';
        //已关闭
        $condition_arr['activity_enddate_greater_or'] = time();
        //过期
        $condition_arr['activity_id_in'] = $id;
        $activity_list = $activity->getList($condition_arr);
        if (empty($activity_list)) {
            //没有符合条件的活动信息直接返回成功信息
            success(core\language::get('nc_common_del_succ'));
        }
        $id_arr = array();
        foreach ($activity_list as $v) {
            $id_arr[] = $v['activity_id'];
        }
        $id_new = "'" . implode("','", $id_arr) . "'";
        //只有关闭或者过期的活动，能删除
        if ($activity_detail->del($id_new)) {
            if ($activity->del($id_new)) {
                $this->log(lang('nc_del,activity_index') . '[ID:' . $id . ']', null);
                success(core\language::get('nc_common_del_succ'));
            }
        }
        error(core\language::get('activity_del_fail'));
    }
    /**
     * 编辑活动/保存编辑活动
     */
    public function editOp()
    {
        if (!chksubmit()) {
            if (empty($_GET['activity_id'])) {
                error(core\language::get('miss_argument'));
            }
            $activity = model('activity');
            $row = $activity->getOneById(intval($_GET['activity_id']));
            core\tpl::output('activity', $row);
            core\tpl::showpage('activity.edit');
            exit;
        }
        //提交表单
        $obj_validate = new lib\validate();
        $validate_arr[] = array("input" => $_POST["activity_title"], "require" => "true", "message" => core\language::get('activity_new_title_null'));
        $validate_arr[] = array("input" => $_POST["activity_start_date"], "require" => "true", "message" => core\language::get('activity_new_startdate_null'));
        $validate_arr[] = array("input" => $_POST["activity_end_date"], "require" => "true", 'validator' => 'Compare', 'operator' => '>', 'to' => "{$_POST['activity_start_date']}", "message" => core\language::get('activity_new_enddate_null'));
        $validate_arr[] = array("input" => $_POST["activity_style"], "require" => "true", "message" => core\language::get('activity_new_style_null'));
        $validate_arr[] = array('input' => $_POST['activity_type'], 'require' => 'true', 'message' => core\language::get('activity_new_type_null'));
        $validate_arr[] = array('input' => $_POST['activity_desc'], 'require' => 'true', 'message' => core\language::get('activity_new_desc_null'));
        $validate_arr[] = array('input' => $_POST['activity_sort'], 'require' => 'true', 'validator' => 'Range', 'min' => 0, 'max' => 255, 'message' => core\language::get('activity_new_sort_error'));
        $obj_validate->validateparam = $validate_arr;
        $error = $obj_validate->validate();
        if ($error != '') {
            error(core\language::get('error') . $error);
        }
        //构造更新内容
        $input = array();
        if ($_FILES['activity_banner']['name'] != '') {
            $upload = new lib\uploadfile();
            $upload->set('default_dir', ATTACH_ACTIVITY);
            $result = $upload->upfile('activity_banner');
            if (!$result) {
                error($upload->error);
            }
            $input['activity_banner'] = $upload->file_name;
        }
        $input['activity_title'] = trim($_POST['activity_title']);
        $input['activity_type'] = trim($_POST['activity_type']);
        $input['activity_style'] = trim($_POST['activity_style']);
        $input['activity_desc'] = trim($_POST['activity_desc']);
        $input['activity_sort'] = intval(trim($_POST['activity_sort']));
        $input['activity_start_date'] = strtotime(trim($_POST['activity_start_date']));
        $input['activity_end_date'] = strtotime(trim($_POST['activity_end_date']));
        $input['activity_state'] = intval($_POST['activity_state']);
        $activity = model('activity');
        $row = $activity->getOneById(intval($_POST['activity_id']));
        $result = $activity->update($input, intval($_POST['activity_id']));
        if ($result) {
            if ($_FILES['activity_banner']['name'] != '') {
                unlink(BASE_UPLOAD_PATH . DS . ATTACH_ACTIVITY . DS . $row['activity_banner']);
            }
            $this->log(lang('nc_edit,activity_index') . '[ID:' . $_POST['activity_id'] . ']', null);
            error(core\language::get('nc_common_save_succ'), 'index.php?act=activity&op=activity');
        } else {
            if ($_FILES['activity_banner']['name'] != '') {
                unlink(BASE_UPLOAD_PATH . DS . ATTACH_ACTIVITY . DS . $upload->file_name);
            }
            error(core\language::get('nc_common_save_fail'));
        }
    }
    /**
     * 活动细节列表
     */
    public function detailOp()
    {
        $activity_id = intval($_GET['id']);
        if ($activity_id <= 0) {
            error(core\language::get('miss_argument'));
        }
        //条件
        $condition_arr = array();
        $condition_arr['activity_id'] = $activity_id;
        //审核状态
        if (!empty($_GET['searchstate'])) {
            $state = intval($_GET['searchstate']) - 1;
            $condition_arr['activity_detail_state'] = "{$state}";
        }
        //店铺名称
        if (!empty($_GET['searchstore'])) {
            $condition_arr['store_name'] = $_GET['searchstore'];
        }
        //商品名称
        if (!empty($_GET['searchgoods'])) {
            $condition_arr['item_name'] = $_GET['searchgoods'];
        }
        $condition_arr['order'] = 'activity_detail.activity_detail_state asc,activity_detail.activity_detail_sort asc';
        $page = new lib\page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        $activitydetail_model = model('activity_detail');
        $list = $activitydetail_model->getList($condition_arr, $page);
        //输出到模板
        core\tpl::output('show_page', $page->show());
        core\tpl::output('list', $list);
        core\tpl::showpage('activity_detail.index');
    }
    /**
     * 活动内容处理
     */
    public function dealOp()
    {
        if (empty($_REQUEST['activity_detail_id'])) {
            error(core\language::get('activity_detail_del_choose_detail'));
        }
        //获取id
        $id = '';
        if (is_array($_POST['activity_detail_id'])) {
            $id = "'" . implode("','", $_POST['activity_detail_id']) . "'";
        } else {
            $id = intval($_GET['activity_detail_id']);
        }
        //创建活动内容对象
        $activity_detail = model('activity_detail');
        if ($activity_detail->update(array('activity_detail_state' => intval($_GET['state'])), $id)) {
            $this->log(lang('nc_edit,activity_index') . '[ID:' . $id . ']', null);
            success(core\language::get('nc_common_op_succ'));
        } else {
            error(core\language::get('nc_common_op_fail'));
        }
    }
    /**
     * 删除活动内容
     */
    public function del_detailOp()
    {
        if (empty($_REQUEST['activity_detail_id'])) {
            error(core\language::get('activity_detail_del_choose_detail'));
        }
        $id = '';
        if (is_array($_POST['activity_detail_id'])) {
            $id = "'" . implode("','", $_POST['activity_detail_id']) . "'";
        } else {
            $id = "'" . intval($_GET['activity_detail_id']) . "'";
        }
        $activity_detail = model('activity_detail');
        //条件
        $condition_arr = array();
        $condition_arr['activity_detail_id_in'] = $id;
        $condition_arr['activity_detail_state_in'] = "'0','2'";
        //未审核和已拒绝
        if ($activity_detail->delList($condition_arr)) {
            $this->log(lang('nc_del,activity_index_content') . '[ID:' . $id . ']', null);
            success(core\language::get('nc_common_del_succ'));
        } else {
            error(core\language::get('nc_common_del_fail'));
        }
    }
    /**
     * 根据活动编号删除横幅图片
     *
     * @param int $id
     */
    private function delBanner($id)
    {
        $activity = model('activity');
        $row = $activity->getOneById($id);
        //删除图片文件
        unlink(BASE_UPLOAD_PATH . DS . ATTACH_ACTIVITY . DS . $row['activity_banner']);
    }
}