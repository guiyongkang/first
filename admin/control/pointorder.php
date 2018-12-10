<?php
/**
 * 积分兑换订单管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class pointorder extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('pointorder');
    }
    /**
     * 积分兑换列表
     */
    public function pointorder_listOp()
    {
        $model_pointorder = model('pointorder');
        //获取兑换订单状态
        $pointorderstate_arr = $model_pointorder->getPointOrderStateBySign();
        $where = array();
        //兑换单号
        $pordersn = isset($_GET['pordersn']) ? trim($_GET['pordersn']) : '';
        if ($pordersn) {
            $where['point_ordersn'] = array('like', '%' . $pordersn . '%');
        }
        //兑换会员名称
        $pbuyname = isset($_GET['pbuyname']) ? trim($_GET['pbuyname']) : '';
        if ($pbuyname) {
            $where['point_buyername'] = array('like', '%' . $pbuyname . '%');
        }
        //订单状态
		$porderstate = isset($_GET['porderstate']) ? trim($_GET['porderstate']) : '';
        if ($porderstate) {
            $where['point_orderstate'] = $pointorderstate_arr[$_GET['porderstate']][0];
        }
        //查询兑换订单列表
        $order_list = $model_pointorder->getPointOrderList($where, '*', 10, 0, 'point_orderid desc');
        //信息输出
        core\tpl::output('pointorderstate_arr', $pointorderstate_arr);
        core\tpl::output('order_list', $order_list);
        core\tpl::output('show_page', $model_pointorder->showpage());
        core\tpl::showpage('pointorder.list');
    }
    /**
     * 删除兑换订单信息
     */
    public function order_dropOp()
    {
        $data = model('pointorder')->delPointOrderByOrderID($_GET['order_id']);
        if ($data['state']) {
            showDialog(lang('admin_pointorder_del_success'), 'index.php?act=pointorder&op=pointorder_list', 'succ');
        } else {
            showDialog($data['msg'], 'index.php?act=pointorder&op=pointorder_list', 'error');
        }
    }
    /**
     * 取消兑换
     */
    public function order_cancelOp()
    {
        $model_pointorder = model('pointorder');
        //取消订单
        $data = $model_pointorder->cancelPointOrder($_GET['id']);
        if ($data['state']) {
            showDialog(lang('admin_pointorder_cancel_success'), 'index.php?act=pointorder&op=pointorder_list', 'succ');
        } else {
            showDialog($data['msg'], 'index.php?act=pointorder&op=pointorder_list', 'error');
        }
    }
    /**
     * 发货
     */
    public function order_shipOp()
    {
        $order_id = intval($_GET['id']);
        if ($order_id <= 0) {
            showDialog(lang('admin_pointorder_parameter_error'), 'index.php?act=pointorder&op=pointorder_list', 'error');
        }
        $model_pointorder = model('pointorder');
        //获取订单状态
        $pointorderstate_arr = $model_pointorder->getPointOrderStateBySign();
        //查询订单信息
        $where = array();
        $where['point_orderid'] = $order_id;
        $where['point_orderstate'] = array('in', array($pointorderstate_arr['waitship'][0], $pointorderstate_arr['waitreceiving'][0]));
        //待发货和已经发货状态
        $order_info = $model_pointorder->getPointOrderInfo($where);
        if (!$order_info) {
            showDialog(lang('admin_pointorderd_record_error'), 'index.php?act=pointorder&op=pointorder_list', 'error');
        }
        if (chksubmit()) {
            $obj_validate = new lib\validate();
            $validate_arr[] = array("input" => $_POST["shippingcode"], "require" => "true", "message" => lang('admin_pointorder_ship_code_nullerror'));
            $obj_validate->validateparam = $validate_arr;
            $error = $obj_validate->validate();
            if ($error != '') {
                showDialog(lang('error') . $error, 'index.php?act=pointorder&op=pointorder_list', 'error');
            }
            //发货
            $data = $model_pointorder->shippingPointOrder($order_id, $_POST, $order_info);
            if ($data['state']) {
                showDialog('发货修改成功', 'index.php?act=pointorder&op=pointorder_list', 'succ');
            } else {
                showDialog($data['msg'], 'index.php?act=pointorder&op=pointorder_list', 'error');
            }
        } else {
            $express_list = model('express')->getExpressList();
            core\tpl::output('express_list', $express_list);
            core\tpl::output('order_info', $order_info);
            core\tpl::showpage('pointorder.ship');
        }
    }
    /**
     * 兑换信息详细
     */
    public function order_infoOp()
    {
        $order_id = intval($_GET['order_id']);
        if ($order_id <= 0) {
            showDialog(lang('admin_pointorder_parameter_error'), 'index.php?act=pointorder&op=pointorder_list', 'error');
        }
        //查询订单信息
        $model_pointorder = model('pointorder');
        $order_info = $model_pointorder->getPointOrderInfo(array('point_orderid' => $order_id));
        if (!$order_info) {
            showDialog(lang('admin_pointorderd_record_error'), 'index.php?act=pointorder&op=pointorder_list', 'error');
        }
        $orderstate_arr = $model_pointorder->getPointOrderState($order_info['point_orderstate']);
        $order_info['point_orderstatetext'] = $orderstate_arr[1];
        //查询兑换订单收货人地址
        $orderaddress_info = $model_pointorder->getPointOrderAddressInfo(array('point_orderid' => $order_id));
        core\tpl::output('orderaddress_info', $orderaddress_info);
        //兑换商品信息
        $prod_list = $model_pointorder->getPointOrderGoodsList(array('point_orderid' => $order_id));
        core\tpl::output('prod_list', $prod_list);
        //物流公司信息
        if ($order_info['point_shipping_ecode'] != '') {
            $data = model('express')->getExpressInfoByECode($order_info['point_shipping_ecode']);
            if ($data['state']) {
                $express_info = $data['data']['express_info'];
            }
            core\tpl::output('express_info', $express_info);
        }
        core\tpl::output('order_info', $order_info);
        core\tpl::showpage('pointorder.info');
    }
}