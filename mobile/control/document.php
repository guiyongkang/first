<?php
/**
 * 前台品牌分类
 *
*/
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class document extends mobileHomeControl {
    public function __construct() {
        parent::__construct();
    }

    public function agreementOp() {
        $doc = model('document')->getOneByCode('agreement');
        output_data($doc);
    }
}
