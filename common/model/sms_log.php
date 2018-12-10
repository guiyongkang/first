<?php
/**
 * 手机短信记录  
 *
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class sms_log extends core\model
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 增加短信记录
     *
     * @param
     * @return int
     */
    public function addSms($log_array)
    {
        $log_id = $this->table('sms_log')->insert($log_array);
        return $log_id;
    }
    /**
     * 查询单条记录
     *
     * @param
     * @return array
     */
    public function getSmsInfo($condition)
    {
        if (empty($condition)) {
            return false;
        }
        $result = $this->table('sms_log')->where($condition)->order('log_id desc')->find();
        return $result;
    }
    /**
     * 查询记录
     *
     * @param
     * @return array
     */
    public function getSmsList($condition = array(), $page = '', $limit = '', $order = 'log_id desc')
    {
        $result = $this->table('sms_log')->where($condition)->page($page)->limit($limit)->order($order)->select();
        return $result;
    }
}