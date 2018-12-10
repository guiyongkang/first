<?php
/**
 * 商品分析
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class stat_goods extends SystemControl
{
    private $links = array(array('url' => 'act=stat_goods&op=pricerange', 'lang' => 'stat_goods_pricerange'), array('url' => 'act=stat_goods&op=hotgoods', 'lang' => 'stat_hotgoods'), array('url' => 'act=stat_goods&op=goods_sale', 'lang' => 'stat_goods_sale'));
    private $search_arr;
    //处理后的参数
    private $gc_arr;
    //分类数组
    private $choose_gcid;
    //选择的分类ID
    public function __construct()
    {
        parent::__construct();
        core\language::read('stat');
        require_cache(BASE_CORE_PATH . DS . 'framework' . DS . 'function' . DS . 'statistics.php');
        require_cache(BASE_CORE_PATH . DS . 'framework' . DS . 'function' . DS . 'datehelper.php');
        $model = model('stat');
        //存储参数
        $this->search_arr = $_REQUEST;
        //处理搜索时间
        if (in_array($this->search_arr['op'], array('pricerange', 'hotgoods', 'goods_sale'))) {
            $this->search_arr = $model->dealwithSearchTime($this->search_arr);
            //获得系统年份
            $year_arr = getSystemYearArr();
            //获得系统月份
            $month_arr = getSystemMonthArr();
            //获得本月的周时间段
            $week_arr = getMonthWeekArr($this->search_arr['week']['current_year'], $this->search_arr['week']['current_month']);
            core\tpl::output('year_arr', $year_arr);
            core\tpl::output('month_arr', $month_arr);
            core\tpl::output('week_arr', $week_arr);
        }
        core\tpl::output('search_arr', $this->search_arr);
        /**
         * 处理商品分类
         */
        $this->choose_gcid = ($t = (isset($_REQUEST['choose_gcid']) ? intval($_REQUEST['choose_gcid']) : 0)) > 0 ? $t : 0;
        $gccache_arr = model('goods_class')->getGoodsclassCache($this->choose_gcid, 3);
        $this->gc_arr = $gccache_arr['showclass'];
        core\tpl::output('gc_json', json_encode($gccache_arr['showclass']));
        core\tpl::output('gc_choose_json', json_encode($gccache_arr['choose_gcid']));
    }
    /**
     * 价格区间统计
     */
    public function pricerangeOp()
    {
        if (empty($this->search_arr['search_type'])) {
            $this->search_arr['search_type'] = 'day';
        }
        $model = model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        $where = array();
        $where['order_isvalid'] = 1;
        //计入统计的有效订单
        $where['order_add_time'] = array('between', $searchtime_arr);
        //商品分类
        if ($this->choose_gcid > 0) {
            //获得分类深度
            $depth = $this->gc_arr[$this->choose_gcid]['depth'];
            $where['gc_parentid_' . $depth] = $this->choose_gcid;
        }
        $field = '1';
        $pricerange_arr = ($t = trim(core\config::get('stat_pricerange'))) ? unserialize($t) : '';
        if ($pricerange_arr) {
            $stat_arr['series'][0]['name'] = '下单量';
            //设置价格区间最后一项，最后一项只有开始值没有结束值
            $pricerange_count = count($pricerange_arr);
            if ($pricerange_arr[$pricerange_count - 1]['e']) {
                $pricerange_arr[$pricerange_count]['s'] = $pricerange_arr[$pricerange_count - 1]['e'] + 1;
                $pricerange_arr[$pricerange_count]['e'] = '';
            }
            foreach ((array) $pricerange_arr as $k => $v) {
                $v['s'] = intval($v['s']);
                $v['e'] = intval($v['e']);
                //构造查询字段
                if ($v['e']) {
                    $field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']} and goods_pay_price/goods_num <= {$v['e']},goods_num,0)) as goodsnum_{$k}";
                } else {
                    $field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']},goods_num,0)) as goodsnum_{$k}";
                }
            }
            $ordergooods_list = $model->getoneByStatordergoods($where, $field);
            if ($ordergooods_list) {
                foreach ((array) $pricerange_arr as $k => $v) {
                    //横轴
                    if ($v['e']) {
                        $stat_arr['xAxis']['categories'][] = $v['s'] . '-' . $v['e'];
                    } else {
                        $stat_arr['xAxis']['categories'][] = $v['s'] . '以上';
                    }
                    //统计图数据
                    if ($ordergooods_list['goodsnum_' . $k]) {
                        $stat_arr['series'][0]['data'][] = intval($ordergooods_list['goodsnum_' . $k]);
                    } else {
                        $stat_arr['series'][0]['data'][] = 0;
                    }
                }
            }
            //得到统计图数据
            $stat_arr['title'] = '价格销量分布';
            $stat_arr['legend']['enabled'] = false;
            $stat_arr['yAxis'] = '销量';
            $pricerange_statjson = getStatData_LineLabels($stat_arr);
        } else {
            $pricerange_statjson = '';
        }
        core\tpl::output('pricerange_statjson', $pricerange_statjson);
        core\tpl::output('searchtime', implode('|', $searchtime_arr));
        core\tpl::output('top_link', $this->sublink($this->links, 'pricerange'));
        core\tpl::showpage('stat.goods.prange');
    }
    /**
     * 热卖商品
     */
    public function hotgoodsOp()
    {
        if (empty($this->search_arr['search_type'])) {
            $this->search_arr['search_type'] = 'day';
        }
        $model = model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        core\tpl::output('searchtime', implode('|', $searchtime_arr));
        core\tpl::output('top_link', $this->sublink($this->links, 'hotgoods'));
        core\tpl::showpage('stat.goods.hotgoods');
    }
    /**
     * 热卖商品列表
     */
    public function hotgoods_listOp()
    {
        $model = model('stat');
		$_GET['type'] = isset($_GET['type']) ? $_GET['type'] : '';
        switch ($_GET['type']) {
            case 'goodsnum':
                $sort_text = '下单量';
                break;
            default:
                $_GET['type'] = 'orderamount';
                $sort_text = '下单金额';
                break;
        }
        //构造横轴数据
        for ($i = 1; $i <= 50; $i++) {
            //数据
            $stat_arr['series'][0]['data'][] = array('name' => '', 'y' => 0);
            //横轴
            $stat_arr['xAxis']['categories'][] = "{$i}";
        }
        $where = array();
        $where['order_isvalid'] = 1;
        //计入统计的有效订单
        $searchtime_arr_tmp = explode('|', $this->search_arr['t']);
        foreach ((array) $searchtime_arr_tmp as $k => $v) {
            $searchtime_arr[] = intval($v);
        }
        $where['order_add_time'] = array('between', $searchtime_arr);
        //商品分类
        if ($this->choose_gcid > 0) {
            //获得分类深度
            $depth = $this->gc_arr[$this->choose_gcid]['depth'];
            $where['gc_parentid_' . $depth] = $this->choose_gcid;
        }
        //查询统计数据
        $field = ' goods_id,goods_name ';
        switch ($_GET['type']) {
            case 'goodsnum':
                $field .= ' ,SUM(goods_num) as goodsnum ';
                $orderby = 'goodsnum desc';
                break;
            default:
                $_GET['type'] = 'orderamount';
                $field .= ' ,SUM(goods_pay_price) as orderamount ';
                $orderby = 'orderamount desc';
                break;
        }
        $orderby .= ',goods_id';
        $statlist = $model->statByStatordergoods($where, $field, 0, 50, $orderby, 'goods_id');
        foreach ((array) $statlist as $k => $v) {
            switch ($_GET['type']) {
                case 'goodsnum':
                    $stat_arr['series'][0]['data'][$k] = array('name' => strval($v['goods_name']), 'y' => intval($v[$_GET['type']]));
                    break;
                case 'orderamount':
                    $stat_arr['series'][0]['data'][$k] = array('name' => strval($v['goods_name']), 'y' => floatval($v[$_GET['type']]));
                    break;
            }
            $statlist[$k]['sort'] = $k + 1;
        }
        $stat_arr['series'][0]['name'] = $sort_text;
        $stat_arr['legend']['enabled'] = false;
        //得到统计图数据
        $stat_arr['title'] = '热卖商品TOP50';
        $stat_arr['yAxis'] = $sort_text;
        $stat_json = getStatData_Column2D($stat_arr);
        core\tpl::output('stat_json', $stat_json);
        core\tpl::output('statlist', $statlist);
        core\tpl::output('sort_text', $sort_text);
        core\tpl::output('stat_field', $_GET['type']);
        core\tpl::showpage('stat.goods.hotgoods.list', 'null_layout');
    }
    /**
     * 商品销售明细
     */
    public function goods_saleOp()
    {
        if (empty($this->search_arr['search_type'])) {
            $this->search_arr['search_type'] = 'day';
        }
        $model = model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        //获取相关数据
        $where = array();
        $where['order_isvalid'] = 1;
        //计入统计的有效订单
        $where['order_add_time'] = array('between', $searchtime_arr);
        //品牌
        $brand_id = isset($_REQUEST['b_id']) ? intval($_REQUEST['b_id']) : 0;
        if ($brand_id > 0) {
            $where['brand_id'] = $brand_id;
        }
        //商品分类
        if ($this->choose_gcid > 0) {
            //获得分类深度
            $depth = $this->gc_arr[$this->choose_gcid]['depth'];
            $where['gc_parentid_' . $depth] = $this->choose_gcid;
        }
        if (!empty($_GET['goods_name'])) {
            $where['goods_name'] = array('like', '%' . trim($_GET['goods_name']) . '%');
        }
        if (!empty($_GET['store_name'])) {
            $where['store_name'] = array('like', '%' . trim($_GET['store_name']) . '%');
        }
        $field = 'goods_id,goods_name,store_id,store_name,goods_commonid,SUM(goods_num) as goodsnum,COUNT(DISTINCT order_id) as ordernum,SUM(goods_pay_price) as goodsamount';
        //排序
        $orderby_arr = array('goodsnum asc', 'goodsnum desc', 'ordernum asc', 'ordernum desc', 'goodsamount asc', 'goodsamount desc');
        if (isset($this->search_arr['orderby']) && !in_array(trim($this->search_arr['orderby']), $orderby_arr)) {
            $this->search_arr['orderby'] = 'goodsnum desc';
        }
        $orderby = (isset($this->search_arr['orderby']) ? trim($this->search_arr['orderby']) . ',' : '') . 'goods_id asc';
        //查询记录总条数
        $count_arr = $model->getoneByStatordergoods($where, 'COUNT(DISTINCT goods_id) as countnum');
        $countnum = intval($count_arr['countnum']);
        if (isset($_GET['exporttype']) && $_GET['exporttype'] == 'excel') {
            $goods_list = $model->statByStatordergoods($where, $field, 0, 0, $orderby, 'goods_id');
        } else {
            $goods_list = $model->statByStatordergoods($where, $field, array(10, $countnum), 0, $orderby, 'goods_id');
        }
        //导出Excel
        if (isset($_GET['exporttype']) && $_GET['exporttype'] == 'excel') {
            //导出Excel
            $excel_obj = new lib\excel();
            $excel_data = array();
            //设置样式
            $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
            //header
            $excel_data[0][] = array('styleid' => 's_title', 'data' => '商品名称');
            $excel_data[0][] = array('styleid' => 's_title', 'data' => '平台货号');
            $excel_data[0][] = array('styleid' => 's_title', 'data' => '店铺名称');
            $excel_data[0][] = array('styleid' => 's_title', 'data' => '下单商品件数');
            $excel_data[0][] = array('styleid' => 's_title', 'data' => '下单单量');
            $excel_data[0][] = array('styleid' => 's_title', 'data' => '下单金额');
            //data
            foreach ($goods_list as $k => $v) {
                $excel_data[$k + 1][] = array('data' => $v['goods_name']);
                $excel_data[$k + 1][] = array('data' => $v['goods_commonid']);
                $excel_data[$k + 1][] = array('data' => $v['store_name']);
                $excel_data[$k + 1][] = array('data' => $v['goodsnum']);
                $excel_data[$k + 1][] = array('data' => $v['ordernum']);
                $excel_data[$k + 1][] = array('data' => $v['goodsamount']);
            }
            $excel_data = $excel_obj->charset($excel_data, CHARSET);
            $excel_obj->addArray($excel_data);
            $excel_obj->addWorksheet($excel_obj->charset('商品销售明细', CHARSET));
            $excel_obj->generateXML($excel_obj->charset('商品销售明细', CHARSET) . date('Y-m-d-H', time()));
            exit;
        } else {
            //查询品牌
            $brand_list = model('brand')->getBrandList(array('brand_apply' => 1));
            core\tpl::output('brand_list', $brand_list);
            core\tpl::output('goods_list', $goods_list);
            core\tpl::output('show_page', $model->showpage(2));
            core\tpl::output('orderby', isset($this->search_arr['orderby']) ? $this->search_arr['orderby'] : '');
            core\tpl::output('top_link', $this->sublink($this->links, 'goods_sale'));
            core\tpl::showpage('stat.goodssale');
        }
    }
}