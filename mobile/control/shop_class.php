<?php
/**
 * 所有店铺分类
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class shop_class extends mobileHomeControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /*
     * 首页显示
     */
    public function indexOp()
    {
        $this->_get_shop_class_List();
    }
    private function _get_shop_class_List()
    {
        //获取自营店列表
        $model_store_class = model("store_class");
        //如果只想显示自营店铺，把下面的//去掉即可
        //$condition = array(
        //   'is_own_shop' => 1,
        //);
        $lst = $model_store_class->getStoreClassList();
        $new_lst = array();
        foreach ($lst as $key => $value) {
            $new_lst[$key]['sc_id'] = $lst[$key]['sc_id'];
            $new_lst[$key]['sc_name'] = $lst[$key]['sc_name'];
            $new_lst[$key]['sc_bail'] = $lst[$key]['sc_bail'];
            $new_lst[$key]['sc_sort'] = $lst[$key]['sc_sort'];
        }
        output_data(array('class_list' => $new_lst));
    }
}