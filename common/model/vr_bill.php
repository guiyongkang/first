<?php
/**
 * 结算模型
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class vr_bill extends core\model
{
    /**
     * 取得平台月结算单
     * @param unknown $condition
     * @param unknown $fields
     * @param unknown $pagesize
     * @param unknown $order
     * @param unknown $limit
     */
    public function getOrderStatisList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null)
    {
        return $this->table('vr_order_statis')->where($condition)->field($fields)->order($order)->page($pagesize)->limit($limit)->select();
    }
    /**
     * 取得平台月结算单条信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getOrderStatisInfo($condition = array(), $fields = '*', $order = null)
    {
        return $this->table('vr_order_statis')->where($condition)->field($fields)->order($order)->find();
    }
    /**
     * 取得店铺月结算单列表
     * @param unknown $condition
     * @param string $fields
     * @param string $pagesize
     * @param string $order
     * @param string $limit
     */
    public function getOrderBillList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null)
    {
        return $this->table('vr_order_bill')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }
    /**
     * 取得店铺月结算单单条
     * @param unknown $condition
     * @param string $fields
     */
    public function getOrderBillInfo($condition = array(), $fields = '*')
    {
        return $this->table('vr_order_bill')->where($condition)->field($fields)->find();
    }
    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderBillCount($condition)
    {
        return $this->table('vr_order_bill')->where($condition)->count();
    }
    public function addOrderStatis($data)
    {
        return $this->table('vr_order_statis')->insert($data);
    }
    public function addOrderBill($data)
    {
        return $this->table('vr_order_bill')->insert($data);
    }
    public function editOrderBill($data, $condition = array())
    {
        return $this->table('vr_order_bill')->where($condition)->update($data);
    }
}