<?php
/*******
 * 圈子话题管理 
 *
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class circle_cache extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function indexOp()
    {
        rcache('circle_level', true);
        success(lang('nc_common_op_succ'), 'index.php?act=circle_setting');
    }
}