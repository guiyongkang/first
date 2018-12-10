<?php
/**
 * 评价行为
 *
 */
namespace common\logic;

use core;
defined('SAFE_CONST') or exit('Access Invalid!');
class member_evaluate
{
    public function evaluateListDity($goods_eval_list)
    {
        foreach ($goods_eval_list as $key => $value) {
            $goods_eval_list[$key]['member_avatar'] = getMemberAvatarForID($value['geval_frommemberid']);
        }
        return $goods_eval_list;
    }
    /**
     * 虚拟评价 33HA O .
     * COM V 4 .2
     */
    public function validationVr($order_id, $member_id)
    {
        if (!$order_id) {
            return callback(false, '参数错误');
        }
        $model_order = model('vr_order');
        $model_store = model('store');
        // 获取订单信息
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id));
        // 判断订单身份
        if ($order_info['buyer_id'] != $member_id) {
            return callback(false, '参数错误');
        }
        // 订单为'已收货'状态，并且未评论
        $order_info['evaluate_able'] = $model_order->getOrderOperateState('evaluation', $order_info);
        if (!$order_info['evaluate_able']) {
            return callback(false, '订单信息错误');
        }
        // 查询店铺信息
        $store_info = $model_store->getStoreInfoByID($order_info['store_id']);
        if (empty($store_info)) {
            return callback(false, '店铺信息错误');
        }
        $order_info['goods_image_url'] = cthumb($order_info['goods_image'], 60, $order_info['store_id']);
        $date = array('order_info' => $order_info, 'store_info' => $store_info);
        return callback(true, '', $date);
    }
    public function saveVr($date, $order_info, $store_info, $member_id, $member_name)
    {
        // 如果未评分，默认为5分
        $evaluate_score = intval($date['goods'][$order_info['goods_id']]['score']);
        if ($evaluate_score <= 0 || $evaluate_score > 5) {
            $evaluate_score = 5;
        }
        // 默认评语
        $evaluate_comment = $date['goods'][$order_info['goods_id']]['comment'];
        if (empty($evaluate_comment)) {
            $evaluate_comment = '不错哦';
        }
        $evaluate_goods_info = array();
        $evaluate_goods_info['geval_orderid'] = $order_info['order_id'];
        $evaluate_goods_info['geval_orderno'] = $order_info['order_sn'];
        $evaluate_goods_info['geval_ordergoodsid'] = $order_info['order_id'];
        $evaluate_goods_info['geval_goodsid'] = $order_info['goods_id'];
        $evaluate_goods_info['geval_goodsname'] = $order_info['goods_name'];
        $evaluate_goods_info['geval_goodsprice'] = $order_info['goods_price'];
        $evaluate_goods_info['geval_goodsimage'] = $order_info['goods_image'];
        $evaluate_goods_info['geval_scores'] = $evaluate_score;
        $evaluate_goods_info['geval_content'] = $evaluate_comment;
        $evaluate_goods_info['geval_isanonymous'] = $date['goods'][$order_info['goods_id']]['anony'] ? 1 : 0;
        $evaluate_goods_info['geval_addtime'] = TIMESTAMP;
        $evaluate_goods_info['geval_storeid'] = $store_info['store_id'];
        $evaluate_goods_info['geval_storename'] = $store_info['store_name'];
        $evaluate_goods_info['geval_frommemberid'] = $member_id;
        $evaluate_goods_info['geval_frommembername'] = $member_name;
        $evaluate_goods_array[] = $evaluate_goods_info;
        $goodsid_array[] = $order_info['goods_id'];
        model('evaluate_goods')->addEvaluateGoodsArray($evaluate_goods_array, $goodsid_array);
        // 更新订单信息并记录订单日志
        $model_order = model('vr_order');
        $state = $model_order->editOrder(array('evaluation_state' => 1, 'evaluation_time' => TIMESTAMP), array('order_id' => $order_info['order_id']));
        // 添加会员元
        if (core\config::get('points_isuse') == 1) {
            $points_model = model('points');
            $points_model->savePointsLog('comments', array('pl_memberid' => $member_id, 'pl_membername' => $member_name));
        }
        // 添加会员经验值
        model('exppoints')->saveExppointsLog('comments', array('exp_memberid' => $member_id, 'exp_membername' => $member_name));
        return callback(true);
    }
}