<?php
/**
 * 网站设置
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class perform extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('setting');
    }
    /**
     * 性能优化
     */
    public function performOp()
    {
        if (isset($_GET['type']) && $_GET['type'] == 'clear') {
            $lang = core\language::getLangContent();
            $cache = core\cache::connect(core\config::get('cache'));
            $cache->clear();
            success($lang['nc_common_op_succ']);
        }
        core\tpl::showpage('setting.perform_opt');
    }
}