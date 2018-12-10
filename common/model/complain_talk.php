<?php
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class complain_talk
{
    /*
     * 构造条件
     */
    private function getCondition($condition)
    {
        $condition_str = '';
        if (!empty($condition['complain_id'])) {
            $condition_str .= " and complain_id = '{$condition['complain_id']}'";
        }
        if (!empty($condition['talk_id'])) {
            $condition_str .= " and talk_id = '{$condition['talk_id']}'";
        }
        return $condition_str;
    }
    /*
     * 增加 
     * @param array $param
     * @return bool
     */
    public function saveComplainTalk($param)
    {
        return db\mysqli::insert('complain_talk', $param);
    }
    /*
     * 更新
     * @param array $update_array
     * @param array $where_array
     * @return bool
     */
    public function updateComplainTalk($update_array, $where_array)
    {
        $where = $this->getCondition($where_array);
        return db\mysqli::update('complain_talk', $update_array, $where);
    }
    /*
     * 删除
     * @param array $param
     * @return bool
     */
    public function dropComplainTalk($param)
    {
        $where = $this->getCondition($param);
        return db\mysqli::delete('complain_talk', $where);
    }
    /*
     *  获得列表
     *  @param array $condition
     *  @param obj $page 	//分页对象
     *  @return array
     */
    public function getComplainTalk($condition = '', $page = '', $field = '*')
    {
        $param = array();
        $param['table'] = 'complain_talk';
        $param['field'] = $field;
        $param['where'] = $this->getCondition($condition);
        $param['order'] = $condition['order'] ? $condition['order'] : ' talk_id desc ';
        return db\mysqli::select($param, $page);
    }
}