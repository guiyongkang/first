<?php
/**
 * 前台登录 退出操作
 */
namespace pc\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class index extends BaseHomeControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 登录操作
     *
     */
    public function indexOp()
    {
        header('location:' . urlShop('show_joinin'));
        exit;
        /*20160917edit by Mr.范*/
    }
    /**
     * json输出地址数组 原data/resource/js/area_array.js
     */
    public function json_areaOp()
    {
        echo $_GET['callback'] . '(' . json_encode(model('area')->getAreaArrayForJson()) . ')';
    }
    /**
     * json输出地址数组
     */
    public function json_area_showOp()
    {
        $area_info['text'] = model('area')->getTopAreaName(intval($_GET['area_id']));
        echo $_GET['callback'] . '(' . json_encode($area_info) . ')';
    }
    //json输出商品分类
    public function josn_classOp()
    {
        /**
         * 实例化商品分类模型
         */
        $model_class = model('goods_class');
        $goods_class = $model_class->getGoodsClassListByParentId(intval($_GET['gc_id']));
        $array = array();
        if (is_array($goods_class) and count($goods_class) > 0) {
            foreach ($goods_class as $val) {
                $array[$val['gc_id']] = array('gc_id' => $val['gc_id'], 'gc_name' => htmlspecialchars($val['gc_name']), 'gc_parent_id' => $val['gc_parent_id'], 'commis_rate' => $val['commis_rate'], 'gc_sort' => $val['gc_sort']);
            }
        }
        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK') {
            $array = core\language::getUTF8(array_values($array));
            //网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        } else {
            $array = array_values($array);
        }
        echo $_GET['callback'] . '(' . json_encode($array) . ')';
    }
}