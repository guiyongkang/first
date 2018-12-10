<?php
/**
 * 举报主题模型 
 *
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class inform_subject
{
    /*
     * 构造条件
     */
    private function getCondition($condition)
    {
        $condition_str = '';
        if (!empty($condition['inform_subject_state'])) {
            $condition_str .= " and inform_subject_state = '{$condition['inform_subject_state']}'";
        }
        if (!empty($condition['inform_subject_type_id'])) {
            $condition_str .= " and inform_subject_type_id = '{$condition['inform_subject_type_id']}'";
        }
        if (!empty($condition['in_inform_subject_id'])) {
            $condition_str .= " and inform_subject_id in (" . $condition['in_inform_subject_id'] . ')';
        }
        if (!empty($condition['in_inform_subject_type_id'])) {
            $condition_str .= " and inform_subject_type_id in (" . $condition['in_inform_subject_type_id'] . ')';
        }
        return $condition_str;
    }
    /*
     * 增加 
     * @param array $param
     * @return bool
     */
    public function saveInformSubject($param)
    {
        return db\mysqli::insert('inform_subject', $param);
    }
    /*
     * 更新
     * @param array $update_array
     * @param array $where_array
     * @return bool
     */
    public function updateInformSubject($update_array, $where_array)
    {
        $where = $this->getCondition($where_array);
        return db\mysqli::update('inform_subject', $update_array, $where);
    }
    /*
     * 删除
     * @param array $param
     * @return bool
     */
    public function dropInformSubject($param)
    {
        $where = $this->getCondition($param);
        return db\mysqli::delete('inform_subject', $where);
    }
    /*
     *  获得列表
     *  @param array $condition
     *  @param obj $page 	//分页对象
     *  @return array
     */
    public function getInformSubject($condition = '', $page = '', $field = '')
    {
        $param = array();
        $param['table'] = 'inform_subject';
        $param['field'] = $field;
        $param['where'] = $this->getCondition($condition);
        $param['order'] = $condition['order'] ? $condition['order'] : ' inform_subject_id desc ';
        return db\mysqli::select($param, $page);
    }
}