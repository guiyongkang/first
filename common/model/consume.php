<?php
namespace common\model;

use db;
use core;
defined('SAFE_CONST') or exit('Access Invalid!');
class consume extends core\model
{
    public function __construct()
    {
        parent::__construct('consume');
    }
    /**
     * 构造检索条件
     *
     * @param array $condition 检索条件
     * @return string 数组形式的返回结果
     */
    private function _condition($condition)
    {
        $condition_str = '';
        if (!empty($condition['member_id'])) {
            $condition_str .= " member_id = '" . intval($condition['member_id']) . "'";
        }
        return $condition_str;
    }
    /**
     * 新增地址
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addConsume($param)
    {
        return $this->insert($param);
    }
}