<?php
/**
 * 清理缓存
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class cache extends SystemControl
{
    //protected $cacheItems = array('setting', 'seo', 'groupbuy_price', 'nav', 'express', 'store_class', 'store_grade', 'store_msg_tpl', 'member_msg_tpl', 'consult_type', 'circle_level');
    protected $cacheItems = array('setting', 'seo', 'groupbuy_price', 'nav', 'express', 'store_class', 'store_grade', 'store_msg_tpl', 'member_msg_tpl', 'consult_type');/*20160831*/
	public function __construct()
    {
        parent::__construct();
        core\language::read('cache');
    }
    /**
     * 清理缓存
     */
    public function clearOp()
    {
        if (!chksubmit()) {
            core\tpl::showpage('cache.clear');
            return;
        }
        $lang = core\language::getLangContent();
        // 清理所有缓存
        if (isset($_POST['cls_full']) && $_POST['cls_full'] == 1) {
            foreach ($this->cacheItems as $i) {
                dkcache($i);
            }
            // 表主键
            dkcache('_pk');
            // 商品分类
            dkcache('gc_class');
            dkcache('all_categories');
            dkcache('goods_class_seo');
            dkcache('class_tag');
            // 广告
            //model('adv')->makeApAllCache();
            // 首页
            //model('web_config')->getWebHtml('index', 1);
            //delCacheFile('index');
        } else {
            $todo = (array) $_POST['cache'];
            foreach ($this->cacheItems as $i) {
                if (in_array($i, $todo)) {
                    dkcache($i);
                }
            }
            // 表主键
            if (in_array('table', $todo)) {
                dkcache('_pk');
            }
            // 商品分类
            if (in_array('goodsclass', $todo)) {
                dkcache('gc_class');
                dkcache('all_categories');
                dkcache('goods_class_seo');
                dkcache('class_tag');
            }
            // 广告
            if (in_array('adv', $todo)) {
                //model('adv')->makeApAllCache();
            }
            // 首页
            if (in_array('index', $todo)) {
                //model('web_config')->getWebHtml('index', 1);
                //delCacheFile('index');
            }
        }
        $this->log(lang('cache_cls_operate'));
        success($lang['cache_cls_ok']);
    }
}