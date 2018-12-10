<?php
/**
 * 买家相册模型
 *
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class sns_album extends core\model
{
    public function __construct()
    {
        parent::__construct('sns_albumpic');
    }
    public function getSnsAlbumClassDefault($member_id)
    {
        if (empty($member_id)) {
            return null;
        }
        $condition = array();
        $condition['member_id'] = $member_id;
        $condition['is_default'] = 1;
        $info = $this->table('sns_albumclass')->where($condition)->find();
        if (!empty($info)) {
            return $info['ac_id'];
        } else {
            return null;
        }
    }
}