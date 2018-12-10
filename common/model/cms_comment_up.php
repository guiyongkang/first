<?php
/**
 * CMS评论顶模型
 *
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class cms_comment_up extends core\model
{
    public function __construct()
    {
        parent::__construct('cms_comment_up');
    }
    /**
     * 读取列表 
     * @param array $condition
     *
     */
    public function getList($condition, $page = '', $order = '', $field = '*')
    {
        $result = $this->table('cms_comment_up')->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
    }
    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getOne($condition)
    {
        $result = $this->where($condition)->find();
        return $result;
    }
    /*
     *  判断是否存在 
     *  @param array $condition
     *
     */
    public function isExist($condition)
    {
        $result = $this->getOne($condition);
        if (empty($result)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    /*
     * 增加 
     * @param array $param
     * @return bool
     */
    public function save($param)
    {
        return $this->insert($param);
    }
    /*
     * 增加 
     * @param array $param
     * @return bool
     */
    public function saveAll($param)
    {
        return $this->insertAll($param);
    }
    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
    public function modify($update, $condition)
    {
        return $this->where($condition)->update($update);
    }
    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function drop($condition)
    {
        return $this->where($condition)->delete();
    }
}