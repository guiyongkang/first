<?php
/**
 * 店铺帮助
 **/
namespace pc\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class show_help extends BaseHomeControl
{
    public function __construct()
    {
        parent::__construct();
        core\tpl::output('show_sign', 'help');
    }
    /**
     * 店铺帮助页
     *
     */
    public function indexOp()
    {
        $model_help = model('help');
        $list = $model_help->getShowStoreHelpList();
        $type_id = isset($_GET['t_id']) ? intval($_GET['t_id']) : 0;
        //帮助类型编号
        if ($type_id < 1 || empty($list[$type_id])) {
            $type_array = current($list);
            $type_id = $type_array['type_id'];
        }
        core\tpl::output('type_id', $type_id);
        $help_id = isset($_GET['help_id']) ? intval($_GET['help_id']) : 0;
        //帮助编号
        if ($help_id < 1 || empty($list[$type_id]['help_list'][$help_id])) {
            $help_array = current($list[$type_id]['help_list']);
            $help_id = $help_array['help_id'];
        }
        core\tpl::output('help_id', $help_id);
        $help = $list[$type_id]['help_list'][$help_id];
        core\tpl::output('list', $list);
        //左侧帮助类型及帮助
        core\tpl::output('help', $help);
        //当前帮助
        core\tpl::output('article_list', '');
        //底部不显示首页的文章分类
        $phone_array = explode(',', core\config::get('site_phone'));
        core\tpl::output('phone_array', $phone_array);
        core\tpl::output('html_title', core\config::get('site_name') . ' - ' . '商家帮助指南');
        core\tpl::setLayout('store_joinin_layout');
        core\tpl::showpage('store_help');
    }
}