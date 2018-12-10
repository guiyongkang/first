<?php
/**
 * 商品栏目管理
 *
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class goods extends SystemControl
{
    const EXPORT_SIZE = 5000;
    public function __construct()
    {
        parent::__construct();
        core\language::read('goods');
    }
    /**
     * 商品设置
     */
    public function goods_setOp()
    {
        $model_setting = model('setting');
        if (chksubmit()) {
            $update_array = array();
            $update_array['goods_verify'] = $_POST['goods_verify'];
            $result = $model_setting->updateSetting($update_array);
            if ($result === true) {
                $this->log(lang('nc_edit,nc_goods_set'), 1);
                success(lang('nc_common_save_succ'));
            } else {
                $this->log(lang('nc_edit,nc_goods_set'), 0);
                error(lang('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        core\tpl::output('list_setting', $list_setting);
        core\tpl::showpage('goods.setting');
    }
    /**
     * 设置全返
     */
    public function goodsr_setOp()
    {
      
        $common_id = $_GET['commonid'];            
    
        if ($common_id <= 0) {
            error('参数错误');
        }
        $model_goods = model('goods');
        $goodscommon_info = $model_goods->getGoodeCommonInfoByID($common_id);
        file_put_contents(dirname(__FILE__).'/tian1.txt','sql==>>111'.print_r($goodscommon_info,1)."\r\n", FILE_APPEND);
        if (empty($goodscommon_info) || $goodscommon_info['goods_lock'] == 1) { 
            error('参数错误');
        }
        if (chksubmit()) {
            $commonids =  $_POST['commonids'];           
            $update = array();  
            $update['points'] = $_POST['points'];
            $update['pointsdays'] = $_POST['pointsdays'];          
            
            $where = array();
            $where['goods_commonid'] = array('in', $commonids);
            model('goods')->editProducesLockUp($update, $where);
           // showDialog(lang('nc_common_save_succ'), 'reload', 'succ');
           // success(lang('nc_common_save_succ'));
            success(lang('nc_common_save_succ'));
        }       
        core\tpl::output('goodscommon_info', $goodscommon_info);
        core\tpl::showpage('goodsr.setting');
    }
    /**
     * 商品管理
     */
    public function goodsOp()
    {
        $model_goods = model('goods');
        /**
         * 处理商品分类
         */
        $choose_gcid = (isset($_REQUEST['choose_gcid']) && intval($_REQUEST['choose_gcid'])) > 0 ? $_REQUEST['choose_gcid'] : 0;
        $gccache_arr = model('goods_class')->getGoodsclassCache($choose_gcid, 3);
        core\tpl::output('gc_json', json_encode($gccache_arr['showclass']));
        core\tpl::output('gc_choose_json', json_encode($gccache_arr['choose_gcid']));
        /**
         * 查询条件
         */
        $where = array();
        if (!empty($_GET['search_goods_name'])) {
            $where['goods_name'] = array('like', '%' . trim($_GET['search_goods_name']) . '%');
        }
        if (isset($_GET['search_commonid']) && intval($_GET['search_commonid']) > 0) {
            $where['goods_commonid'] = intval($_GET['search_commonid']);
        }
        if (!empty($_GET['search_store_name'])) {
            $where['store_name'] = array('like', '%' . trim($_GET['search_store_name']) . '%');
        }
        if (isset($_GET['b_id']) && intval($_GET['b_id']) > 0) {
            $where['brand_id'] = intval($_GET['b_id']);
        }
        if ($choose_gcid > 0) {
            $where['gc_id_' . $gccache_arr['showclass'][$choose_gcid]['depth']] = $choose_gcid;
        }
        if (isset($_GET['search_state']) && in_array($_GET['search_state'], array('0', '1', '10'))) {
            $where['goods_state'] = $_GET['search_state'];
        }
        if (isset($_GET['search_verify']) && in_array($_GET['search_verify'], array('0', '1', '10'))) {
            $where['goods_verify'] = $_GET['search_verify'];
        }
		$goods_list = array();
		
		$_GET['type'] = isset($_GET['type']) ? $_GET['type'] : 'all';
		switch ($_GET['type']) {
			// 禁售
			case 'lockup':
				$goods_list = $model_goods->getGoodsCommonLockUpList($where);
				break;
				// 等待审核
			// 等待审核
			case 'waitverify':
				$goods_list = $model_goods->getGoodsCommonWaitVerifyList($where, '*', 10, 'goods_verify desc, goods_commonid desc');
				break;
				// 全部商品
			// 全部商品
			default:
				$goods_list = $model_goods->getGoodsCommonList($where);
				break;
		}
        core\tpl::output('goods_list', $goods_list);
        core\tpl::output('page', $model_goods->showpage(2));
        $storage_array = $model_goods->calculateStorage($goods_list);
        core\tpl::output('storage_array', $storage_array);
        // 品牌
        $brand_list = model('brand')->getBrandPassedList(array());
        core\tpl::output('search', $_GET);
        core\tpl::output('brand_list', $brand_list);
        core\tpl::output('state', array('1' => '出售中', '0' => '仓库中', '10' => '违规下架'));
        core\tpl::output('verify', array('1' => '通过', '0' => '未通过', '10' => '等待审核'));
        core\tpl::output('ownShopIds', array_fill_keys(model('store')->getOwnShopIds(), true));
		switch ($_GET['type']) {
			// 禁售
			case 'lockup':
				core\tpl::showpage('goods.close');
				break;
				// 等待审核
			// 等待审核
			case 'waitverify':
				core\tpl::showpage('goods.verify');
				break;
				// 全部商品
			// 全部商品
			default:
				core\tpl::showpage('goods.index');
				break;
		}
    }
    /**
     * 违规下架
     */
    public function goods_lockupOp()
    {
        if (chksubmit()) {
            $commonids = $_POST['commonids'];
            $commonid_array = explode(',', $commonids);
            foreach ($commonid_array as $value) {
                if (!is_numeric($value)) {
                    showDialog(lang('nc_common_op_fail'), 'reload');
                }
            }
            $update = array();
            $update['goods_stateremark'] = trim($_POST['close_reason']);
            $where = array();
            $where['goods_commonid'] = array('in', $commonid_array);
            model('goods')->editProducesLockUp($update, $where);
            showDialog(lang('nc_common_op_succ'), 'reload', 'succ');
        }
        core\tpl::output('commonids', $_GET['id']);
        core\tpl::showpage('goods.close_remark', 'null_layout');
    }
    /**
     * 删除商品
     */
    public function goods_delOp()
    {
        $common_id = intval($_GET['goods_id']);
        if ($common_id <= 0) {
            showDialog(lang('nc_common_op_fail'), 'reload');
        }
        model('goods')->delGoodsAll(array('goods_commonid' => $common_id));
        showDialog(lang('nc_common_op_succ'), 'reload', 'succ');
    }
    /**
     * 审核商品
     */
    public function goods_verifyOp()
    {
        if (chksubmit()) {
            $commonids = $_POST['commonids'];
            $commonid_array = explode(',', $commonids);
            foreach ($commonid_array as $value) {
                if (!is_numeric($value)) {
                    showDialog(lang('nc_common_op_fail'), 'reload');
                }
            }
            $update2 = array();
            $update2['goods_verify'] = intval($_POST['verify_state']);
            $update1 = array();
            $update1['goods_verifyremark'] = trim($_POST['verify_reason']);
            $update1 = array_merge($update1, $update2);
            $where = array();
            $where['goods_commonid'] = array('in', $commonid_array);
            $model_goods = model('goods');
            if (intval($_POST['verify_state']) == 0) {
                $model_goods->editProducesVerifyFail($where, $update1, $update2);
            } else {
                $model_goods->editProduces($where, $update1, $update2);
            }
            showDialog(lang('nc_common_op_succ'), 'reload', 'succ');
        }
        core\tpl::output('commonids', $_GET['id']);
        core\tpl::showpage('goods.verify_remark', 'null_layout');
    }
    /**
     * ajax获取商品列表
     */
    public function get_goods_list_ajaxOp()
    {
        $commonid = $_GET['commonid'];
        if ($commonid <= 0) {
            echo 'false';
            exit;
        }
        $model_goods = model('goods');
        $goodscommon_list = $model_goods->getGoodeCommonInfoByID($commonid, 'spec_name');
        if (empty($goodscommon_list)) {
            echo 'false';
            exit;
        }
        $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $commonid), 'goods_id,goods_spec,store_id,goods_price,goods_serial,goods_storage,goods_image');
        if (empty($goods_list)) {
            echo 'false';
            exit;
        }
        $spec_name = array_values((array) unserialize($goodscommon_list['spec_name']));
        foreach ($goods_list as $key => $val) {
            $goods_spec = array_values((array) unserialize($val['goods_spec']));
            $spec_array = array();
            foreach ($goods_spec as $k => $v) {
                $spec_array[] = '<div class="goods_spec">' . $spec_name[$k] . lang('nc_colon') . '<em title="' . $v . '">' . $v . '</em>' . '</div>';
            }
            $goods_list[$key]['goods_image'] = thumb($val, '60');
            $goods_list[$key]['goods_spec'] = implode('', $spec_array);
        }
        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK') {
            core\language::getUTF8($goods_list);
        }
        echo json_encode($goods_list);
    }
}