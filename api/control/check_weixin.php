<?php
/**
 * 微信相关接口功能
**/
namespace api\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class check_weixin extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
    }
    public function indexOp()
    {
		$ownerid = logic('get_ownerid')->get_ownerid(urldecode($_GET['ref']));
        $wechat_isuse = core\config::get('wechat_isuse');
		if(empty($wechat_isuse)){
			output_error(0);
		}else{
			output_data(1);
		}
	}
	
	public function weixin_jssdkOp(){
		$wechat_isuse = core\config::get('wechat_isuse');
		if(empty($wechat_isuse)){
			output_error(0);
		}elseif(empty($_GET['ref'])){
			output_error(0);
		}else{
			$share_config = logic('weixin_jssdk')->jssdk_get_signature(urldecode($_GET['ref']));
			if(empty($share_config)){
				output_error(0);
			}else{
				$ownerid = logic('get_ownerid')->get_ownerid(urldecode($_GET['ref']));
				$share_config['link'] = logic('get_ownerid')->connect_url($ownerid,urldecode($_GET['ref']));
				
				$pageinfo = logic('get_ownerid')->split_url(urldecode($_GET['ref']));
				
				switch($pageinfo['page']){
					case 'product_detail':
						$model_goods = model('goods');
						$goods_id = $pageinfo['requesturi']['goods_id'];
						$goodsinfo = $model_goods->getGoodsInfoByID($goods_id, 'goods_commonid');
						if(!empty($goodsinfo)){
							$goods_common = $model_goods->getGoodeCommonInfoByID($goodsinfo['goods_commonid'], 'goods_name,goods_jingle,goods_image');
							$share_config['title'] = empty($goods_common["goods_name"]) ? '' : htmlspecialchars_decode($goods_common["goods_name"],ENT_QUOTES);
							$share_config['desc'] = empty($goods_common["goods_jingle"]) ? $share_config['title'] : str_replace(array("\r","\n","\t","\r\n"),'',$goods_common["goods_jingle"]);
							$share_config['img_url'] = empty($goods_common["goods_image"]) ? '' : thumb($goods_common, '100');
						}
					break;
					default:
						$model_wechat = model('wechat');
						$wechat_info = $model_wechat->getInfoOne('weixin_wechat','','wechat_share_title,wechat_share_logo,wechat_share_desc');
						$share_config['title'] = empty($wechat_info["wechat_share_title"]) ? '' : htmlspecialchars_decode($wechat_info["wechat_share_title"],ENT_QUOTES);
						$share_config['desc'] = empty($wechat_info["wechat_share_desc"]) ? '' : htmlspecialchars_decode($wechat_info["wechat_share_desc"],ENT_QUOTES);
						$share_config['img_url'] = empty($wechat_info["wechat_share_logo"]) ? '' : UPLOAD_SITE_URL.$wechat_info["wechat_share_logo"];
					break;
				}
				
				output_data(1,$share_config);
			}
		}
	}
}