<?php
/**
 * 元及元日志管理
 *
 */
namespace common\model;

use core;
use db;
defined('SAFE_CONST') or exit('Access Invalid!');
class points extends core\model
{
    /**
     * 操作元
     * @param  string $stage 操作阶段 regist(注册),login(登录),comments(评论),order(下单),system(系统),other(其他),pointorder(元礼品兑换),app(同步元兑换)
     * @param  array $insertarr 该数组可能包含信息 array('pl_memberid'=>'会员编号','pl_membername'=>'会员名称','pl_adminid'=>'管理员编号','pl_adminname'=>'管理员名称','pl_points'=>'元','pl_desc'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号','point_ordersn'=>'元兑换订单编号');
     * @param  bool $if_repeat 是否可以重复记录的信息,true可以重复记录，false不可以重复记录，默认为true
     * @return bool
     */
    function savePointsLog($stage, $insertarr, $if_repeat = true)
    {
        if (!$insertarr['pl_memberid']) {
            return false;
        }
        //记录原因文字
        switch ($stage) {
            case 'regist':
                if (empty($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '注册会员';
                }
                $insertarr['pl_points'] = intval(core\config::get('points_reg'));
                break;
            case 'login':
                if (empty($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '会员登录';
                }
                $insertarr['pl_points'] = intval(core\config::get('points_login'));
                break;
            case 'comments':
                if (empty($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '评论商品';
                }
                $insertarr['pl_points'] = intval(core\config::get('points_comments'));
                break;
            case 'order':
                if (empty($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '订单' . $insertarr['order_sn'] . '购物消费';
                }
                $insertarr['pl_points'] = 0;
                if (!empty($insertarr['orderprice'])) {
                    $insertarr['pl_points'] = intval($insertarr['orderprice'] / core\config::get('points_orderrate'));
                    if ($insertarr['pl_points'] > intval(core\config::get('points_ordermax'))) {
                        $insertarr['pl_points'] = intval(core\config::get('points_ordermax'));
                    }
                }
                //订单添加赠送元列
                $obj_order = model('order');
                $data = array();
                $data['order_pointscount'] = array('exp', 'order_pointscount+' . $insertarr['pl_points']);
                $obj_order->editOrderCommon($data, array('order_id' => $insertarr['order_id']));
                break;
            case 'system':
                break;
            case 'pointorder':
                if (empty($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '兑换礼品信息' . $insertarr['point_ordersn'] . '消耗元';
                }
                break;
            case 'app':
                if (empty($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = core\language::get('points_pointorderdesc_app');
                }
                //邀请元返利
                break;
            case 'signin':
                if (empty($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '签到得到元';
                }
                break;
            case 'inviter':
                if (empty($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '邀请新会员[' . $insertarr['invited'] . ']注册';
                }
                $insertarr['pl_points'] = intval(core\config::get('points_invite'));
                break;
            case 'rebate':
                if (empty($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '被邀请人[' . core\session::get('member_name') . ']消费';
                }
                $insertarr['pl_points'] = isset($insertarr['rebate_amount']) ? $insertarr['rebate_amount'] : 0;
                break;
            case 'other':
                break;
        }
        $save_sign = true;
        if ($if_repeat == false) {
            //检测是否有相关信息存在，如果没有，入库
            $condition['pl_memberid'] = $insertarr['pl_memberid'];
            $condition['pl_stage'] = $stage;
            $log_array = self::getPointsInfo($condition);
            if (!empty($log_array)) {
                $save_sign = false;
            }
        }
        if ($save_sign == false) {
            return true;
        }
        //新增日志
        $value_array = array();
        $value_array['pl_memberid'] = $insertarr['pl_memberid'];
        $value_array['pl_membername'] = $insertarr['pl_membername'];
        if (!empty($insertarr['pl_adminid'])) {
            $value_array['pl_adminid'] = $insertarr['pl_adminid'];
        }
        if (!empty($insertarr['pl_adminname'])) {
            $value_array['pl_adminname'] = $insertarr['pl_adminname'];
        }
        $value_array['pl_points'] = $insertarr['pl_points'];
        $value_array['pl_addtime'] = time();
        $value_array['pl_desc'] = $insertarr['pl_desc'];
        $value_array['pl_stage'] = $stage;
        $result = false;
        if (isset($value_array['pl_points']) && $value_array['pl_points'] != '0') {
            $result = self::addPointsLog($value_array);
        }
        if ($result) {
            //更新member内容
            $obj_member = model('member');
            $upmember_array = array();
            $upmember_array['member_points'] = array('exp', 'member_points+' . $insertarr['pl_points']);
            $obj_member->editMember(array('member_id' => $insertarr['pl_memberid']), $upmember_array);
            return true;
        } else {
            return false;
        }
    }
    /**
     * 添加元日志信息
     *
     * @param array $param 添加信息数组
     */
    public function addPointsLog($param)
    {
        if (empty($param)) {
            return false;
        }
        $result = db\mysqli::insert('points_log', $param);
        return $result;
    }
    /**
     * 元日志列表
     *
     * @param array $condition 条件数组
     * @param array $page   分页
     * @param array $field   查询字段
     * @param array $page   分页
     */
    public function getPointsLogList($condition, $page = '', $field = '*')
    {
        $condition_str = $this->getCondition($condition);
        $param = array();
        $param['table'] = 'points_log';
        $param['where'] = $condition_str;
        $param['field'] = $field;
        $param['order'] = isset($condition['order']) ? $condition['order'] : 'points_log.pl_id desc';
		if(isset($condition['limit'])){
			$param['limit'] = $condition['limit'];
		}
		if(isset($condition['group'])){
			$param['group'] = $condition['group'];
		}
        return db\mysqli::select($param, $page);
    }
    /**
     * 元日志详细信息
     *
     * @param array $condition 条件数组
     * @param array $field   查询字段
     */
    public function getPointsInfo($condition, $field = '*')
    {
        //得到条件语句
        $condition_str = $this->getCondition($condition);
        $array = array();
        $array['table'] = 'points_log';
        $array['where'] = $condition_str;
        $array['field'] = $field;
        $list = db\mysqli::select($array);
		if(empty($list)){
			return array();
		}else{
			return $list[0];
		}
    }
    /**
     * 将条件数组组合为SQL语句的条件部分
     *
     * @param	array $condition_array
     * @return	string
     */
    private function getCondition($condition_array)
    {
        $condition_sql = '';
        //元日志会员编号
        if (!empty($condition_array['pl_memberid'])) {
            $condition_sql .= " and points_log.pl_memberid = '{$condition_array['pl_memberid']}'";
        }
        //操作阶段
        if (!empty($condition_array['pl_stage'])) {
            $condition_sql .= " and points_log.pl_stage = '{$condition_array['pl_stage']}'";
        }
        //会员名称
        if (!empty($condition_array['pl_membername_like'])) {
            $condition_sql .= " and points_log.pl_membername like '%{$condition_array['pl_membername_like']}%'";
        }
        //管理员名称
        if (!empty($condition_array['pl_adminname_like'])) {
            $condition_sql .= " and points_log.pl_adminname like '%{$condition_array['pl_adminname_like']}%'";
        }
        //添加时间
        if (!empty($condition_array['saddtime'])) {
            $condition_sql .= " and points_log.pl_addtime >= '{$condition_array['saddtime']}'";
        }
        if (!empty($condition_array['eaddtime'])) {
            $condition_sql .= " and points_log.pl_addtime <= '{$condition_array['eaddtime']}'";
        }
        //描述
        if (!empty($condition_array['pl_desc_like'])) {
            $condition_sql .= " and points_log.pl_desc like '%{$condition_array['pl_desc_like']}%'";
        }
        return $condition_sql;
    }
}