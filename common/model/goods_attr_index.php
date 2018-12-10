<?php
/**
 * 商品与属性对应
 *
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class goods_attr_index extends core\model
{
    public function __construct()
    {
        parent::__construct('goods_attr_index');
    }
    /**
     * 对应列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsAttrIndexList($condition, $field = '*')
    {
        return $this->where($condition)->field($field)->select();
    }
}