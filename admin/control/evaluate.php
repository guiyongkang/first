<?php
/**
 * 商品评价
 *
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class evaluate extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('evaluate');
    }
    public function indexOp()
    {
        $this->evalgoods_listOp();
    }
    /**
     * 商品来自买家的评价列表
     */
    public function evalgoods_listOp()
    {
        $model_evaluate_goods = model('evaluate_goods');
        $condition = array();
        //商品名称
        if (!empty($_GET['goods_name'])) {
            $condition['geval_goodsname'] = array('like', '%' . $_GET['goods_name'] . '%');
        }
        //店铺名称
        if (!empty($_GET['store_name'])) {
            $condition['geval_storename'] = array('like', '%' . $_GET['store_name'] . '%');
        }
		if (!empty($_GET['stime']) || !empty($_GET['etime'])) {
            $condition['geval_addtime'] = array('time', array(strtotime($_GET['stime']), strtotime($_GET['etime'])));
        }
        $evalgoods_list = $model_evaluate_goods->getEvaluateGoodsList($condition, 10);
        core\tpl::output('show_page', $model_evaluate_goods->showpage());
        core\tpl::output('evalgoods_list', $evalgoods_list);
        core\tpl::showpage('evalgoods.index');
    }
    /**
     * 删除商品评价
     */
    public function evalgoods_delOp()
    {
        $geval_id = intval($_POST['geval_id']);
        if ($geval_id <= 0) {
            error(core\language::get('param_error'));
        }
        $model_evaluate_goods = model('evaluate_goods');
        $result = $model_evaluate_goods->delEvaluateGoods(array('geval_id' => $geval_id));
        if ($result) {
            $this->log('删除商品评价，评价编号' . $geval_id);
            success(core\language::get('nc_common_del_succ'));
        } else {
            error(core\language::get('nc_common_del_fail'));
        }
    }
    /**
     * 店铺动态评价列表
     */
    public function evalstore_listOp()
    {
        $model_evaluate_store = model('evaluate_store');
        $condition = array();
        //评价人
        if (!empty($_GET['from_name'])) {
            $condition['seval_membername'] = array('like', '%' . $_GET['from_name'] . '%');
        }
        //店铺名称
        if (!empty($_GET['store_name'])) {
            $condition['seval_storename'] = array('like', '%' . $_GET['store_name'] . '%');
        }
        if (!empty($_GET['stime']) || !empty($_GET['etime'])) {
            $condition['seval_addtime_gt'] = array('time', array(strtotime($_GET['stime']), strtotime($_GET['etime'])));
        }
        $evalstore_list = $model_evaluate_store->getEvaluateStoreList($condition, 10);
        core\tpl::output('show_page', $model_evaluate_store->showpage());
        core\tpl::output('evalstore_list', $evalstore_list);
        core\tpl::showpage('evalstore.index');
    }
    /**
     * 删除店铺评价
     */
    public function evalstore_delOp()
    {
        $seval_id = intval($_POST['seval_id']);
        if ($seval_id <= 0) {
            error(core\language::get('param_error'));
        }
        $model_evaluate_store = model('evaluate_store');
        $result = $model_evaluate_store->delEvaluateStore(array('seval_id' => $seval_id));
        if ($result) {
            $this->log('删除店铺评价，评价编号' . $geval_id);
            success(core\language::get('nc_common_del_succ'));
        } else {
            error(core\language::get('nc_common_del_fail'));
        }
    }
}