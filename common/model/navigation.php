<?php
/**
 * 文件的简短描述
 *
 * 文件的详细描述
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class navigation
{
    /**
     * 列表
     *
     * @param array $condition 检索条件
     * @param obj $page 分页
     * @return array 数组结构的返回结果
     */
    public function getNavigationList($condition, $page)
    {
        $condition_str = $this->_condition($condition);
        $param = array();
        $param['table'] = 'navigation';
        $param['where'] = $condition_str;
        $param['order'] = $condition['order'] ? $condition['order'] : 'nav_id';
        $result = db\mysqli::select($param, $page);
        return $result;
    }
    /**
     * 构造检索条件
     *
     * @param int $id 记录ID
     * @return string 字符串类型的返回结果
     */
    private function _condition($condition)
    {
        $condition_str = '';
        if (!empty($condition['like_nav_title'])) {
            $condition_str .= " and nav_title like '%" . $condition['like_nav_title'] . "%'";
        }
        if (!empty($condition['nav_location'])) {
            $condition_str .= " and nav_location = '" . $condition['nav_location'] . "'";
        }
        return $condition_str;
    }
    /**
     * 取单个内容
     *
     * @param int $id ID
     * @return array 数组类型的返回结果
     */
    public function getOneNavigation($id)
    {
        if (intval($id) > 0) {
            $param = array();
            $param['table'] = 'navigation';
            $param['field'] = 'nav_id';
            $param['value'] = intval($id);
            $result = db\mysqli::getRow($param);
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function add($param)
    {
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            $tmp = array();
            foreach ($param as $k => $v) {
                $tmp[$k] = $v;
            }
            $result = db\mysqli::insert('navigation', $tmp);
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function update($param)
    {
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            $tmp = array();
            foreach ($param as $k => $v) {
                $tmp[$k] = $v;
            }
            $where = " nav_id = '" . $param['nav_id'] . "'";
            $result = db\mysqli::update('navigation', $tmp, $where);
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 删除
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id)
    {
        if (intval($id) > 0) {
            $where = " nav_id = '" . intval($id) . "'";
            $result = db\mysqli::delete('navigation', $where);
            return $result;
        } else {
            return false;
        }
    }
}