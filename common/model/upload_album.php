<?php
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class upload_album
{
    /**
     * 列表
     *
     * @param array $condition 检索条件
     * @return array 数组结构的返回结果
     */
    public function getUploadList($condition)
    {
        $condition_str = $this->_condition($condition);
        $param = array();
        $param['table'] = 'album_pic';
        $param['where'] = $condition_str;
        $result = db\mysqli::select($param);
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
        if (!empty($condition['apic_name'])) {
            $condition_str .= " and apic_name='{$condition['pic_name']}'";
        }
        if (!empty($condition['apic_tag'])) {
            $condition_str .= " and apic_tag='{$condition['apic_tag']}'";
        }
        if (!empty($condition['aclass_id'])) {
            $condition_str .= " and aclass_id='{$condition['aclass_id']}'";
        }
        if (!empty($condition['apic_cover'])) {
            $condition_str .= " and apic_cover='{$condition['apic_cover']}'";
        }
        if (!empty($condition['apic_size'])) {
            $condition_str .= " and apic_size='{$condition['apic_size']}'";
        }
        if (!empty($condition['store_id'])) {
            $condition_str .= " and store_id='{$condition['store_id']}'";
        }
        if (!empty($condition['upload_time'])) {
            $condition_str .= " and upload_time='{$condition['upload_time']}'";
        }
        return $condition_str;
    }
    /**
     * 取单个内容
     *
     * @param int $id 分类ID
     * @return array 数组类型的返回结果
     */
    public function getOneUpload($id)
    {
        if (intval($id) > 0) {
            $param = array();
            $param['table'] = 'album_pic';
            $param['field'] = 'apic_id';
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
            $result = db\mysqli::insert('album_pic', $param);
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
            $where = " apic_id = '{$param['apic_id']}'";
            $result = db\mysqli::update('album_pic', $tmp, $where);
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @param array $conditionarr 条件数组 
     * @return bool 布尔类型的返回结果
     */
    public function updatebywhere($param, $conditionarr)
    {
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            //条件
            $condition_str = $this->_condition($conditionarr);
            //更新信息
            $tmp = array();
            foreach ($param as $k => $v) {
                $tmp[$k] = $v;
            }
            $result = db\mysqli::update('album_pic', $tmp, $condition_str);
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 删除分类
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id)
    {
        if (intval($id) > 0) {
            $where = " apic_id = '" . intval($id) . "'";
            $result = db\mysqli::delete('album_pic', $where);
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 删除上传图片信息
     * @param	mixed $id 删除上传图片记录编号
     */
    public function dropUploadById($id)
    {
        if (empty($id)) {
            return false;
        }
        $condition_str = ' 1=1 ';
        if (is_array($id) && count($id) > 0) {
            $idStr = implode(',', $id);
            $condition_str .= " and apic_id in({$idStr}) ";
        } else {
            $condition_str .= " and apic_id = {$id} ";
        }
        $result = db\mysqli::delete('album_pic', $condition_str);
        return $result;
    }
}