<?php
/**
 * 验证码
 */
namespace mobile\control;

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
    public function makecodekeyOp()
    {
        output_data(array('codekey' => getNchash()));
    }
    /**
     * 产生验证码
     *
     */
    public function makecodeOp()
    {
        $seccode = makeSeccode($_GET['k']);
        header("Expires: -1");
        header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
        header("Pragma: no-cache");
        $code = new lib\seccode();
        $code->code = $seccode;
        $code->width = 120;
        $code->height = 50;
        $code->background = 2;
        $code->adulterate = 30;
        $code->scatter = 0;
        $code->color = 5;
        $code->size = 2;
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
            exit('true');
        } else {
            exit('false');
        }
    }
}