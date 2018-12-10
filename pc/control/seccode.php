<?php
/**
 * 验证码
 **/
namespace pc\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class seccode
{
    public function __construct()
    {
    }
    /**
     * 产生验证码
     *
     */
    public function makecodeOp()
    {
        $seccode = makeSeccode($_GET['nchash']);
        header("Expires: -1");
        header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
        header("Pragma: no-cache");
        $code = new lib\seccode();
        $code->code = $seccode;
        $code->width = 120;
        $code->height = 50;
        $code->background = 1;
        $code->adulterate = 1;
        $code->scatter = '';
        $code->color = 1;
        $code->size = 0;
        $code->shadow = 1;
        $code->animator = 0;
        $code->datapath = BASE_DATA_PATH . '/resource/seccode/';
        $code->display();
    }
    /**
     * AJAX验证
     *
     */
    public function checkOp()
    {
        if (checkSeccode($_GET['nchash'], $_GET['captcha'])) {
            core\session::set('captcha_num', $_GET['captcha']);
            exit('true');
        } else {
            exit('false');
        }
    }
}