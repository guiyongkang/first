<?php
namespace admin\control;

use core;
defined('SAFE_CONST') or exit('Access Invalid!');
class index extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('index');
    }
    public function indexOp()
    {
        //输出管理员信息
        core\tpl::output('admin_info', $this->getAdminInfo());
        //输出菜单
        $this->getNav('', $top_nav, $left_nav, $map_nav);
        core\tpl::output('top_nav', $top_nav);
        core\tpl::output('left_nav', $left_nav);
        core\tpl::output('map_nav', $map_nav);
        core\tpl::showpage('index', 'index_layout');
    }
    /**
     * 退出
     */
    public function logoutOp()
    {
        setNcCookie('sys_key', '', -1, '', null);
        @header("Location: index.php");
        exit;
    }
    /**
     * 修改密码
     */
    public function modifypwOp()
    {
        if (chksubmit()) {
            if (trim($_POST['new_pw']) !== trim($_POST['new_pw2'])) {
                error(lang('index_modifypw_repeat_error'));
            }
            $admininfo = $this->getAdminInfo();
            //查询管理员信息
            $admin_model = model('admin');
            $admininfo = $admin_model->getOneAdmin($admininfo['id']);
            if (!is_array($admininfo) || count($admininfo) <= 0) {
                error(lang('index_modifypw_admin_error'));
            }
            //旧密码是否正确
            if ($admininfo['admin_password'] != md5(trim($_POST['old_pw']))) {
                error(lang('index_modifypw_oldpw_error'));
            }
            $new_pw = md5(trim($_POST['new_pw']));
            $result = $admin_model->updateAdmin(array('admin_password' => $new_pw, 'admin_id' => $admininfo['admin_id']));
            if ($result) {
                error(lang('index_modifypw_success'));
            } else {
                error(lang('index_modifypw_fail'));
            }
        } else {
            core\language::read('admin');
            core\tpl::showpage('admin.modifypw');
        }
    }
    /**
     * json输出地址数组 原data/resource/js/area_array.js
     */
    public function json_areaOp()
    {
        echo $_GET['callback'] . '(' . json_encode(model('area')->getAreaArrayForJson()) . ')';
    }
    //json输出商品分类
    public function josn_classOp()
    {
        /**
         * 实例化商品分类模型
         */
        $model_class = model('goods_class');
        $goods_class = $model_class->getGoodsClassListByParentId(intval($_GET['gc_id']));
        $array = array();
        if (!empty($goods_class) and is_array($goods_class)) {
            foreach ($goods_class as $val) {
                $array[$val['gc_id']] = array('gc_id' => $val['gc_id'], 'gc_name' => htmlspecialchars($val['gc_name']), 'gc_parent_id' => $val['gc_parent_id'], 'commis_rate' => $val['commis_rate'], 'gc_sort' => $val['gc_sort']);
            }
        }
        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK') {
            $array = core\language::getUTF8(array_values($array));
            //网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        } else {
            $array = array_values($array);
        }
        echo $_GET['callback'] . '(' . json_encode($array) . ')';
    }
}