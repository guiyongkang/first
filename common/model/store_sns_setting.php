<?php
/**
 * 店铺动态自动发布
 *
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class store_sns_setting extends core\model
{
    public function __construct()
    {
        parent::__construct('store_sns_setting');
    }
    /**
     * 获取单条动态设置设置信息
     * 
     * @param unknown $condition
     * @param string $field
     * @return array
     */
    public function getStoreSnsSettingInfo($condition, $field = '*')
    {
        return $this->field($field)->where($condition)->find();
    }
    /**
     * 保存店铺动态设置
     * 
     * @param unknown $insert
     * @return boolean
     */
    public function saveStoreSnsSetting($insert)
    {
        return $this->insert($insert);
    }
}