<?php
/**
 * 合作伙伴管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class mb_feedback extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('mobile');
    }
    /**
     * 意见反馈
     */
    public function flistOp()
    {
        $model_mb_feedback = Model('mb_feedback');
        $list = $model_mb_feedback->getMbFeedbackList(array(), 10);
        core\tpl::output('list', $list);
        core\tpl::output('page', $model_mb_feedback->showpage());
        core\tpl::showpage('mb_feedback.index');
    }
    /**
     * 删除
     */
    public function delOp()
    {
        $model_mb_feedback = Model('mb_feedback');
        $result = $model_mb_feedback->delMbFeedback($_POST['feedback_id']);
        if ($result) {
            success(lang('nc_common_op_succ'));
        } else {
            error(lang('nc_common_op_fail'));
        }
    }
}