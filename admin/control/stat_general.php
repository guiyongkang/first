<?php
/**
 * 统计概述
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class stat_general extends SystemControl
{
    private $links = array(array('url' => 'act=stat_general&op=general', 'lang' => 'stat_generalindex'), /*array('url' => 'act=stat_general&op=setting', 'lang' => 'stat_goodspricerange'), array('url' => 'act=stat_general&op=orderprange', 'lang' => 'stat_orderpricerange')*/);
    public function __construct()
    {
        parent::__construct();
        core\language::read('stat');
        require_cache(BASE_CORE_PATH . DS . 'framework' . DS . 'function' . DS . 'statistics.php');
    }
    /**
     * 促销分析
     */
    public function generalOp()
    {
        $model = model('distributor');
        $sales = $order = $dis = $public = $commission = $hongbao = array();
		
		//获取总销售额和总订单数
		$result = $model->getInfoOne('order','', 'SUM(order_amount) as money,COUNT(order_id) as num');
		if($result['num']==0){
			$sales = array(
				'amount'=>0,
				'nopay'=>0,
				'payed'=>0,
				'return'=>0,
				'out'=>0
			);
			$order = array(
				'amount'=>0,
				'nopay'=>0,
				'payed'=>0,
				'return'=>0,
				'out'=>0
			);
			unset($result);
		}else{
			$sales['amount'] = $result['money'];
			$order['amount'] = $result['num'];
			unset($result);
			
			//未付款
			$result = $model->getInfoOne('order',array('order_state'=>10), 'SUM(order_amount) as money,COUNT(order_id) as num');
			$sales['nopay'] = $result['money'] ? $result['money'] : 0;
			$order['nopay'] = $result['num'];
			unset($result);
			
			//已付款
			$result = $model->getInfoOne('order',array('order_state'=>array('egt',20)), 'SUM(order_amount) as money,COUNT(order_id) as num');
			$sales['payed'] = $result['money'] ? $result['money'] : 0;
			$order['payed'] = $result['num'];
			unset($result);
			
			//已退款
			$result = $model->getInfoOne('refund_return','', 'SUM(refund_amount) as money,COUNT(refund_id) as num');
			$sales['return'] = $result['money'] ? $result['money'] : 0;
			$order['return'] = $result['num'];
			unset($result);
			
			//已取消
			$result = $model->getInfoOne('order',array('order_state'=>0), 'SUM(order_amount) as money,COUNT(order_id) as num');
			$sales['out'] = $result['money'] ? $result['money'] : 0;
			$order['out'] = $result['num'];
			unset($result);
		}
		
		//分销商总数
		$result = $model->getInfoOne('distributor_account','', 'COUNT(distributor_id) as num');
		if($result['num'] == 0){
			$dis['amount'] = 0;
			$dis['today'] = 0;
			$dis['month'] = 0;
			unset($result);
		}else{
			$dis['amount'] = $result['num'];
			unset($result);
			//今日分销商
			$time_start = strtotime(date('Y-m-d 00:00:00',time()));
			$result = $model->getInfoOne('distributor_account',array('addtime'=>array('between',array($time_start,time()))), 'COUNT(distributor_id) as num');
			$dis['today'] = $result['num'];
			unset($result);
			//本月分销商
			$time_start = strtotime(date('Y-m-01 00:00:00',time()));
			$result = $model->getInfoOne('distributor_account',array('addtime'=>array('between',array($time_start,time()))), 'COUNT(distributor_id) as num');
			$dis['month'] = $result['num'];
			unset($result);
		}
		
		//获取公排位置总数和级最大别数
		$result = $model->getInfoOne('distributor_gp','', 'COUNT(ralate_id) as num,MAX(distributor_y) as level');
		if($result['num'] == 0){
			$public['amount'] = 0;
			$public['maxlevel'] = 0;
			$public['member'] = 0;
			unset($result);
		}else{
			$public['amount'] = $result['num'];
			$public['maxlevel'] = $result['level'];
			unset($result);
			$public['member'] = $dis['amount'];
			unset($result);
		}
		
		//获取佣金总额
		$result = $model->getInfoOne('distributor_goodsrecord_detail','', 'SUM(detail_bonus) as money');
		if(!$result['money']){
			$commission['amount'] = 0;
			$commission['nopay'] = 0;
			$commission['payed'] = 0;
			$commission['complate'] = 0;
			unset($result);
		}else{
			$commission['amount'] = $result['money'];
			unset($result);
			
			//获取未付款佣金
			$result = $model->getInfoOne('distributor_goodsrecord_detail',array('detail_status'=>10), 'SUM(detail_bonus) as money');
			$commission['nopay'] = $result['money'] ? $result['money'] : 0;
			unset($result);
			
			//获取已付款佣金
			$result = $model->getInfoOne('distributor_goodsrecord_detail',array('detail_status'=>array('egt',20)), 'SUM(detail_bonus) as money');
			$commission['payed'] = $result['money'] ? $result['money'] : 0;
			unset($result);
			
			//获取已到账佣金
			$result = $model->getInfoOne('distributor_goodsrecord_detail',array('detail_status'=>40), 'SUM(detail_bonus) as money');
			$commission['complate'] = $result['money'] ? $result['money'] : 0;
			unset($result);
		}
		
		//获取公排红包总额
		$result = $model->getInfoOne('distributor_gp_detail','', 'SUM(detail_bonus) as money');
		if(!$result['money']){
			$hongbao['amount'] = 0;
			$hongbao['level'] = 0;
			$hongbao['invite'] = 0;
			$hongbao['parent'] = 0;
			$hongbao['thankful'] = 0;
			unset($result);
		}else{
			$hongbao['amount'] = $result['money'];
			unset($result);
			
			//获取级别奖红包
			$result = $model->getInfoOne('distributor_gp_detail',array('detail_type'=>'level'), 'SUM(detail_bonus) as money');
			$hongbao['level'] = $result['money'] ? $result['money'] : 0;
			unset($result);
			
			//获取推荐奖红包
			$result = $model->getInfoOne('distributor_gp_detail',array('detail_type'=>'invite'), 'SUM(detail_bonus) as money');
			$hongbao['invite'] = $result['money'] ? $result['money'] : 0;
			unset($result);
			
			//获取见点奖红包
			$result = $model->getInfoOne('distributor_gp_detail',array('detail_type'=>'parent'), 'SUM(detail_bonus) as money');
			$hongbao['parent'] = $result['money'] ? $result['money'] : 0;
			unset($result);
			
			//获取感恩奖红包
			$result = $model->getInfoOne('distributor_gp_detail',array('detail_type'=>'thankful'), 'SUM(detail_bonus) as money');
			$hongbao['thankful'] = $result['money'] ? $result['money'] : 0;
			unset($result);
		}
		core\tpl::output('sales', $sales);
		core\tpl::output('order', $order);
		core\tpl::output('dis', $dis);
		core\tpl::output('public', $public);
		core\tpl::output('commission', $commission);
		core\tpl::output('hongbao', $hongbao);
        core\tpl::output('top_link', $this->sublink($this->links, 'general'));
        core\tpl::showpage('stat.general.index');
    }
    /**
     * 统计设置
     */
    public function settingOp()
    {
        $model_setting = model('setting');
        if (chksubmit()) {
            $update_array = $pricerange_arr = array();
            if (!empty($_POST['pricerange'])) {
                foreach ((array) $_POST['pricerange'] as $k => $v) {
                    $pricerange_arr[] = $v;
                }
                $update_array['stat_pricerange'] = serialize($pricerange_arr);
            } else {
                $update_array['stat_pricerange'] = '';
            }
            $result = $model_setting->updateSetting($update_array);
            if ($result === true) {
                $this->log(lang('nc_edit,stat_setting'), 1);
                success(lang('nc_common_save_succ'));
            } else {
                $this->log(lang('nc_edit,stat_setting'), 0);
                error(lang('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $list_setting['stat_pricerange'] = unserialize($list_setting['stat_pricerange']);
        core\tpl::output('list_setting', $list_setting);
        core\tpl::output('top_link', $this->sublink($this->links, 'setting'));
        core\tpl::showpage('stat.setting');
    }
    /**
     * 统计设置
     */
    public function orderprangeOp()
    {
        $model_setting = model('setting');
        if (chksubmit()) {
            $update_array = array();
            if ($_POST['pricerange']) {
                foreach ((array) $_POST['pricerange'] as $k => $v) {
                    $pricerange_arr[] = $v;
                }
                $update_array['stat_orderpricerange'] = serialize($pricerange_arr);
            } else {
                $update_array['stat_orderpricerange'] = '';
            }
            $result = $model_setting->updateSetting($update_array);
            if ($result === true) {
                $this->log(lang('nc_edit,stat_setting'), 1);
                success(lang('nc_common_save_succ'));
            } else {
                $this->log(lang('nc_edit,stat_setting'), 0);
                error(lang('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $list_setting['stat_orderpricerange'] = unserialize($list_setting['stat_orderpricerange']);
        core\tpl::output('list_setting', $list_setting);
        core\tpl::output('top_link', $this->sublink($this->links, 'orderprange'));
        core\tpl::showpage('stat.setting.orderprange');
    }
}