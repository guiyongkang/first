<?php
/**
 * 任务计划 - 小时执行的任务
 *
 */
namespace crontab\control;
use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class pointsfanyong extends control
{
    /**
     * 执行频率常量 1小时
     * @var int
     */
    const EXE_TIMES = 3600;
    private $_doc;
    private $_xs;
    private $_index;
    private $_search;
    /**
     * 默认方法
     */
    public function indexOp()
    {
        //更新全文搜索内容
        $this->_xs_update();
    }
    /**
     * 初始化对象
     */
    private function _ini_xs()
    {
        require BASE_DATA_PATH . '/api/xs/lib/XS.php';
        $this->_doc = new \XSDocument();
        $this->_xs = new \XS(core\config::get('fullindexer.appname'));
        $this->_index = $this->_xs->index;
        $this->_search = $this->_xs->search;
        $this->_search->setCharset(CHARSET);
    }
    /**
     * 全量创建索引
     */
    public function xs_getdata()
    {      
        if (!core\config::get('fullindexer.open')) {
            return;
        }
        $this->_ini_xs();
        try {
            //每次批量更新商品数
            $step_num = 200;
            $model_goods = model('goods');
            $count = $model_goods->getGoodsOnlineCount(array(), "distinct CONCAT(goods_commonid,',',color_id)");
            echo 'Total:' . $count . "\n";
            $fields = "*,CONCAT(goods_commonid,',',color_id) as nc_distinct";
            for ($i = 0; $i <= $count; $i = $i + $step_num) {
                $goods_list = $model_goods->getGoodsOnlineList(array(), $fields, 0, '', "{$i},{$step_num}", 'nc_distinct');
                $this->_build_goods($goods_list);
                echo $i . " ok\n";
                flush();
                ob_flush();
            }
            if ($count > 0) {
                sleep(2);
                $this->_index->flushIndex();
                sleep(2);
                $this->_index->flushLogging();
            }
        } catch (XSException $e) {
            $this->log($e->getMessage());
        }
    }
    /**
     * 更新增量索引
     */
    public function _xs_update()
    {                
        set_time_limit(0);
        file_put_contents(dirname(__FILE__).'/tian1.txt',date("Y-m-d H:i:s", time()).' start'."\r\n", FILE_APPEND);       
        try {
            $valuea['addtime'] = time();
            $datey=date('Y',$valuea['addtime']);       
            $datem=date('m',$valuea['addtime']);
            $dated=date('d',$valuea['addtime']);         
            $beginToday=mktime(0,0,0,$datem,$dated,$datey);
            $endToday=mktime(0,0,0,$datem,$dated+1,$datey)-1;
			
            
            $model_goods = model('goods');
            $model_order = model('order');
            $model_points_fanyong = model('points_fanyong');
            $model_member = model('member');
            $model_dis = model('distributor');
            $condition = array();         
            
            $sql_goods_common = "SELECT * FROM shop_order_goods  where points >0 and pointsdays > 0 ORDER BY rec_id desc";
            $list = $model_goods->query($sql_goods_common);
            file_put_contents(dirname(__FILE__).'/tian1.txt',date("Y-m-d H:i:s", time()).' list:'.print_r($list,1)."\r\n", FILE_APPEND);
            if($list){
                foreach($list as $value){
                    $sql_order = "SELECT DISTINCT order_id, buyer_id,order_state FROM shop_order  where order_state>10 and FROM_UNIXTIME(payment_time,'%Y-%m-%d') < CURDATE() and  order_id =".$value['order_id']." ORDER BY order_id desc";//
                    $list_order = $model_goods->query($sql_order);
                   // file_put_contents(dirname(__FILE__).'/tian1.txt',date("Y-m-d H:i:s", time()).' list_order:'.print_r($list_order,1)."\r\n", FILE_APPEND);
                    if($list_order){
                        foreach($list_order as $value_order){
                                                       
                            $parm = array();
                            $parm['buyer_id']=$value_order['buyer_id'];                        
                            $parm['order_id']=$value_order['order_id'];
                            $parm['goods_id']=$value['goods_id'];
                            $count =$model_points_fanyong->getFanyongCount($parm);
                            file_put_contents(dirname(__FILE__).'/tian1.txt',date("Y-m-d H:i:s", time()).' count:'.$count."\r\n", FILE_APPEND);
                            $parm['points_time'] = array('between', $beginToday . ',' . $endToday);
                            $cuns =$model_points_fanyong->getFanyongCount($parm);                      
                            file_put_contents(dirname(__FILE__).'/tian1.txt',date("Y-m-d H:i:s", time()).' cuns:'.$cuns."\r\n", FILE_APPEND);
                            if($count < $value['pointsdays'] && $cuns==0){                                
                                $data = array();
                                $points_price =$value['goods_price'] * $value['points'] / 100;
                                $data['order_id'] = $value_order['order_id'];
                                $data['goods_id'] = $value['goods_id'];
                                $data['buyer_id'] = $value_order['buyer_id'];
                                $data['points'] = $value['points'];
                                $data['pointsdays'] = $value['pointsdays'];
                                $data['points_price'] = $points_price;
                                $data['points_time'] = time(); 
                                file_put_contents(dirname(__FILE__).'/tian1.txt',date("Y-m-d H:i:s", time()).' '.print_r($data,1)."\r\n", FILE_APPEND);                               
                                $model_points_fanyong->addPointsfanyong($data);                              
                                
                              /*   $detail_data[] = array(                               
                                    'good_id'=>$value['goods_id'],
                                    'order_id'=>$value_order['order_id'],
                                    'member_id'=>$value_order['buyer_id'],                            
                                    'detail_bonus'=>$points_price,                                 
                                    'detail_desc'=>'本次获得全返金额'.$points_price.'元',
                                    'detail_status'=>$value_order['order_state'],
                                    'points_status'=>1,
                                    'detail_addtime'=>time()
                                ); */
                                $detail_data = array();
                                 $detail_data['good_id']=$value['goods_id'];
                                 $detail_data['order_id']=$value_order['order_id'];
                                 $detail_data['member_id']=$value_order['buyer_id'];                            
                                 $detail_data['detail_bonus']=$points_price;                                
                                 $detail_data['detail_desc']='本次获得全返金额'.$points_price.'元';
                                 $detail_data['detail_status']=$value_order['order_state'];
                                 $detail_data['points_status']=1;
                                 $detail_data['detail_addtime']=time();
                                $res_456 = $model_dis->addInfo('distributor_goodsrecord_detail',$detail_data);
                                file_put_contents(dirname(__FILE__).'/tian1.txt',date("Y-m-d H:i:s", time()).' distributor_goodsrecord_detail:'.print_r($detail_data,1)."\r\n", FILE_APPEND);
                            }
                        }                        
                    }                   
                }
            }                 
            
            file_put_contents(dirname(__FILE__).'/tian1.txt',date("Y-m-d H:i:s", time()).' end'."\r\n", FILE_APPEND);          
            echo urlencode(core\config::get('mobile_username')).'--'.urlencode(core\config::get('mobile_pwd'));            
        } catch (XSException $e) {
            $this->log($e->getMessage());
        }
    }
    /**
     * 索引商品数据
     * @param array $goods_list
     */
    private function _build_goods($goods_list = array())
    {
        if (empty($goods_list) || !is_array($goods_list)) {
            return;
        }
        $goods_class = model('goods_class')->getGoodsClassForCacheModel();
        $goods_commonid_array = array();
        $goods_id_array = array();
        $store_id_array = array();
        foreach ($goods_list as $k => $v) {
            $goods_commonid_array[] = $v['goods_commonid'];
            $goods_id_array[] = $v['goods_id'];
            $store_id_array[] = $v['store_id'];
        }
        //取common表内容
        $model_goods = model('goods');
        $condition_common = array();
        $condition_common['goods_commonid'] = array('in', $goods_commonid_array);
        $goods_common_list = $model_goods->getGoodsCommonOnlineList($condition_common, '*', 0);
        $goods_common_new_list = array();
        foreach ($goods_common_list as $k => $v) {
            $goods_common_new_list[$v['goods_commonid']] = $v;
        }
        //取属性表值
        $model_type = model('type');
        $attr_list = $model_type->getGoodsAttrIndexList(array('goods_id' => array('in', $goods_id_array)), 0, 'goods_id,attr_value_id');
        if (is_array($attr_list) && !empty($attr_list)) {
            $attr_value_list = array();
            foreach ($attr_list as $val) {
                $attr_value_list[$val['goods_id']][] = $val['attr_value_id'];
            }
        }
        //整理需要索引的数据
        foreach ($goods_list as $k => $v) {
            $gc_id = $v['gc_id'];
            $depth = $goods_class[$gc_id]['depth'];
            if ($depth == 3) {
                $cate_3 = $gc_id;
                $gc_id = $goods_class[$gc_id]['gc_parent_id'];
                $depth--;
            }
            if ($depth == 2) {
                $cate_2 = $gc_id;
                $gc_id = $goods_class[$gc_id]['gc_parent_id'];
                $depth--;
            }
            if ($depth == 1) {
                $cate_1 = $gc_id;
                $gc_id = $goods_class[$gc_id]['gc_parent_id'];
            }
            $index_data = array();
            $index_data['pk'] = $v['goods_id'];
            $index_data['goods_id'] = $v['goods_id'];
            $index_data['goods_name'] = $v['goods_name'] . $v['goods_jingle'];
            $index_data['brand_id'] = $v['brand_id'];
            $index_data['goods_price'] = $v['goods_promotion_price'];
            $index_data['goods_click'] = $v['goods_click'];
            $index_data['goods_salenum'] = $v['goods_salenum'];
            // 判断店铺是否为自营店铺
            $index_data['store_id'] = $v['is_own_shop'];
            $index_data['area_id'] = $v['areaid_1'];
            $index_data['gc_id'] = $v['gc_id'];
            $index_data['gc_name'] = str_replace('&gt;', '', $goods_common_new_list[$v['goods_commonid']]['gc_name']);
            $index_data['brand_name'] = $goods_common_new_list[$v['goods_commonid']]['brand_name'];
            $index_data['have_gift'] = $v['have_gift'];
            if (!empty($attr_value_list[$v['goods_id']])) {
                $index_data['attr_id'] = implode('_', $attr_value_list[$v['goods_id']]);
            }
            if (!empty($cate_1)) {
                $index_data['cate_1'] = $cate_1;
            }
            if (!empty($cate_2)) {
                $index_data['cate_2'] = $cate_2;
            }
            if (!empty($cate_3)) {
                $index_data['cate_3'] = $cate_3;
            }
            //添加到索引库
            $this->_doc->setFields($index_data);
            $this->_index->update($this->_doc);
        }
    }
    public function xs_clearOp()
    {
        if (!core\config::get('fullindexer.open')) {
            return;
        }
        $this->_ini_xs();
        try {
            $this->_index->clean();
        } catch (\XSException $e) {
            $this->log($e->getMessage());
        }
    }
    public function xs_flushLoggingOp()
    {
        if (!core\config::get('fullindexer.open')) {
            return;
        }
        $this->_ini_xs();
        try {
            $this->_index->flushLogging();
        } catch (\XSException $e) {
            $this->log($e->getMessage());
        }
    }
    public function xs_flushIndexOp()
    {
        if (!core\config::get('fullindexer.open')) {
            return;
        }
        $this->_ini_xs();
        try {
            $this->_index->flushIndex();
        } catch (\XSException $e) {
            $this->log($e->getMessage());
        }
    }
}