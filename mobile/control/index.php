<?php
namespace mobile\control;
use core;

defined('SAFE_CONST') or exit('Access Invalid!');
class index extends mobileHomeControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 首页
     */
    public function indexOp()
    {
        $model_mb_special = model('mb_special');
        $data = $model_mb_special->getMbSpecialIndex();
		if(!empty($_GET['type'])){
			$this->_output_special($data, $_GET['type']);
		}else{
			$this->_output_special($data);
		}
        
    }
	public function index_moduleOp()
	{
		$index_module = model('index_module');
		$condition = array();
		$condition['status'] = 1;
        $list = $index_module->field('*')->where($condition)->order('sort asc,id asc')->select();
		foreach($list as $k => $v){
			$list[$k]['bg_img'] = BASE_SITE_URL . DS . DIR_UPLOAD . DS . ATTACH_COMMON . DS . 'index_icon' . DS . $v['bg_img'];
		}
		output_data($list);
	}
    /**
     * 专题
     */
    public function specialOp()
    {
        $model_mb_special = model('mb_special');
        $data = $model_mb_special->getMbSpecialItemUsableListByID($_GET['special_id']);
        $item_list = $model_mb_special->getMbSpecialInfo($_GET['special_id']);
        $item_list['list'] = $data;
        $this->_output_special($item_list, isset($_GET['type']) ? $_GET['type'] : '', $_GET['special_id']);
    }
    /**
     * 输出专题
     */
    private function _output_special($data, $type = 'json', $special_id = 0)
    {
        $model_special = model('mb_special');
        if (isset($_GET['type']) && $_GET['type'] == 'html') {
            $html_path = $model_special->getMbSpecialHtmlPath($special_id);
            if (!is_file($html_path)) {
                ob_start();
                core\tpl::output('list', $data);
                core\tpl::showpage('mb_special');
                file_put_contents($html_path, ob_get_clean());
            }
            header('Location: ' . $model_special->getMbSpecialHtmlUrl($special_id));
            die;
        } else {
            output_data($data);
        }
    }
    /**
     * android客户端版本号
     */
    public function apk_versionOp()
    {
        $version = core\config::get('mobile_apk_version');
        $url = core\config::get('mobile_apk');
        if (empty($version)) {
            $version = '';
        }
        if (empty($url)) {
            $url = '';
        }
        output_data(array('version' => $version, 'url' => $url));
    }
    /**
     * 默认搜索词列表
     */
    public function search_key_listOp()
    {
        $list = explode(',', core\config::get('hot_search'));
        if (!$list || !is_array($list)) {
            $list = array();
        }
        if (!empty($_COOKIE['hisSearch'])) {
            $his_search_list = explode('~', $_COOKIE['hisSearch']);
        }
        if (!isset($his_search_list) || !is_array($his_search_list)) {
            $his_search_list = array();
        }
        output_data(array('list' => $list, 'his_list' => $his_search_list));
    }
    /**
     * 热门搜索列表
     */
    public function search_hot_infoOp()
    {
		$rec_search_list = array();
        if (core\config::get('rec_search')) {
            $rec_search_list = unserialize(core\config::get('rec_search'));
        }
		if($rec_search_list){
			$result = $rec_search_list[array_rand($rec_search_list)];
		}else{
			$result = array();
		}
        
        output_data(array('hot_info' => $result));
    }
    /**
     * 高级搜索
     */
    public function search_advOp()
    {
        $gc_id = isset($_GET['gc_id']) ? intval($_GET['gc_id']) : 0;
        $area_list = model('area')->getAreaList(array('area_deep' => 1), 'area_id,area_name');
		$_tmp = array();
        if (core\config::get('contract_allow') == 1) {
            $contract_list = model('contract')->getContractItemByCache();
            $i = 0;
            foreach ($contract_list as $k => $v) {
                $_tmp[$i]['id'] = $v['cti_id'];
                $_tmp[$i]['name'] = $v['cti_name'];
                $i++;
            }
        }
        output_data(array('area_list' => $area_list?: array(), 'contract_list' => $_tmp));
    }
}