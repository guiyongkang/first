<?php
function thumb($goods = array(), $type = '') {
	$type_array = explode ( ',_', ltrim ( GOODS_IMAGES_EXT, '_' ) );
	if (! in_array ( $type, $type_array )) {
		$type = '240';
	}
	if (empty ( $goods )) {
		return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
	}
	if (array_key_exists ( 'apic_cover', $goods )) {
		$goods ['goods_image'] = $goods ['apic_cover'];
	}
	if (empty ( $goods ['goods_image'] )) {
		return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
	}
	$search_array = explode ( ',', GOODS_IMAGES_EXT );
	$file = str_ireplace ( $search_array, '', $goods ['goods_image'] );
	$fname = basename ( $file );
	// 取店铺ID
	if (preg_match ( '/^(\d+_)/', $fname )) {
		$store_id = substr ( $fname, 0, strpos ( $fname, '_' ) );
	} else {
		$store_id = $goods ['store_id'];
	}
	$file = $type == '' ? $file : str_ireplace ( '.', '_' . $type . '.', $file );
	if (! file_exists ( BASE_UPLOAD_PATH . '/' . ATTACH_GOODS . '/' . $store_id . '/' . $file )) {
		return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
	}
	$thumb_host = UPLOAD_SITE_URL . '/' . ATTACH_GOODS;
	return $thumb_host . '/' . $store_id . '/' . $file;
}
/**
 * 取得商品缩略图的完整URL路径，接收图片名称与店铺ID
 *
 * @param string $file
 *        	图片名称
 * @param string $type
 *        	缩略图尺寸类型，值为60,240,360,1280
 * @param mixed $store_id
 *        	店铺ID 如果传入，则返回图片完整URL,如果为假，返回系统默认图
 * @return string
 */
function cthumb($file, $type = '', $store_id = false) {
	$type_array = explode ( ',_', ltrim ( GOODS_IMAGES_EXT, '_' ) );
	if (! in_array ( $type, $type_array )) {
		$type = '240';
	}
	if (empty ( $file )) {
		return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
	}
	$search_array = explode ( ',', GOODS_IMAGES_EXT );
	$file = str_ireplace ( $search_array, '', $file );
	$fname = basename ( $file );
	// 取店铺ID
	if ($store_id === false || ! is_numeric ( $store_id )) {
		$store_id = substr ( $fname, 0, strpos ( $fname, '_' ) );
	}
	// 本地存储时，增加判断文件是否存在，用默认图代替
	if (! file_exists ( BASE_UPLOAD_PATH . '/' . ATTACH_GOODS . '/' . $store_id . '/' . ($type == '' ? $file : str_ireplace ( '.', '_' . $type . '.', $file )) )) {
		return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
	}
	$thumb_host = UPLOAD_SITE_URL . '/' . ATTACH_GOODS;
	return $thumb_host . '/' . $store_id . '/' . ($type == '' ? $file : str_ireplace ( '.', '_' . $type . '.', $file ));
}
/**
 * 商品二维码
 * 
 * @param array $goods_info        	
 * @return string
 */
function goodsQRCode($goods_info) {
	if (! file_exists ( BASE_UPLOAD_PATH . '/' . ATTACH_STORE . '/' . $goods_info ['store_id'] . '/' . $goods_info ['goods_id'] . '.png' )) {
		return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . 'default_qrcode.png';
	}
	return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $goods_info ['store_id'] . DS . $goods_info ['goods_id'] . '.png';
}

/**
 * 商品二维码 v3-b12
 * 
 * @param array $goods_info        	
 * @return string
 */
function storeQRCode($store_id) {
	if (! file_exists ( BASE_UPLOAD_PATH . '/' . ATTACH_STORE . '/' . $store_id . '/' . $store_id . '_store.png' )) {
		return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . 'default_qrcode.png';
	}
	return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $store_id . DS . $store_id . '_store.png';
}

/**
 * 取得抢购缩略图的完整URL路径
 *
 * @param string $imgurl
 *        	商品名称
 * @param string $type
 *        	缩略图类型 值为small,mid,max
 * @return string
 */
function gthumb($image_name = '', $type = '') {
	if (! in_array ( $type, array (
			'small',
			'mid',
			'max' 
	) ))
		$type = 'small';
	if (empty ( $image_name )) {
		return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( '240' );
	}
	list ( $base_name, $ext ) = explode ( '.', $image_name );
	list ( $store_id ) = explode ( '_', $base_name );
	$file_path = ATTACH_GROUPBUY . DS . $store_id . DS . $base_name . '_' . $type . '.' . $ext;
	if (! file_exists ( BASE_UPLOAD_PATH . DS . $file_path )) {
		return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( '240' );
	}
	return UPLOAD_SITE_URL . DS . $file_path;
}

/**
 * 取得买家缩略图的完整URL路径
 *
 * @param string $imgurl
 *        	商品名称
 * @param string $type
 *        	缩略图类型 值为240,1024
 * @return string
 */
function snsThumb($image_name = '', $type = '') {
	if (! in_array ( $type, array (
			'240',
			'1024' 
	) ))
		$type = '240';
		/*
	 * if ($image_name) {
	 * return UPLOAD_SITE_URL . "/" . defaultGoodsImage("240");
	 * }
	 */
	
	if (strpos ( $image_name, "/" )) {
		$image = explode ( "/", $image_name );
		$image = end ( $image );
	} else {
		$image = $image_name;
	}
	
	list ( $member_id ) = explode ( "_", $image );
	$file_path = ATTACH_MALBUM . DS . $member_id . DS . str_ireplace ( ".", "_" . $type . ".", $image_name );
	
	if (! file_exists ( BASE_UPLOAD_PATH . DS . $file_path )) {
		return UPLOAD_SITE_URL . "/" . defaultGoodsImage ( "240" );
	}
	
	return UPLOAD_SITE_URL . DS . $file_path;
}
function pointprodThumb($image_name = '', $type = '') {
	if (! in_array ( $type, array (
			"small",
			"mid" 
	) )) {
		$type = "";
	}
	
	if (empty ( $image_name )) {
		return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( '240' );
	}
	
	if ($type) {
		$file_path = ATTACH_POINTPROD . DS . str_ireplace ( ".", "_" . $type . ".", $image_name );
	} else {
		$file_path = ATTACH_POINTPROD . DS . $image_name;
	}
	
	if (! file_exists ( BASE_UPLOAD_PATH . DS . $file_path )) {
		return UPLOAD_SITE_URL . "/" . defaultGoodsImage ( "240" );
	}
	
	return UPLOAD_SITE_URL . DS . $file_path;
}
function brandImage($image_name = '') {
	if ($image_name != "") {
		return UPLOAD_SITE_URL . "/" . ATTACH_BRAND . "/" . $image_name;
	}
	
	return UPLOAD_SITE_URL . "/" . ATTACH_COMMON . "/default_brand_image.gif";
}
function orderState($order_info) {
	switch ($order_info ["order_state"]) {
		case ORDER_STATE_CANCEL :
			$order_state = "已取消";
			break;
		
		case ORDER_STATE_NEW :
			$order_state = "待付款";
			break;
		
		case ORDER_STATE_PAY :
			$order_state = "待发货";
			break;
		
		case ORDER_STATE_SEND :
			$order_state = "待收货";
			break;
		
		case ORDER_STATE_SUCCESS :
			$order_state = "交易完成";
			break;
	}
	
	return $order_state;
}
/**
 * 取得订单支付类型文字输出形式
 */
function orderPaymentName($payment_code) {
	return str_replace ( array (
			'offline',
			'online',
			'ali_native',
			'alipay',
			'tenpay',
			'chinabank',
			'predeposit',
			'wxpay',
			'wx_jsapi',
			'wx_saoma' 
	), array (
			'货到付款',
			'在线付款',
			'支付宝移动支付',
			'支付宝',
			'财付通',
			'网银在线',
			'站内余额支付',
			'微信支付[客户端]',
			'微信支付[jsapi]',
			'微信支付[扫码]' 
	), $payment_code );
}
function orderGoodsType($goods_type) {
	return str_replace ( array (
			"1",
			"2",
			"3",
			"4",
			"5",
			"8",
			"9" 
	), array (
			"",
			"抢购",
			"限时折扣",
			"优惠套装",
			"赠品",
			"",
			"换购" 
	), $goods_type );
}
function billState($bill_state) {
	return str_replace ( array (
			"1",
			"2",
			"3",
			"4" 
	), array (
			"已出账",
			"商家已确认",
			"平台已审核",
			"结算完成" 
	), $bill_state );
}

defined ( "SAFE_CONST" ) || exit ( "Access Invalid!" );

?>
