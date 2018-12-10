<?php
/**
 * 广告管理
 *
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class adv extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('adv');
    }
    /**
     *
     * 管理员添加广告
     */
    public function adv_addOp()
    {
        if (!chksubmit()) {
            $adv = model('adv');
            /**
             * 取广告位信息
             */
            $ap_list = $adv->getApList();
            core\tpl::output('ap_list', $ap_list);
            core\tpl::showpage('adv_add');
        } else {
            $lang = core\language::getLangContent();
            $adv = model('adv');
            $upload = new lib\uploadfile();
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $validate_arr = array();
            $validate_arr[] = array("input" => $_POST["adv_name"], "require" => "true", "message" => $lang['adv_can_not_null']);
            $validate_arr[] = array("input" => $_POST["aptype_hidden"], "require" => "true", "message" => $lang['must_select_ap']);
            $validate_arr[] = array("input" => $_POST["ap_id"], "require" => "true", "message" => $lang['must_select_ap']);
            $validate_arr[] = array("input" => $_POST["adv_start_time"], "require" => "true", "message" => $lang['must_select_start_time']);
            $validate_arr[] = array("input" => $_POST["adv_end_time"], "require" => "true", "message" => $lang['must_select_end_time']);
            if ($_POST["aptype_hidden"] == '1') {
                //文字广告
                $validate_arr[] = array("input" => $_POST["adv_word"], "require" => "true", "message" => $lang['textadv_null_error']);
            } elseif ($_POST["aptype_hidden"] == '3') {
                //flash广告
                $validate_arr[] = array("input" => $_FILES['flash_swf']['name'], "require" => "true", "message" => $lang['flashadv_null_error']);
            } else {
                //图片广告
                $validate_arr[] = array("input" => $_FILES['adv_pic']['name'], "require" => "true", "message" => $lang['picadv_null_error']);
            }
            $obj_validate->validateparam = $validate_arr;
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $insert_array['adv_title'] = trim($_POST['adv_name']);
                $insert_array['ap_id'] = intval($_POST['ap_id']);
                $insert_array['adv_start_date'] = $this->getunixtime($_POST['adv_start_time']);
                $insert_array['adv_end_date'] = $this->getunixtime($_POST['adv_end_time']);
                $insert_array['is_allow'] = '1';
                /**
                 * 建立文字广告信息的入库数组
                 */
                //判断页面编码确定汉字所占字节数
                switch (CHARSET) {
                    case 'UTF-8':
                        $charrate = 3;
                        break;
                    case 'GBK':
                        $charrate = 2;
                        break;
                }
                //图片广告
                if ($_POST['aptype_hidden'] == '0') {
                    $upload->set('default_dir', ATTACH_ADV);
                    $result = $upload->upfile('adv_pic');
                    if (!$result) {
                        error($upload->error, '', '', 'error');
                    }
                    $ac = array('adv_pic' => $upload->file_name, 'adv_pic_url' => trim($_POST['adv_pic_url']));
                    $ac = serialize($ac);
                    $insert_array['adv_content'] = $ac;
                }
                //文字广告
                if ($_POST['aptype_hidden'] == '1') {
                    if (strlen($_POST['adv_word']) > $_POST['adv_word_len'] * $charrate) {
                        $error = $lang['wordadv_toolong'];
                        error($error);
                        die;
                    }
                    $ac = array('adv_word' => trim($_POST['adv_word']), 'adv_word_url' => trim($_POST['adv_word_url']));
                    $ac = serialize($ac);
                    $insert_array['adv_content'] = $ac;
                }
                //建立Flash广告信息的入库数组
                if ($_POST['aptype_hidden'] == '3') {
                    $upload->set('default_dir', ATTACH_ADV);
                    $upload->upfile('flash_swf');
                    $ac = array('flash_swf' => $upload->file_name, 'flash_url' => trim($_POST['flash_url']));
                    $ac = serialize($ac);
                    $insert_array['adv_content'] = $ac;
                }
                //广告信息入库
                $result = $adv->adv_add($insert_array);
                //更新相应广告位所拥有的广告数量
                $condition['ap_id'] = intval($_POST['ap_id']);
                $ap_list = $adv->getApList($condition);
                $ap_list = $ap_list['0'];
                $adv_num = $ap_list['adv_num'];
                $param['ap_id'] = intval($_POST['ap_id']);
                $param['adv_num'] = $adv_num + 1;
                $result2 = $adv->ap_update($param);
                if ($result && $result2) {
                    $this->log(lang('adv_add_succ') . '[' . $_POST["adv_name"] . ']', null);
                    success($lang['adv_add_succ'], 'index.php?act=adv&op=adv&ap_id=' . $_POST['ap_id']);
                } else {
                    error($lang['adv_add_fail']);
                }
            }
        }
    }
    /**
     *
     * 管理广告位
     */
    public function ap_manageOp()
    {
        $lang = core\language::getLangContent();
        $adv = model('adv');
        /**
         * 多选删除广告位
         */
        if (chksubmit()) {
			$in_array_id = '';
            if (!empty($_POST['del_id'])) {
                $in_array_id = implode(',', $_POST['del_id']);
                $where = "where ap_id in (" . $in_array_id . ")";
                \db\mysqli::delete("adv_position", $where);
                foreach ($_POST['del_id'] as $v) {
                    $adv->delapcache($v);
                }
            }
            $this->log(lang('ap_del_succ') . '[ID:' . $in_array_id . ']', null);
            success($lang['ap_del_succ'], isset($_POST['ref_url']) ? $_POST['ref_url'] : '');
        }
        /**
         * 显示广告位管理界面
         */
        $condition = array();
        $orderby = '';
        if (!empty($_GET['search_name'])) {
            $condition['ap_name'] = trim($_GET['search_name']);
        }
        /**
         * 分页
         */
        $page = new lib\page();
        $page->setEachNum(25);
        $page->setStyle('admin');
        $ap_list = $adv->getApList($condition, $page, $orderby);
        $adv_list = $adv->getList();
        core\tpl::output('ap_list', $ap_list);
        core\tpl::output('adv_list', $adv_list);
        core\tpl::output('page', $page->show());
        core\tpl::showpage('ap_manage');
    }
    /**
     * js代码调用
     */
    public function ap_copyOp()
    {
        core\tpl::showpage('ap_copy', 'null_layout');
    }
    /**
     *
     * 修改广告位
     */
    public function ap_editOp()
    {
        if (!chksubmit()) {
            $adv = model('adv');
            $condition['ap_id'] = intval($_GET['ap_id']);
            $ap_list = $adv->getApList($condition);
            core\tpl::output('ref_url', getReferer());
            core\tpl::output('ap_list', $ap_list);
            core\tpl::showpage('ap_edit');
        } else {
            $lang = core\language::getLangContent();
            $adv = model('adv');
            $upload = new lib\uploadfile();
            $obj_validate = new lib\validate();
            if ($_POST['ap_class'] == '1') {
                $obj_validate->validateparam = array(array("input" => $_POST["ap_name"], "require" => "true", "message" => $lang['ap_can_not_null']), array("input" => $_POST["ap_width"], "require" => "true", 'validator' => 'Number', "message" => $lang['ap_width_must_num']));
            } else {
                $obj_validate->validateparam = array(array("input" => $_POST["ap_name"], "require" => "true", "message" => $lang['ap_can_not_null']), array("input" => $_POST["ap_width"], "require" => "true", 'validator' => 'Number', "message" => $lang['ap_width_must_num']), array("input" => $_POST["ap_height"], "require" => "true", 'validator' => 'Number', "message" => $lang['ap_height_must_num']));
            }
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $param['ap_id'] = intval($_GET['ap_id']);
                $param['ap_name'] = trim($_POST["ap_name"]);
                $param['ap_intro'] = trim($_POST["ap_intro"]);
                $param['ap_width'] = intval(trim($_POST["ap_width"]));
                $param['ap_height'] = intval(trim($_POST["ap_height"]));
                if (!empty($_POST["ap_display"])) {
                    $param['ap_display'] = intval($_POST["ap_display"]);
                }
                if (!empty($_POST["is_use"])) {
                    $param['is_use'] = intval($_POST["is_use"]);
                }
                if (!empty($_FILES['default_pic']['name'])) {
                    $upload->set('default_dir', ATTACH_ADV);
                    $result = $upload->upfile('default_pic');
                    if (!$result) {
                        error($upload->error, '', '', 'error');
                    }
                    $param['default_content'] = $upload->file_name;
                }
                if (!empty($_POST['default_word'])) {
                    $param['default_content'] = trim($_POST['default_word']);
                }
                $result = $adv->ap_update($param);
                if ($result) {
                    $this->log(lang('ap_change_succ') . '[' . $_POST["ap_name"] . ']', null);
                    success($lang['ap_change_succ'], isset($_POST['ref_url']) ? $_POST['ref_url'] : '');
                } else {
                    error($lang['ap_change_fail']);
                }
            }
        }
    }
    /**
     *
     * 新增广告位
     */
    public function ap_addOp()
    {
        if (!chksubmit()) {
            core\tpl::showpage('ap_add');
        } else {
            $lang = core\language::getLangContent();
            $adv = model('adv');
            $upload = new lib\uploadfile();
            $obj_validate = new lib\validate();
            if ($_POST['ap_class'] == '1') {
                $obj_validate->validateparam = array(array("input" => $_POST["ap_name"], "require" => "true", "message" => $lang['ap_can_not_null']), array("input" => $_POST["ap_width_word"], "require" => "true", 'validator' => 'Number', "message" => $lang['ap_wordwidth_must_num']), array("input" => $_POST["default_word"], "require" => "true", "message" => $lang['default_word_can_not_null']));
            } else {
                $obj_validate->validateparam = array(array("input" => $_POST["ap_name"], "require" => "true", "message" => $lang['ap_can_not_null']), array("input" => $_POST["ap_width_media"], "require" => "true", 'validator' => 'Number', "message" => $lang['ap_width_must_num']), array("input" => $_POST["ap_height"], "require" => "true", 'validator' => 'Number', "message" => $lang['ap_height_must_num']), array("input" => $_FILES["default_pic"], "require" => "true", "message" => $lang['default_pic_can_not_null']));
            }
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $insert_array['ap_name'] = trim($_POST['ap_name']);
                $insert_array['ap_intro'] = trim($_POST['ap_intro']);
                $insert_array['ap_class'] = intval($_POST['ap_class']);
                $insert_array['ap_display'] = intval($_POST['ap_display']);
                $insert_array['is_use'] = intval($_POST['is_use']);
                if (!empty($_POST['ap_width_media'])) {
                    $insert_array['ap_width'] = intval(trim($_POST['ap_width_media']));
                }
                if (!empty($_POST['ap_width_word'])) {
                    $insert_array['ap_width'] = intval(trim($_POST['ap_width_word']));
                }
                if (!empty($_POST['default_word'])) {
                    $insert_array['default_content'] = trim($_POST['default_word']);
                }
                if (!empty($_FILES['default_pic']['name'])) {
                    $upload->set('default_dir', ATTACH_ADV);
                    $result = $upload->upfile('default_pic');
                    if (!$result) {
                        error($upload->error, '', '', 'error');
                    }
                    $insert_array['default_content'] = $upload->file_name;
                }
                $insert_array['ap_height'] = intval(trim($_POST['ap_height']));
                $result = $adv->ap_add($insert_array);
                if ($result) {
                    $this->log(lang('ap_add_succ') . '[' . $_POST["ap_name"] . ']', null);
                    success($lang['ap_add_succ'], 'index.php?act=adv&op=ap_manage', 'html', 'succ', 1, 4000);
                } else {
                    error($lang['ap_add_fail']);
                }
            }
        }
    }
    /**
     *
     * 广告管理
     */
    public function advOp()
    {
        $lang = core\language::getLangContent();
        $adv = model('adv');
        if (chksubmit()) {
            if (!empty($_POST['del_id']) && is_array($_POST['del_id'])) {
                // 删除缓存
                model('adv')->dropApCacheByAdvIds($_POST['del_id']);
                $in_array_id = "'" . implode("','", $_POST['del_id']) . "'";
                $where = 'where adv_id in (' . $in_array_id . ')';
                \db\mysqli::delete("adv", $where);
            }
            $this->log(lang('adv_del_succ') . '[ID:' . $in_array_id . ']', null);
            success($lang['adv_del_succ'], getReferer());
        }
        /**
         * 分页
         */
        $page = new lib\page();
        $page->setEachNum(20);
        $page->setStyle('admin');
        $condition = array();
        $condition['is_allow'] = '1';
        $limit = '';
        $orderby = '';
        if ($_GET['ap_id'] != '') {
            $condition['ap_id'] = intval($_GET['ap_id']);
        }
        $adv_info = $adv->getList($condition, $page, $limit, $orderby);
        $ap_info = $adv->getApList();
        core\tpl::output('adv_info', $adv_info);
        core\tpl::output('ap_info', $ap_info);
        core\tpl::output('ap_name', model()->table('adv_position')->getfby_ap_id(intval($_GET['ap_id']), 'ap_name'));
        core\tpl::output('page', $page->show());
        core\tpl::showpage('adv.index');
    }
    /**
     *
     * 修改广告
     */
    public function adv_editOp()
    {
        if (!chksubmit()) {
            $adv = model('adv');
            $condition['adv_id'] = intval($_GET['adv_id']);
            $adv_list = $adv->getList($condition);
            $ap_info = $adv->getApList();
            core\tpl::output('ref_url', getReferer());
            core\tpl::output('adv_list', $adv_list);
            core\tpl::output('ap_info', $ap_info);
            core\tpl::showpage('adv.edit');
        } else {
            $lang = core\language::getLangContent();
            $adv = model('adv');
            $upload = new core\uploadfile();
            /**
             * 验证
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(array("input" => $_POST["adv_name"], "require" => "true", "message" => $lang['ap_can_not_null']), array("input" => $_POST["adv_start_date"], "require" => "true", "message" => $lang['must_select_start_time']), array("input" => $_POST["adv_end_date"], "require" => "true", "message" => $lang['must_select_end_time']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $param['adv_id'] = intval($_GET['adv_id']);
                $param['adv_title'] = trim($_POST['adv_name']);
                $param['adv_start_date'] = $this->getunixtime(trim($_POST['adv_start_date']));
                $param['adv_end_date'] = $this->getunixtime(trim($_POST['adv_end_date']));
                /**
                 * 建立图片广告信息的入库数组
                 */
                if ($_POST['mark'] == '0') {
                    if ($_FILES['adv_pic']['name'] != '') {
                        $upload->set('default_dir', ATTACH_ADV);
                        $result = $upload->upfile('adv_pic');
                        if (!$result) {
                            error($upload->error, '', '', 'error');
                        }
                        $ac = array('adv_pic' => $upload->file_name, 'adv_pic_url' => trim($_POST['adv_pic_url']));
                        $ac = serialize($ac);
                        $param['adv_content'] = $ac;
                    } else {
                        $ac = array('adv_pic' => trim($_POST['pic_ori']), 'adv_pic_url' => trim($_POST['adv_pic_url']));
                        $ac = serialize($ac);
                        $param['adv_content'] = $ac;
                    }
                }
                /**
                 * 建立文字广告信息的入库数组
                 */
                if ($_POST['mark'] == '1') {
                    //判断页面编码确定汉字所占字节数
                    switch (CHARSET) {
                        case 'UTF-8':
                            $charrate = 3;
                            break;
                        case 'GBK':
                            $charrate = 2;
                            break;
                    }
                    if (strlen($_POST['adv_word']) > $_POST['adv_word_len'] * $charrate) {
                        $error = $lang['wordadv_toolong'];
                        error($error);
                        die;
                    }
                    $ac = array('adv_word' => trim($_POST['adv_word']), 'adv_word_url' => trim($_POST['adv_word_url']));
                    $ac = serialize($ac);
                    $param['adv_content'] = $ac;
                }
                /**
                 * 建立Flash广告信息的入库数组
                 */
                if ($_POST['mark'] == '3') {
                    if ($_FILES['flash_swf']['name'] != '') {
                        $upload->set('default_dir', ATTACH_ADV);
                        $result = $upload->upfile('flash_swf');
                        $ac = array('flash_swf' => $upload->file_name, 'flash_url' => trim($_POST['flash_url']));
                        $ac = serialize($ac);
                        $param['adv_content'] = $ac;
                    } else {
                        $ac = array('flash_swf' => trim($_POST['flash_ori']), 'flash_url' => trim($_POST['flash_url']));
                        $ac = serialize($ac);
                        $param['adv_content'] = $ac;
                    }
                }
                $result = $adv->update($param);
                if ($result) {
                    $this->log(lang('adv_change_succ') . '[' . $_POST["ap_name"] . ']', null);
                    success($lang['adv_change_succ'], $_POST['ref_url']);
                } else {
                    error($lang['adv_change_fail']);
                }
            }
        }
    }
    /**
     *
     * 删除广告
     */
    public function adv_delOp()
    {
        $lang = core\language::getLangContent();
        $adv = model('adv');
        /**
         * 删除一个广告
         */
        $result = $adv->adv_del(intval($_GET['adv_id']));
        if (!$result) {
            error($lang['adv_del_fail']);
            die;
        } else {
            $this->log(lang('adv_del_succ') . '[' . intval($_GET['adv_id']) . ']', null);
            success($lang['adv_del_succ']);
            die;
        }
        /**
         * 多选删除多个广告
         */
        if (chksubmit()) {
            if ($_POST['del_id'] != '') {
                foreach ($_POST['del_id'] as $k => $v) {
                    $v = intval($v);
                    $adv->adv_del($v);
                }
                $url = 'index.php?act=adv&op=adv';
                success($lang['adv_del_succ'], $url);
            }
        }
    }
    /**
     *
     * 获取UNIX时间戳
     */
    public function getunixtime($time)
    {
        $array = explode("-", $time);
        $unix_time = mktime(0, 0, 0, $array[1], $array[2], $array[0]);
        return $unix_time;
    }
    /**
     *
     * ajaxOp
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            case 'is_use':
                $adv = model('adv');
                $param[trim($_GET['column'])] = intval($_GET['value']);
                $param['ap_id'] = intval($_GET['id']);
                $adv->ap_update($param);
                echo 'true';
                exit;
			break;
        }
    }
}