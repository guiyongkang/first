<?php
/**
 * 我的足迹
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class member_goodsbrowse extends mobileMemberControl {
    public function __construct(){
        parent::__construct();
    }

    /**
     * 我的足迹
     */
    public function browse_listOp() {
		$model_browse = model('goods_browse');
		$page_count = $model_browse->gettotalpage();
		$viewed_goods = $model_browse->getViewedGoodsList($this->member_info['member_id'], 20);
		foreach($viewed_goods as $key=>$value){
			$viewed_goods[$key]['goods_image_url'] = thumb($value, 360);
		}
		output_data(array('goodsbrowse_list'=>$viewed_goods),mobile_page($page_count));
    }
	
	
	/**
     * 清空浏览历史
     */
	public function browse_clearallOp() {
		$model_browse = model('goods_browse');
		$return = $model_browse->delGoodsbrowse(array('member_id'=>$this->member_info['member_id']));
		if($return){
			output_data('清空成功');
		}else{
			output_error('清空失败');
		}
	}
		

	protected function getMemberAndGradeInfo($is_return = false){
        $member_info = array();
        //会员详情及会员级别处理
        if($_SESSION['member_id']) {
            $model_member = model('member');
            $member_info = $model_member->getMemberInfoByID($_SESSION['member_id']);
            if ($member_info){
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
                $member_info = array_merge($member_info,$member_gradeinfo);
                $member_info['security_level'] = $model_member->getMemberSecurityLevel($member_info);
            }
        }
        if ($is_return == true){//返回会员信息
            return $member_info;
        } else {//输出会员信息
            Tpl::output('member_info',$member_info);
        }
    }
}
