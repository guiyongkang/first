<?php
/**
 * 地区
 *
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class area extends mobileHomeControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function indexOp()
    {
        $this->area_listOp();
    }
    /**
     * 地区列表
     */
    public function area_listOp()
    {
        $area_id = isset($_GET['area_id']) ? intval($_GET['area_id']) : 0;
        $model_area = model('area');
        $condition = array();
        if ($area_id > 0) {
            $condition['area_parent_id'] = $area_id;
        } else {
            $condition['area_deep'] = 1;
        }
        $area_list = $model_area->getAreaList($condition, 'area_id,area_name');
        output_data(array('area_list' => $area_list));
    }
}