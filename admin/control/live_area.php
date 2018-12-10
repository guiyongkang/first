<?php
/**
 *
 *	城市管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class live_areaControl extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('live');
    }
    public function indexOp()
    {
        $this->live_areaOp();
    }
    /*
     * 区域列表
     */
    public function live_areaOp()
    {
        $condition = array();
        //搜索条件
        $condition['parent_area_id'] = 0;
        if (isset($_GET['live_area_name']) && !empty($_GET['live_area_name'])) {
            $condition['live_area_name'] = array('like', "%{$_GET['live_area_name']}%");
            core\tpl::output('live_area_name', $_GET['live_area_name']);
        }
        if (isset($_GET['first_letter']) && !empty($_GET['first_letter'])) {
            $condition['first_letter'] = $_GET['first_letter'];
            core\tpl::output('first_letter', $_GET['first_letter']);
        }
        $model_live_area = model('live_area');
        $area = $model_live_area->getList($condition);
        core\tpl::output('list', $area);
        //区域列表
        core\tpl::output('show_page', $model_live_area->showpage());
        //城市首字母
        $letterArr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        core\tpl::output('letter', $letterArr);
        core\tpl::showpage("livearea.list");
    }
    /*
     * 添加区域
     */
    public function area_addOp()
    {
        if (isset($_POST) && !empty($_POST)) {
            //数据验证
            $obj_validate = new lib\validate();
            $validate_array = array(array('input' => $_POST['live_area_name'], 'require' => 'true', 'message' => '区域名称不能为空'), array('input' => $_POST['first_letter'], 'require' => 'true', 'message' => '首字母不能为空'));
            $obj_validate->validateparam = $validate_array;
            $error = $obj_validate->validate();
            if ($error != '') {
                showMessage(core\language::get('error') . $error, '', '', 'error');
            }
            $params = array('live_area_name' => trim($_POST['live_area_name']), 'parent_area_id' => isset($_POST['parent_area_id']) && !empty($_POST['parent_area_id']) ? $_POST['parent_area_id'] : 0, 'add_time' => time(), 'first_letter' => $_POST['first_letter'], 'area_number' => trim($_POST['area_number']), 'post' => trim($_POST['post']), 'hot_city' => intval($_POST['is_hot']));
            $model_live_area = model('live_area');
            $res = $model_live_area->add($params);
            if ($res) {
                $this->log('添加线下抢区域[ID:' . $res . ']', 1);
                showMessage('添加成功', 'index.php?act=live_area', '', 'succ');
            } else {
                showMessage('添加失败', 'index.php?act=live_area', '', 'error');
            }
        }
        //城市首字母
        $letterArr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        core\tpl::output('letter', $letterArr);
        if (isset($_GET['live_area_id'])) {
            $model_live_area = model('live_area');
            $area = $model_live_area->live_areaInfo(array('live_area_id' => intval($_GET['live_area_id'])));
            core\tpl::output('live_area_name', $area['live_area_name']);
            core\tpl::output('live_area_id', $area['live_area_id']);
        } else {
            core\tpl::output('live_area_name', core\language::get('live_area_first_area'));
            core\tpl::output('live_area_id', 0);
        }
        core\tpl::showpage("livearea.add");
    }
    /*
     * 编辑区域
     */
    public function area_editOp()
    {
        if (isset($_POST) && !empty($_POST)) {
            //数据验证
            $obj_validate = new lib\validate();
            $validate_array = array(array('input' => $_POST['live_area_name'], 'require' => 'true', 'message' => '区域名称不能为空'), array('input' => $_POST['first_letter'], 'require' => 'true', 'message' => '首字母不能为空'));
            $obj_validate->validateparam = $validate_array;
            $error = $obj_validate->validate();
            if ($error != '') {
                showMessage(core\language::get('error') . $error, '', '', 'error');
            }
            $params = array('live_area_name' => trim($_POST['live_area_name']), 'add_time' => time(), 'first_letter' => $_POST['first_letter'], 'area_number' => trim($_POST['area_number']), 'post' => trim($_POST['post']), 'hot_city' => intval($_POST['is_hot']));
            $condition = array();
            $condition['live_area_id'] = intval($_POST['live_area_id']);
            $model_live_area = model('live_area');
            $res = $model_live_area->edit($condition, $params);
            if ($res) {
                H('city', null);
                //清除缓存
                delCacheFile('city');
                $this->log('编辑线下抢区域[ID:' . intval($_POST['live_area_id']) . ']', 1);
                showMessage('编辑成功', 'index.php?act=live_area', '', 'succ');
            } else {
                showMessage('编辑失败', 'index.php?act=live_area', '', 'error');
            }
        }
        //城市首字母
        $letterArr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'W', 'X', 'Y', 'Z');
        core\tpl::output('letter', $letterArr);
        $model_area = model('live_area');
        $model = model();
        $area = $model->table('live_area')->where(array('live_area_id' => intval($_GET['live_area_id'])))->find();
        core\tpl::output('area', $area);
        $parent_area = $model->table('live_area')->where(array('live_area_id' => $area['parent_area_id']))->find();
        if (!empty($parent_area)) {
            core\tpl::output('parent_area_name', $parent_area['live_area_name']);
        } else {
            core\tpl::output('parent_area_name', core\language::get('live_area_first_area'));
        }
        core\tpl::showpage("livearea.edit");
    }
    /*
     *  查看区域
     */
    public function view_areaOp()
    {
        //获取区域信息
        $model = model();
        $area_list = $model->table('live_area')->where(array('parent_area_id' => intval($_GET['parent_area_id'])))->select();
        core\tpl::output('show_page', $model->showpage());
        core\tpl::output('list', $area_list);
        $area = $model->table('live_area')->where(array('live_area_id' => intval($_GET['parent_area_id'])))->find();
        //print_r($area);exit;
        core\tpl::output('parent_area', $area);
        core\tpl::showpage("livedistrict.list");
    }
    /*
     * 查看商区
     */
    public function view_mall_streetOp()
    {
        //获取区域信息
        $model = model();
        $mall_list = $model->table('live_area')->where(array('parent_area_id' => intval($_GET['parent_area_id'])))->select();
        core\tpl::output('show_page', $model->showpage());
        core\tpl::output('list', $mall_list);
        $mall = $model->table('live_area')->where(array('live_area_id' => intval($_GET['parent_area_id'])))->find();
        core\tpl::output('parent_area', $mall);
        core\tpl::showpage("livemall.list");
    }
    /*
     * 删除区域
     */
    public function area_dropOp()
    {
        $model = model();
        $res = $model->table('live_area')->where(array('live_area_id' => array('in', intval($_POST['live_area_id']))))->delete();
        if ($res) {
            H('city', null);
            //清除缓存
            delCacheFile('city');
            $this->log('删除线下抢区域[ID:' . intval($_POST['live_area_id']) . ']', 1);
            showMessage('删除成功', 'index.php?act=live_area', '', 'succ');
        } else {
            showMessage('删除失败', 'index.php?act=live_area', '', 'error');
        }
    }
}