<?php
/**
 * 圈子模型
 *
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class circle extends core\model
{
    public function __construct()
    {
        parent::__construct('circle');
    }
    /**
     * 获取圈子数量
     * @param array $condition
     * @return int
     */
    public function getCircleCount($condition)
    {
        return $this->where($condition)->count();
    }
    /**
     * 未审核的圈子数量
     * @param array $condition
     * @return int
     */
    public function getCircleUnverifiedCount($condition = array())
    {
        $condition['circle_status'] = 2;
        return $this->getCircleCount($condition);
    }
}