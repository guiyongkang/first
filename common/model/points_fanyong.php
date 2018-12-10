<?php
/**
 * 订单管理
 *
 */
namespace common\model;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class points_fanyong extends core\model
{
    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return unknown
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $order = '', $group = '')
    {
        $order_info = $this->table('order')->field($fields)->where($condition)->group($group)->order($order)->find();
        if (empty($order_info)) {
            return array();
        }
        if (isset($order_info['order_state'])) {
            $order_info['state_desc'] = orderState($order_info);
        }
        if (isset($order_info['payment_code'])) {
            $order_info['payment_name'] = orderPaymentName($order_info['payment_code']);
        }
        //追加返回订单扩展表信息
        if (in_array('order_common', $extend)) {
            $order_info['extend_order_common'] = $this->getOrderCommonInfo(array('order_id' => $order_info['order_id']));
            $order_info['extend_order_common']['reciver_info'] = unserialize($order_info['extend_order_common']['reciver_info']);
            $order_info['extend_order_common']['invoice_info'] = unserialize($order_info['extend_order_common']['invoice_info']);
        }
        //追加返回店铺信息
        if (in_array('store', $extend)) {
            $order_info['extend_store'] = model('store')->getStoreInfo(array('store_id' => $order_info['store_id']));
        }
        //返回买家信息
        if (in_array('member', $extend)) {
            $order_info['extend_member'] = model('member')->getMemberInfoByID($order_info['buyer_id']);
        }
        //追加返回商品信息
        if (in_array('order_goods', $extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id' => $order_info['order_id']));
            $order_info['extend_order_goods'] = $order_goods_list;
        }
        return $order_info;
    }
    public function getOrderCommonInfo($condition = array(), $field = '*')
    {
        return $this->table('order_common')->where($condition)->find();
    }
    public function getOrderPayInfo($condition = array(), $master = false)
    {
        return $this->table('order_pay')->where($condition)->master($master)->find();
    }
    /**
     * 取得支付单列表
     *
     * @param unknown_type $condition
     * @param unknown_type $pagesize
     * @param unknown_type $filed
     * @param unknown_type $order
     * @param string $key 以哪个字段作为下标,这里一般指pay_id
     * @return unknown
     */
    public function getOrderPayList($condition, $pagesize = '', $filed = '*', $order = '', $key = '')
    {
        return $this->table('order_pay')->field($filed)->where($condition)->order($order)->page($pagesize)->key($key)->select();
    }
    /**
     * 取得店铺订单列表
     *
     * @param int $store_id 店铺编号
     * @param string $order_sn 订单sn
     * @param string $buyer_name 买家名称
     * @param string $state_type 订单状态
     * @param string $query_start_date 搜索订单起始时间
     * @param string $query_end_date 搜索订单结束时间
     * @param string $skip_off 跳过已关闭订单
     * @return array $order_list
     */
    public function getStoreOrderList($store_id, $order_sn, $buyer_name = '', $state_type, $query_start_date, $query_end_date, $skip_off, $fields = '*', $extend = array(), $chain_id = null)
    {
        $condition = array();
        $condition['store_id'] = $store_id;
        if (preg_match('/^\\d{10,20}$/', $order_sn)) {
            $condition['order_sn'] = $order_sn;
        }
        if ($buyer_name != '') {
            $condition['buyer_name'] = $buyer_name;
        }
        if (isset($chain_id)) {
            $condition['chain_id'] = intval($chain_id);
        }
        $allow_state_array = array('state_new', 'state_pay', 'state_send', 'state_success', 'state_cancel');
        if (in_array($state_type, $allow_state_array)) {
            $condition['order_state'] = str_replace($allow_state_array, array(ORDER_STATE_NEW, ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS, ORDER_STATE_CANCEL), $state_type);
        } else {
            if ($state_type != 'state_notakes') {
                $state_type = 'store_order';
            }
        }
        $if_start_date = preg_match('/^20\\d{2}-\\d{2}-\\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\\d{2}-\\d{2}-\\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time', array($start_unixtime, $end_unixtime));
        }
        if ($skip_off == 1) {
            $condition['order_state'] = array('neq', ORDER_STATE_CANCEL);
        }
        if ($state_type == 'state_new') {
            $condition['chain_code'] = 0;
        }
        if ($state_type == 'state_pay') {
            $condition['chain_code'] = 0;
        }
        if ($state_type == 'state_notakes') {
            $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_PAY));
            $condition['chain_code'] = array('gt', 0);
        }
        $order_list = $this->getOrderList($condition, 20, $fields, 'order_id desc', '', $extend);
        //页面中显示那些操作
        foreach ($order_list as $key => $order_info) {
            //显示取消订单
            $order_info['if_store_cancel'] = $this->getOrderOperateState('store_cancel', $order_info);
            //显示调整费用
            $order_info['if_modify_price'] = $this->getOrderOperateState('modify_price', $order_info);
            //显示调整订单费用
            $order_info['if_spay_price'] = $this->getOrderOperateState('spay_price', $order_info);
            //显示发货
            $order_info['if_store_send'] = $this->getOrderOperateState('store_send', $order_info);
            //显示锁定中
            $order_info['if_lock'] = $this->getOrderOperateState('lock', $order_info);
            //显示物流跟踪
            $order_info['if_deliver'] = $this->getOrderOperateState('deliver', $order_info);
            //门店自提订单完成状态
            $order_info['if_chain_receive'] = $this->getOrderOperateState('chain_receive', $order_info);
            //查询消费者保障服务
            if (core\config::get('contract_allow') == 1) {
                $contract_item = model('contract')->getContractItemByCache();
            }
            foreach ($order_info['extend_order_goods'] as $value) {
                $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
                $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
                $value['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
                //处理消费者保障服务
                if (trim($value['goods_contractid']) && $contract_item) {
                    $goods_contractid_arr = explode(',', $value['goods_contractid']);
                    foreach ((array) $goods_contractid_arr as $gcti_v) {
                        $value['contractlist'][] = $contract_item[$gcti_v];
                    }
                }
                if ($value['goods_type'] == 5) {
                    $order_info['zengpin_list'][] = $value;
                } else {
                    $order_info['goods_list'][] = $value;
                }
            }
            if (empty($order_info['zengpin_list'])) {
                $order_info['goods_count'] = count($order_info['goods_list']);
            } else {
                $order_info['goods_count'] = count($order_info['goods_list']) + 1;
            }
            //取得其它订单类型的信息
            $this->getOrderExtendInfo($order_info);
            $order_list[$key] = $order_info;
        }
        return $order_list;
    }
    /**
     * 取得订单列表(未被删除)
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getNormalOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array())
    {
        $condition['delete_state'] = 0;
        return $this->getOrderList($condition, $pagesize, $field, $order, $limit, $extend);
    }
    /**
     * 取得返佣列表(所有)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList($condition, $page = 0, $field = '*', $order = 'order_id desc', $limit = 0, $buyer_id)
    {
        $where = array();
        $where['points'] = array('gt', '0');  
        $where['pointsdays'] = array('gt', '10');
        $where['buyer_id'] = $buyer_id;
		//$list = $this->table('order')->field($field)->where($where)->limit($limit)->page($page)->order($order)->select();
		$list = $this->table('order_goods')->field($field)->where($where)->limit($limit)->page($page)->order($order)->select();
        if (empty($list)) {
            return array();
        }
        $order_list = array();
        foreach ($list as $value) { 
             $order_goods_list = $this->getOrderInfo(array('order_id' => $value['order_id']));                   
            //$goods_common =  $this->table('goods_common')->where(['goods_commonid'=>$order_goods_list['goods_id']])->find();
             if($order_goods_list && $order_goods_list['order_state']>=20){
                //已经支付数量
                $parm = array();            
                $parm['buyer_id']=$buyer_id;
                $parm['order_id']=$value['order_id'];         
                $parm['goods_id']=$value['goods_id'];                        
                $count = $this->table('points_fanyong')->where($parm)->count();             
               // file_put_contents(dirname(__FILE__).'/tian1.txt','sql==>>count1:'.$count.' order_id'.$parm['order_id'].' goods_id:'.$parm['goods_id']."\r\n", FILE_APPEND);
                if($condition =='state_new' & $count < $value['pointsdays'] & $value['points']>0 && $value['pointsdays']>0 ){
                        //$count++;                   
                        //已经支付天数
                        $value['days']=$count;
                        $value['goods_name']=$value['goods_name'];
                        $value['goods_image']=cthumb($value['goods_image'], $value['store_id']);//$order_goods_list['goods_image'];
                        $value['goods_price']=$value['goods_price'];
                        $value['points']=$value['points'];
                        $value['pointsdays']=$value['pointsdays'];
                        $value['pointsstate']='进行中';
                        $value['payment_time']=date("Y-m-d H:i",$order_goods_list['payment_time']) ;
                        $order_list[] = $value;
               
            
                } 
                if($condition =='state_send' & $count >= $value['pointsdays'] & $value['points']>0 && $value['pointsdays']>0 ){ 
                    //$count++;
                        //已经支付天数
                        $value['days']=$count;
                        $value['goods_name']=$value['goods_name'];
                        $value['goods_image']=cthumb($value['goods_image'], $value['store_id']);
                        $value['goods_price']=$value['goods_price'];
                        $value['points']=$value['points'];
                        $value['pointsdays']=$value['pointsdays'];
                        $value['pointsstate']='已完成';
                        $value['payment_time']=date("Y-m-d H:i",$order_goods_list['payment_time']) ;
                        $order_list[] = $value;
                }   
             }
        }      
       // file_put_contents(dirname(__FILE__).'/tian1.txt','sql==>>order_list:'.print_r($order_list,1)."\r\n", FILE_APPEND);
        return $order_list;
    }
    /**
     * 取得买卖家订单数量某个缓存
     * @param string $type $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return int
     */
    public function getOrderCountByID($type, $id, $key)
    {
        //从数据库中取得
        $field = $type == 'buyer' ? 'buyer_id' : 'store_id';
        $condition = array($field => $id);
        $func = 'getOrderState' . $key;
        $count = $this->{$func}($condition);
        return $count;
    }
    /**
     * 待付款订单数量
     * @param unknown $condition
     */
    public function getOrderStateNewCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_NEW;
        return $this->getOrderCount($condition);
    }
    /**
     * 待发货订单数量
     * @param unknown $condition
     */
    public function getOrderStatePayCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_PAY;
        return $this->getOrderCount($condition);
    }
    /**
     * 待收货订单数量
     * @param unknown $condition
     */
    public function getOrderStateSendCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_SEND;
        return $this->getOrderCount($condition);
    }
    /**
     * 待评价订单数量
     * @param unknown $condition
     */
    public function getOrderStateEvalCount($condition = array())
    {
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['evaluation_state'] = 0;
        return $this->getOrderCount($condition);
    }
    /**
     * 交易中的订单数量
     * @param unknown $condition
     */
    public function getOrderStateTradeCount($condition = array())
    {
        $condition['order_state'] = array(array('neq', ORDER_STATE_CANCEL), array('neq', ORDER_STATE_SUCCESS), 'and');
        return $this->getOrderCount($condition);
    }
    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderCount($condition)
    {
        return $this->table('order')->where($condition)->count();
    }
    /**
     * 取得订单商品表详细信息
     * @param unknown $condition
     * @param string $fields
     * @param string $order
     */
    public function getOrderGoodsInfo($condition = array(), $fields = '*', $order = '')
    {
        return $this->table('order_goods')->where($condition)->field($fields)->order($order)->find();
    }
    /**
     * 取得订单商品表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     * @param string $page
     * @param string $order
     * @param string $group
     * @param string $key
     */

    /**
     * 插入全返表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addPointsfanyong($data)
    {
        $insert = $this->table('points_fanyong')->insert($data);        
        return $insert;
    }
    
    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getFanyongCount($condition)
    {
        return $this->table('points_fanyong')->where($condition)->count();
    }
    
    /**
     * 取得返佣列表(所有)
     * @param unknown $condition
     * @param string $page
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getfanyongList($condition, $page = 0, $field = '*', $order = 'id desc', $limit = 0, $extend = array(), $master = false)
    {
        
        $list = $this->table('points_fanyong')->field($field)->where($condition)->limit($limit)->page($page)->order($order)->select();      
        if (empty($list)) {
            return array();
        }
        $model_order = model('order');
        $order_list = array();
        if($list){
            foreach ($list as $value) {               
                $where['order_id'] = $value['order_id'];
                $order_info = $model_order->getOrderInfo($where);
                $value['order_sn']=$order_info['order_sn'];
                $value['buyer_name']=$order_info['buyer_name'];
                $order_list[]=$value;
            }
        } 
        return $order_list;
    }
}