<?php
/**
 * 网站设置
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class search extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('setting');
    }
    /**
     * 搜索设置
     */
    public function searchOp()
    {
        if (chksubmit()) {
            $lang = core\language::getLangContent();
            $model_setting = model('setting');
            /**
             * 转码  防止GBK下用中文逗号截取不正确
             */
            $comma = '，';
            if (strtoupper(CHARSET) == 'GBK') {
                $comma = core\language::getGBK($comma);
            }
            $result = $model_setting->updateSetting(array('hot_search' => str_replace($comma, ',', $_POST['hot_search'])));
            if ($result) {
                success($lang['nc_common_save_succ']);
            } else {
                error($lang['nc_common_save_fail']);
            }
        }
        $model_setting = model('setting');
        $list_setting = $model_setting->getListSetting();
        core\tpl::output('list_setting', $list_setting);
        core\tpl::showpage('setting.search');
    }
}