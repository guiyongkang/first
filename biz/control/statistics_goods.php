<?php
/**
 * 统计概述
 **/
namespace biz\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class statistics_goods extends BaseSellerControl
{
    private $search_arr;
    //处理后的参数
    private $gc_arr;
    //分类数组
    private $choose_gcid;
    //选择的分类ID
    public function __construct()
    {
        parent::__construct();
        core\language::read('member_store_statistics');
        require_cache(BASE_CORE_PATH . DS . 'framework' . DS . 'function' . DS . 'statistics.php');
        require_cache(BASE_CORE_PATH . DS . 'framework' . DS . 'function' . DS . 'datehelper.php');
        $model = model('stat');
        //存储参数
        $this->search_arr = $_REQUEST;
        //处理搜索时间
        if (in_array($this->search_arr['op'], array('price', 'hotgoods'))) {
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
        $this->choose_gcid =  !empty($_REQUEST['choose_gcid']) ? intval($_REQUEST['choose_gcid']) : 0;
        $gccache_arr = model('goods_class')->getGoodsclassCache($this->choose_gcid, 3);
        $this->gc_arr = $gccache_arr['showclass'];
        core\tpl::output('gc_json', json_encode($gccache_arr['showclass']));
        core\tpl::output('gc_choose_json', json_encode($gccache_arr['choose_gcid']));
    }
    /**
     * 商品列表
     */
    public function goodslistOp()
    {
        $model = model('stat');
        //统计的日期0点
        $stat_time = strtotime(date('Y-m-d', time())) - 86400;
        /*
         * 近30天
         */
        $stime = $stat_time - 86400 * 29;
        //30天前
        $etime = $stat_time + 86400 - 1;
        //昨天23:59
        //查询订单商品表下单商品数
        $where = array();
        $where['order_isvalid'] = 1;
        //计入统计的有效订单
        $where['store_id'] = core\session::get('store_id');
        $where['order_add_time'] = array('between', array($stime, $etime));
        if ($this->choose_gcid > 0) {
            $gc_depth = $this->gc_arr[$this->choose_gcid]['depth'];
            $where['gc_parentid_' . $gc_depth] = $this->choose_gcid;
        }
        if (!empty($_GET['search_gname'])) {
            $where['goods_name'] = array('like', '%' . trim($_GET['search_gname']) . '%');
        }
        //查询总条数
        $count_arr = $model->statByStatordergoods($where, 'count(DISTINCT goods_id) as countnum');
        $countnum = intval($count_arr[0]['countnum']);
        $field = ' goods_id,goods_name,goods_image,goods_price,SUM(goods_num) as ordergoodsnum,SUM(goods_pay_price) as ordergamount ';
        //排序
        $orderby_arr = array('ordergoodsnum asc', 'ordergoodsnum desc', 'ordergamount asc', 'ordergamount desc');
        if (isset($this->search_arr['orderby']) && !in_array(trim($this->search_arr['orderby']), $orderby_arr)) {
            $this->search_arr['orderby'] = 'ordergoodsnum desc';
        }
        $orderby = isset($this->search_arr['orderby']) ? trim($this->search_arr['orderby']) . ',' : '' . 'goods_id';
        $stat_ordergoods = $model->statByStatordergoods($where, $field, array(5, $countnum), 0, (isset($this->search_arr['orderby']) ? $this->search_arr['orderby'] : ''), 'goods_id');
        core\tpl::output('goodslist', $stat_ordergoods);
        core\tpl::output('show_page', $model->showpage(2));
        core\tpl::output('orderby', isset($this->search_arr['orderby']) ? $this->search_arr['orderby'] : '');
        self::profile_menu('goodslist');
        core\tpl::showpage('stat.goods.goodslist');
    }
    /**
     * 商品详细
     */
    public function goodsinfoOp()
    {
        $templatesname = 'stat.goods.goodsinfo';
        $goods_id = intval($_GET['gid']);
        if ($goods_id <= 0) {
            core\tpl::output('stat_msg', '参数错误');
            core\tpl::showpage($templatesname, 'null_layout');
        }
        //查询商品信息
        $goods_info = model('goods')->getGoodsInfoByID($goods_id, 'goods_name');
        if (!$goods_info) {
            core\tpl::output('stat_msg', '参数错误');
            core\tpl::showpage($templatesname, 'null_layout');
        }
        $model = model('stat');
        //统计的日期0点
        $stat_time = strtotime(date('Y-m-d', time())) - 86400;
        /*
         * 近30天
         */
        $stime = $stat_time - 86400 * 29;
        //30天前
        $etime = $stat_time + 86400 - 1;
        //昨天23:59
        $stat_arr = array();
        for ($i = $stime; $i < $etime; $i += 86400) {
            //当前数据的时间
            $timetext = date('n', $i) . '-' . date('j', $i);
            //统计图数据
            $stat_list['ordergoodsnum'][$timetext] = 0;
            $stat_list['ordergamount'][$timetext] = 0;
            $stat_list['ordernum'][$timetext] = 0;
            //横轴
            $stat_arr['ordergoodsnum']['xAxis']['categories'][] = $timetext;
            $stat_arr['ordergamount']['xAxis']['categories'][] = $timetext;
            $stat_arr['ordernum']['xAxis']['categories'][] = $timetext;
        }
        //查询订单商品表下单商品数
        $where = array();
        $where['goods_id'] = $goods_id;
        $where['order_isvalid'] = 1;
        //计入统计的有效订单
        $where['store_id'] = core\session::get('store_id');
        $where['order_add_time'] = array('between', array($stime, $etime));
        $field = ' goods_id,goods_name,COUNT(DISTINCT order_id) as ordernum,SUM(goods_num) as ordergoodsnum,SUM(goods_pay_price) as ordergamount,MONTH(FROM_UNIXTIME(order_add_time)) as monthval,DAY(FROM_UNIXTIME(order_add_time)) as dayval ';
        $stat_ordergoods = $model->statByStatordergoods($where, $field, 0, 0, '', 'monthval,dayval');
        $stat_count = array();
        if ($stat_ordergoods) {
            foreach ($stat_ordergoods as $k => $v) {
                $stat_list['ordergoodsnum'][$v['monthval'] . '-' . $v['dayval']] = intval($v['ordergoodsnum']);
                $stat_list['ordergamount'][$v['monthval'] . '-' . $v['dayval']] = floatval($v['ordergamount']);
                $stat_list['ordernum'][$v['monthval'] . '-' . $v['dayval']] = intval($v['ordernum']);
                $stat_count['ordergoodsnum'] = (isset($stat_count['ordergoodsnum']) ? intval($stat_count['ordergoodsnum']) : 0) + (isset($v['ordergoodsnum']) ? $v['ordergoodsnum'] : 0);
                $stat_count['ordergamount'] = (isset($stat_count['ordergamount']) ? floatval($stat_count['ordergamount']) : 0) + (isset($v['ordergamount']) ? floatval($v['ordergamount']) : 0);
                $stat_count['ordernum'] = (isset($stat_count['ordernum']) ? intval($stat_count['ordernum']) : 0) + (isset($v['ordernum']) ? $v['ordernum'] : 0);
            }
        }
        $stat_count['ordergamount'] = ncPriceFormat($stat_count['ordergamount']);
        $stat_arr['ordergoodsnum']['legend']['enabled'] = false;
        $stat_arr['ordergoodsnum']['series'][0]['name'] = '下单商品数';
        $stat_arr['ordergoodsnum']['series'][0]['data'] = array_values($stat_list['ordergoodsnum']);
        $stat_arr['ordergoodsnum']['title'] = '最近30天下单商品数走势';
        $stat_arr['ordergoodsnum']['yAxis'] = '下单金额';
        $stat_json['ordergoodsnum'] = getStatData_LineLabels($stat_arr['ordergoodsnum']);
        $stat_arr['ordergamount']['legend']['enabled'] = false;
        $stat_arr['ordergamount']['series'][0]['name'] = '下单金额';
        $stat_arr['ordergamount']['series'][0]['data'] = array_values($stat_list['ordergamount']);
        $stat_arr['ordergamount']['title'] = '最近30天下单金额走势';
        $stat_arr['ordergamount']['yAxis'] = '下单金额';
        $stat_json['ordergamount'] = getStatData_LineLabels($stat_arr['ordergamount']);
        $stat_arr['ordernum']['legend']['enabled'] = false;
        $stat_arr['ordernum']['series'][0]['name'] = '下单量';
        $stat_arr['ordernum']['series'][0]['data'] = array_values($stat_list['ordernum']);
        $stat_arr['ordernum']['title'] = '最近30天下单量走势';
        $stat_arr['ordernum']['yAxis'] = '下单金额';
        $stat_json['ordernum'] = getStatData_LineLabels($stat_arr['ordernum']);
        core\tpl::output('stat_json', $stat_json);
        core\tpl::output('stat_count', $stat_count);
        core\tpl::output('goods_info', $goods_info);
        core\tpl::showpage($templatesname, 'null_layout');
    }
    /**
     * 价格销量统计
     */
    public function priceOp()
    {
        if (empty($this->search_arr['search_type'])) {
            $this->search_arr['search_type'] = 'day';
        }
        $model = model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        $where = array();
        $where['store_id'] = core\session::get('store_id');
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
        $pricerange = model('store_extend')->getfby_store_id(core\session::get('store_id'), 'pricerange');
        $pricerange_arr = $pricerange ? unserialize($pricerange) : array();
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
        core\tpl::output('statjson', $pricerange_statjson);
        self::profile_menu('price');
        core\tpl::showpage('stat.goods.price');
    }
    /**
     * 热卖商品
     */
    public function hotgoodsOp()
    {
        $topnum = 30;
        if (empty($this->search_arr['search_type'])) {
            $this->search_arr['search_type'] = 'day';
        }
        $model = model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        $model = model('stat');
        $where = array();
        $where['store_id'] = core\session::get('store_id');
        $where['order_isvalid'] = 1;
        //计入统计的有效订单
        $where['order_add_time'] = array('between', $searchtime_arr);
        //查询销量top
        //构造横轴数据
        for ($i = 1; $i <= $topnum; $i++) {
            //数据
            $stat_arr['series'][0]['data'][] = array('name' => '', 'y' => 0);
            //横轴
            $stat_arr['xAxis']['categories'][] = "{$i}";
        }
        $field = ' goods_id,goods_name,SUM(goods_num) as goodsnum ';
        $orderby = 'goodsnum desc,goods_id';
        $statlist = array();
        $statlist['goodsnum'] = $model->statByStatordergoods($where, $field, 0, $topnum, $orderby, 'goods_id');
        foreach ((array) $statlist['goodsnum'] as $k => $v) {
            $stat_arr['series'][0]['data'][$k] = array('name' => strval($v['goods_name']), 'y' => intval($v['goodsnum']));
        }
        $stat_arr['series'][0]['name'] = '下单商品数';
        $stat_arr['legend']['enabled'] = false;
        //得到统计图数据
        $stat_arr['title'] = '热卖商品TOP' . $topnum;
        $stat_arr['yAxis'] = '下单商品数';
        $stat_json['goodsnum'] = getStatData_Column2D($stat_arr);
        unset($stat_arr);
        //查询下单金额top
        //构造横轴数据
        for ($i = 1; $i <= $topnum; $i++) {
            //数据
            $stat_arr['series'][0]['data'][] = array('name' => '', 'y' => 0);
            //横轴
            $stat_arr['xAxis']['categories'][] = "{$i}";
        }
        $field = ' goods_id,goods_name,SUM(goods_pay_price) as orderamount ';
        $orderby = 'orderamount desc,goods_id';
        $statlist['orderamount'] = $model->statByStatordergoods($where, $field, 0, $topnum, $orderby, 'goods_id');
        foreach ((array) $statlist['orderamount'] as $k => $v) {
            $stat_arr['series'][0]['data'][$k] = array('name' => strval($v['goods_name']), 'y' => floatval($v['orderamount']));
        }
        $stat_arr['series'][0]['name'] = '下单金额';
        $stat_arr['legend']['enabled'] = false;
        //得到统计图数据
        $stat_arr['title'] = '热卖商品TOP' . $topnum;
        $stat_arr['yAxis'] = '下单金额';
        $stat_json['orderamount'] = getStatData_Column2D($stat_arr);
        core\tpl::output('stat_json', $stat_json);
        core\tpl::output('statlist', $statlist);
        self::profile_menu('hotgoods');
        core\tpl::showpage('stat.goods.hotgoods');
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key = '')
    {
        $menu_array = array(1 => array('menu_key' => 'goodslist', 'menu_name' => '商品详情', 'menu_url' => 'index.php?act=statistics_goods&op=goodslist'), 2 => array('menu_key' => 'price', 'menu_name' => '价格销量', 'menu_url' => 'index.php?act=statistics_goods&op=price'), 3 => array('menu_key' => 'hotgoods', 'menu_name' => '热卖商品', 'menu_url' => 'index.php?act=statistics_goods&op=hotgoods'));
        core\tpl::output('member_menu', $menu_array);
        core\tpl::output('menu_key', $menu_key);
    }
}