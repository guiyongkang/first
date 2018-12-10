<?php
/**
 * 店铺开店
 **/
namespace pc\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class show_joinin extends BaseHomeControl
{
    public function __construct()
    {
		parent::__construct();
    }
    /**
     * 店铺开店页
     *
     */
    public function indexOp()
    {
        core\language::read('home_login_index');
        $code_info = core\config::get('store_joinin_pic');
        $info['pic'] = array();
        if (!empty($code_info)) {
            $info = unserialize($code_info);
        }
        core\tpl::output('pic_list', $info['pic']);
        //首页图片
        core\tpl::output('show_txt', $info['show_txt']);
        //贴心提示
        $model_help = model('help');
        $condition['type_id'] = '1';
        //入驻指南
        $help_list = $model_help->getHelpList($condition, '', 4);
        //显示4个
        core\tpl::output('help_list', $help_list);
        core\tpl::output('article_list', '');
        //底部不显示文章分类
        core\tpl::output('show_sign', 'joinin');
        core\tpl::output('html_title', core\config::get('site_name') . ' - ' . '商家入驻');
        core\tpl::setLayout('store_joinin_layout');
        core\tpl::showpage('store_joinin');
    }
}