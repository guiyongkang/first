<?php
namespace common\model;

use db;
use core;
defined('SAFE_CONST') or exit('Access Invalid!');
class adv
{
    /**
     * 新增广告位
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function ap_add($param)
    {
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            $tmp = array();
            foreach ($param as $k => $v) {
                $tmp[$k] = $v;
            }
            $result = db\mysqli::insert('adv_position', $tmp);
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 新增广告
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function adv_add($param)
    {
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            $tmp = array();
            foreach ($param as $k => $v) {
                $tmp[$k] = $v;
            }
            $result = db\mysqli::insert('adv', $tmp);
            // drop cache
            $apId = (int) $tmp['ap_id'];
            dkcache("adv/{$apId}");
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 删除一条广告
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function adv_del($adv_id)
    {
        $adv_array = model()->table('adv')->find($adv_id);
        if ($adv_array) {
            // drop cache
            $apId = (int) $adv_array['ap_id'];
            dkcache("adv/{$apId}");
        }
        $where = "where adv_id = '{$adv_id}'";
        $result = db\mysqli::delete("adv", $where);
        return $result;
    }
    /**
     * 删除一个广告位
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function ap_del($ap_id)
    {
        // drop cache
        $apId = (int) $ap_id;
        dkcache("adv/{$apId}");
        $where = "where ap_id = '{$ap_id}'";
        $result = db\mysqli::delete("adv_position", $where);
        return $result;
    }
    /**
     * 获取广告位列表
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getApList($condition = array(), $page = '', $orderby = '')
    {
        $param = array();
        $param['table'] = 'adv_position';
        $param['where'] = $this->getCondition($condition);
        if ($orderby == '') {
            $param['order'] = 'ap_id desc';
        } else {
            $param['order'] = $orderby;
        }
        return db\mysqli::select($param, $page);
    }
    /**
     * 根据条件查询多条记录
     *
     * @param array $condition 查询条件
     * @param obj $page 分页对象
     * @return array 二维数组
     */
    public function getList($condition = array(), $page = '', $limit = '', $orderby = '')
    {
        $param = array();
        $param['table'] = 'adv';
        $param['field'] = isset($condition['field']) ? $condition['field'] : '*';
        $param['where'] = $this->getCondition($condition);
        if ($orderby == '') {
            $param['order'] = 'slide_sort, adv_id desc';
        } else {
            $param['order'] = $orderby;
        }
        $param['limit'] = $limit;
        return db\mysqli::select($param, $page);
    }
    /**
     * 根据id查询一条记录
     *
     * @param int $id 广告id
     * @return array 一维数组
     */
    public function getOneById($id)
    {
        $param = array();
        $param['table'] = 'adv';
        $param['field'] = 'adv_id';
        $param['value'] = $id;
        return db\mysqli::getRow($param);
    }
    /**
     * 更新记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function update($param)
    {
        $adv_array = model()->table('adv')->find($param['adv_id']);
        if ($adv_array) {
            // drop cache
            $apId = (int) $adv_array['ap_id'];
            dkcache("adv/{$apId}");
        }
        return db\mysqli::update('adv', $param, "adv_id='{$param['adv_id']}'");
    }
    /**
     * 更新广告位记录
     *
     * @param array $param 更新内容
     * @return bool
     */
    public function ap_update($param)
    {
        $apId = (int) $param['ap_id'];
        dkcache("adv/{$apId}");
        return db\mysqli::update('adv_position', $param, "ap_id='{$param['ap_id']}'");
    }
    /**
     * 构造查询条件
     *
     * @param array $condition
     * @return string
     */
    private function getCondition($condition = array())
    {
        $return = '';
        $time = time();
        if (!empty($condition['adv_type'])) {
            $return .= " and adv_type='" . $condition['adv_type'] . "'";
        }
        if (!empty($condition['adv_code'])) {
            $return .= " and adv_code='" . $condition['adv_code'] . "'";
        }
        if (!empty($condition['no_adv_type'])) {
            $return .= " and adv_type!='" . $condition['no_adv_type'] . "'";
        }
        if (!empty($condition['adv_state'])) {
            $return .= " and adv_state='" . $condition['adv_state'] . "'";
        }
        if (!empty($condition['ap_id'])) {
            $return .= " and ap_id='" . $condition['ap_id'] . "'";
        }
        if (!empty($condition['adv_id'])) {
            $return .= " and adv_id='" . $condition['adv_id'] . "'";
        }
        if (isset($condition['adv_end_date']) && $condition['adv_end_date'] == 'over') {
            $return .= " and adv_end_date<'" . $time . "'";
        }
        if (isset($condition['adv_end_date']) && $condition['adv_end_date'] == 'notover') {
            $return .= " and adv_end_date>'" . $time . "'";
        }
        if (!empty($condition['ap_name'])) {
            $return .= " and ap_name like '%" . $condition['ap_name'] . "%'";
        }
        if (!empty($condition['adv_title'])) {
            $return .= " and adv_title like '%" . $condition['adv_title'] . "%'";
        }
        if (!empty($condition['add_time_from'])) {
            $return .= " and adv_start_date > '{$condition['add_time_from']}'";
        }
        if (!empty($condition['add_time_to'])) {
            $return .= " and adv_end_date < '{$condition['add_time_to']}'";
        }
        if (!empty($condition['member_name'])) {
            $return .= " and member_name ='" . $condition['member_name'] . "'";
        }
        if (!empty($condition['is_allow'])) {
            $return .= " and is_allow = '" . $condition['is_allow'] . "' ";
        }
        if (!empty($condition['buy_style'])) {
            $return .= " and buy_style = '" . $condition['buy_style'] . "' ";
        }
        if (!empty($condition['adv_start_date']) && $condition['adv_start_date'] == 'nowshow') {
            $return .= " and adv_start_date <'" . $time . "'";
        }
        if (!empty($condition['member_id'])) {
            $return .= " and member_id = '" . $condition['member_id'] . "'";
        }
        if (!empty($condition['is_use'])) {
            $return .= " and is_use = '" . $condition['is_use'] . "' ";
        }
        if (!empty($condition['adv_buy_id'])) {
            $return .= " and ap_id not in (" . $condition['adv_buy_id'] . ")";
        }
        return $return;
    }
    public function delapcache($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        dkcache("adv/{$id}");
        return true;
    }
    /**
     * 广告
     *
     * @return array
     */
    public function makeApAllCache()
    {
        if (core\config::get('cache_open')) {
            // *kcache() doesnt support iterating on keys
        } else {
            delCacheFile('adv');
        }
        $model = model();
        $ap_list = $model->table('adv_position')->where(array('is_use' => 1))->select();
        $adv_list = $model->table('adv')->where(array('adv_end_date' => array('gt', time())))->order('slide_sort, adv_id desc')->select();
        $array = array();
        foreach ((array) $ap_list as $v) {
            foreach ((array) $adv_list as $xv) {
                if ($v['ap_id'] == $xv['ap_id']) {
                    $v['adv_list'][] = $xv;
                }
            }
            // 写入缓存
            $apId = (int) $v['ap_id'];
            if (core\config::get('cache_open')) {
                wkcache("adv/{$apId}", $v);
            } else {
                write_file(BASE_DATA_PATH . '/cache/adv/' . $apId . '.php', $v);
            }
        }
    }
    public function getApById($apId)
    {
        $apId = (int) $apId;
        return rkcache("adv/{$apId}", array($this, 'getApByCacheId'));
    }
    /**
     * 通过缓存id获取广告，生成缓存时使用
     *
     * @param $apCacheId 格式为 adv/{ap_id}
     */
    public function getApByCacheId($apCacheId)
    {
        $apId = substr($apCacheId, strlen('adv/'));
        return $this->getAp($apId);
    }
    /**
     * 生成广告位
     *
     * @param int $ap_id
     */
    protected function getAp($ap_id)
    {
        $model = model();
        $ap_info = $model->table('adv_position')->find($ap_id);
        $ap_info['adv_list'] = $model->table('adv')->where(array('ap_id' => $ap_id, 'adv_end_date' => array('gt', time())))->order('slide_sort, adv_id desc')->select();
        return $ap_info;
    }
    /**
     * 删除缓存
     */
    public function dropApCacheByAdvIds($advIds)
    {
        $apIds = array_keys((array) model()->table('adv')->field('ap_id')->where(array('adv_id' => array('in', (array) $advIds)))->key('ap_id')->select());
        foreach ($apIds as $apId) {
            $apId = (int) $apId;
            dkcache("adv/{$apId}");
        }
    }
}