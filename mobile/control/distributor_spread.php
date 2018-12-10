<?php
/**
 * 我的推广二维码
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');

class distributor_spread extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
		if (empty($this->member_info['is_distributor'])) {
            output_error('您还不是分销商', array('distributor' => '0'));
        }
	}

    /**
     * 我的推广二维码(网页版)
     */
    public function spread_wapOp() {
		
		$imgpath_poster = getMemberPosterImgForID($this->member_info['member_id'],'wap');
		
		if(!$imgpath_poster){//生成海报
			$model_distributor = model('distributor');
			$dis_setting = $model_distributor->getInfoOne('distributor_other_setting','');
			$imgpath_poster = logic('qrcode')->create_poster_wap($this->member_info['member_id'], $this->member_info['member_name'], $dis_setting['qrcode_bg']);
		}
		
		if(!$imgpath_poster){
			output_data(array('imgpath' => ''));
		}
        output_data(array('imgpath' => $imgpath_poster));
    }
	
	/**
     * 我的推广二维码(微信版)
     */
    public function spread_weixinOp() {
		$imgpath_poster = getMemberPosterImgForID($this->member_info['member_id'],'weixin');
		
		if(!$imgpath_poster){//生成海报
			$model_distributor = model('distributor');
			$dis_setting = $model_distributor->getInfoOne('distributor_other_setting','');
			$imgpath_poster = logic('qrcode')->create_poster_weixin($this->member_info['member_id'], $this->member_info['member_name'], $dis_setting['qrcode_bg']);
		}
		
		if($imgpath_poster=='' || $imgpath_poster=='fail'){
			output_data(array('imgpath' => ''));
		}
        output_data(array('imgpath' => $imgpath_poster));
    }
}
