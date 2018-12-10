<?php
/**
 * 我的商城
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class member_index extends mobileMemberControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function indexOp()
    {
        $member_info = array();
		$member_info['user_id'] = $this->member_info['member_id'];
        $member_info['user_name'] = $this->member_info['member_name'];
        $member_info['avator'] = getMemberAvatarForID($this->member_info['member_id']);
        $member_info['point'] = $this->member_info['member_points'];
		$member_info['is_distributor'] = $this->member_info['is_distributor'];
        $member_gradeinfo = model('member')->getOneMemberGrade(intval($this->member_info['member_exppoints']));
        $member_info['level_name'] = $member_gradeinfo['level_name'];
        $member_info['favorites_store'] = model('favorites')->getStoreFavoritesCountByMemberId($this->member_info['member_id']);
        $member_info['favorites_goods'] = model('favorites')->getGoodsFavoritesCountByMemberId($this->member_info['member_id']);
		
		//分销级别
		$model_dis = model('distributor');
		$member_info['distributor_level'] = '';
		
		$member_info['team_level'] = '暂无级别';
		$member_info['team_commission'] = 0;
		if($this->member_info['is_distributor']==1){
			$result = $model_dis->getInfoOne('distributor_account',array('member_id'=>$this->member_info['member_id']), 'level_id,team_id');
			if(!empty($result['level_id'])){
				$result1 = $model_dis->getInfoOne('distributor_level',array('level_id'=>$result['level_id']), 'level_name');
				if(!empty($result1['level_name'])){
					$member_info['distributor_level'] = $result1['level_name'];
				}
			}
			
			//股东相关
			if(!empty($result['team_id'])){
				$result1 = model('distributor')->getInfoOne('distributor_team',array('team_id'=>$result['team_id']), 'team_name');
				if(!empty($result1['team_name'])){
					$member_info['team_level'] = $result1['team_name'];
					
					unset($result1);
					//股东分红
					$result1 = model('distributor')->getInfoOne('distributor_fenhong',array('member_id'=>$this->member_info['member_id']), 'SUM(detail_bonus) as money');
					$member_info['team_commission'] = empty($result1['money']) ? 0 : $result1['money'];
				}
			}
		}
		
		
		//获得公排分区列表
		$result = $model_dis->getInfoList('distributor_gp_area','','','item_id asc','item_id,item_name');
		$public_area = array();
		foreach($result as $r){
			$public_area[] = $r;
		}
		unset($result);
		
        output_data(array('member_info' => $member_info, 'public_area'=>$public_area));
        // 交易提醒
        $model_order = model('order');
        $member_info['order_nopay_count'] = $model_order->getOrderCountByID('buyer', $this->member_info['member_id'], 'NewCount');
        $member_info['order_noreceipt_count'] = $model_order->getOrderCountByID('buyer', $this->member_info['member_id'], 'SendCount');
        $member_info['order_notakes_count'] = $model_order->getOrderCountByID('buyer', $this->member_info['member_id'], 'TakesCount');
        $member_info['order_noeval_count'] = $model_order->getOrderCountByID('buyer', $this->member_info['member_id'], 'EvalCount');
        // 售前退款
        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['refund_state'] = array('lt', 3);
        $member_info['return'] = model('refund_return')->getRefundReturnCount($condition);
    }
    public function my_assetOp()
    {
        $param = $_GET;
        $fields_arr = array('point', 'predepoit', 'available_rc_balance', 'redpacket', 'voucher');
        $fields_str = isset($param['fields']) ? trim($param['fields']) : '';
        if ($fields_str) {
            $fields_arr = explode(',', $fields_str);
        }
        $member_info = array();
        if (in_array('point', $fields_arr)) {
            $member_info['point'] = $this->member_info['member_points'];
        }
        if (in_array('predepoit', $fields_arr)) {
            $member_info['predepoit'] = $this->member_info['available_predeposit'];
        }
        if (in_array('available_rc_balance', $fields_arr)) {
            $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
        }
        if (in_array('redpacket', $fields_arr)) {
            //$member_info['redpacket'] = model('red_packet')->getCurrentAvailableRedpacketCount($this->member_info['member_id']);
        }
        if (in_array('voucher', $fields_arr)) {
            $member_info['voucher'] = model('voucher')->getCurrentAvailableVoucherCount($this->member_info['member_id']);
        }
        output_data($member_info);
    }
}