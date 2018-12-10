<?php
namespace common\logic;
use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class qrcode
{
	/*手机版二维码*/
	public function create_qrcode_wap($member_id){
        require_once BASE_RESOURCE_PATH . DS . 'phpqrcode' . DS . 'index.php';
        $PhpQRCode = new \PhpQRCode();
        $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_QRCODE . DS);
		$PhpQRCode->set('matrixPointSize', 12);
        $qrcode_url = WAP_SITE_URL . '/tmpl/member/register.html?oid='.$member_id;
        $PhpQRCode->set('date', $qrcode_url);
        $PhpQRCode->set('pngTempName', 'wap_qrcode_'.$member_id.'.png');
        $PhpQRCode->init();
		return getMemberQrcodeImgForID($member_id,'wap');
	}
	
	/*手机版海报*/
	public function create_poster_wap($member_id, $member_name, $qrcode_bg){
		$imgpath_qrcode = getMemberQrcodeImgForID($member_id,'wap');
		if(!$imgpath_qrcode){
			$imgpath_qrcode = $this->create_qrcode_wap($member_id);
		}
		 
		if(empty($qrcode_bg)){
			return $imgpath_qrcode;
		}
		
		return $this->make_poster($member_id,$member_name,$imgpath_qrcode,$qrcode_bg,'wap');
	}
	
	/*微信版二维码*/
	public function create_qrcode_weixin($member_id){
		$file_path = BASE_UPLOAD_PATH . DS . ATTACH_QRCODE . DS . 'weixin_qrcode_'.$member_id.'.jpg';
		
        $access_token = logic('weixin_token')->get_access_token();
		$weixin_config = model('wechat')->getInfoOne('weixin_wechat','','wechat_appid,wechat_appsecret');
		$wechat = new lib\wxSDk\WechatAuth($weixin_config['wechat_appid'], $weixin_config['wechat_appsecret'], $access_token);
		
		$data = $wechat->qrcodeCreate($member_id);
		if(!empty($data['ticket'])){
			$img_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($data['ticket']);
			$flag = $this->downloadImageFromWeiXin($img_url,$file_path);
			$resizeImage = new lib\resizeimage();
			$resizeImage->newImg($file_path, 370, 370, 1, ".", dirname($file_path), false);
		}
		
		
		return getMemberQrcodeImgForID($member_id,'weixin');
	}
	
	/*微信版海报*/
	public function create_poster_weixin($member_id, $member_name, $qrcode_bg){
		
		$imgpath_qrcode = getMemberQrcodeImgForID($member_id,'weixin');
		if(!$imgpath_qrcode){
			$imgpath_qrcode = $this->create_qrcode_weixin($member_id);
		}
		
		if(!$imgpath_qrcode){
			return 'fail';
		}
		
		return $this->make_poster($member_id,$member_name,$imgpath_qrcode,$qrcode_bg,'weixin');
	}
	
	/*
	生成海报
	member_id 会员id
	member_name 会员昵称
	qrcode_image 二维码图片
	qrcode_bg 海报背景图片
	type 类型 weixin , wap
	*/
	private function make_poster($member_id,$member_name,$qrcode_image,$qrcode_bg,$type){
		$member_avatar = getMemberAvatarForID($member_id);
		
		if(empty($qrcode_bg)){
			return $qrcode_image;
		}
		$qrcode_config = model('distributor')->getInfoOne('distributor_other_setting','','*');
		$store_wm_info  =array();
		
		//加上头像
		
		$gd_image = new lib\gdimageqrcode();
		$store_wm_info['save_file'] = BASE_UPLOAD_PATH . DS . ATTACH_POSTER . DS . $type . '_poster_'.$member_id.'.jpg';
		$store_wm_info['wm_image_name'] = BASE_UPLOAD_PATH.  str_replace(UPLOAD_SITE_URL, '', $member_avatar);
		$store_wm_info['jpeg_quality'] = 100;
		$store_wm_info['wm_image_pos'] = 9;
		$store_wm_info['wm_image_top'] = 80;
		$store_wm_info['wm_image_left'] = 45;
		$store_wm_info['wm_image_transition'] = 100;		
        $gd_image->setWatermark($store_wm_info);
        $flag = $gd_image->create(BASE_UPLOAD_PATH . $qrcode_bg);
		
		//加上二维码
		if($flag){
			unset($store_wm_info);
			$poster_image = getMemberPosterImgForID($member_id,$type);
			
			if($qrcode_config['qrcode_width']>0 && $qrcode_config['qrcode_width']<370){
				$resizeImage = new lib\resizeimage();
				$resizeImage->newImg(BASE_UPLOAD_PATH.  str_replace(UPLOAD_SITE_URL, '', $qrcode_image), intval($qrcode_config['qrcode_width']), intval($qrcode_config['qrcode_width']), 1, ".", dirname(BASE_UPLOAD_PATH.  str_replace(UPLOAD_SITE_URL, '', $qrcode_image)), false);
			}
			
			$store_wm_info['save_file'] = BASE_UPLOAD_PATH . DS . ATTACH_POSTER . DS . $type . '_poster_'.$member_id.'.jpg';
			$store_wm_info['wm_image_name'] = BASE_UPLOAD_PATH.  str_replace(UPLOAD_SITE_URL, '', $qrcode_image);
			$store_wm_info['jpeg_quality'] = 100;
			$store_wm_info['wm_image_pos'] = 9;
			$store_wm_info['wm_image_top'] = $qrcode_config['qrcode_top'];
			$store_wm_info['wm_image_left'] = $qrcode_config['qrcode_left'];
			$store_wm_info['wm_image_transition'] = 100;
			$gd_image = new lib\gdimageqrcode();
			$gd_image->setWatermark($store_wm_info);
			$flag = $gd_image->create(BASE_UPLOAD_PATH . str_replace(UPLOAD_SITE_URL, '', $poster_image));
			if($flag){
				unset($store_wm_info);
				$poster_image = getMemberPosterImgForID($member_id,$type);
				//加上昵称
				
				$store_wm_info['save_file'] = BASE_UPLOAD_PATH . DS . ATTACH_POSTER . DS . $type . '_poster_'.$member_id.'.jpg';
				$store_wm_info['wm_text'] = $member_name;
				
				$store_wm_info['wm_text_size'] = 40;
				$store_wm_info['wm_text_pos'] = 9;
				$store_wm_info['wm_text_angle'] = 0;
				$store_wm_info['wm_text_top'] = 150;
				$store_wm_info['wm_text_left'] = 190;
				$store_wm_info['wm_text_font'] = 'simhei';
				$store_wm_info['wm_text_color'] = $qrcode_config['title_color'];
				$store_wm_info['jpeg_quality'] = 100;
				$store_wm_info['wm_image_pos'] = 9;
				$store_wm_info['wm_image_top'] = 0;
				$store_wm_info['wm_image_left'] = 0;
				$store_wm_info['wm_image_transition'] = 100;
				$gd_image = new lib\gdimageqrcode();
				$gd_image->setWatermark($store_wm_info);
				$flag = $gd_image->create(BASE_UPLOAD_PATH . str_replace(UPLOAD_SITE_URL, '', $poster_image));
				if($flag){
					return $poster_image;
				}else{
					return $poster_image;
				}				
			}else{
				return $poster_image;
			}
		}else{
			return $qrcode_image;
		}
	}
	
	/*微信版下载二维码*/
	private function downloadImageFromWeiXin($url, $filename){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$file = curl_exec($ch);
		curl_close($ch);
		$flag = true;
        $write = @fopen ( $filename, "w" );
        if ($write == false) {
            $flag = false;
        }
        if (fwrite ( $write, $file ) == false) {
            $flag = false;
        }
        if (fclose ( $write ) == false) {
            $flag = false;
        }
		return $flag;
	}
}