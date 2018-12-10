<?php
$lang['wechat_not_install'] = '您没有安装微商城模块';

$lang['wechat_isuse'] = '微信模块开关';
$lang['wechat_isuse_explain'] = '关闭后系统将不再启用微信相关功能';

//微信接口配置
$lang['nc_wechat_api'] = '接口配置';
$lang['wechat_api_url'] = '接口URL';
$lang['wechat_token'] = 'Token';
$lang['wechat_type'] = '公众号类型';
$lang['wechat_appid'] = 'AppID';
$lang['wechat_appsecret'] = 'AppSecret';
$lang['wechat_name'] = '公众号名称';
$lang['wechat_email'] = '公众号邮箱';
$lang['wechat_preid'] = '公众号原始ID';
$lang['wechat_account'] = '微信号';
$lang['wechat_encodingtype'] = '消息加解密方式';
$lang['wechat_encoding'] = 'EncodingAESKey';
$lang['wechat_type_name'] = array(
	'0'=>'订阅号未认证',
	'1'=>'订阅号已认证',
	'2'=>'服务号未认证',
	'3'=>'服务号已认证'
);

$lang['wechat_encodingtype_name'] = array(
	'0'=>'明文模式',
	'1'=>'兼容模式',
	'2'=>'安全模式'
);

//素材管理
$lang['material_manage'] = '素材管理';
$lang['material_all'] = '全部';
$lang['material_single'] = '单图文';
$lang['material_multi'] = '多图文';
$lang['material_add'] = '新增';
$lang['material_edit'] = '编辑图文';
$lang['material_item_max'] = '你最多只可以加入8条图文消息！';
$lang['material_item_title'] = '标题';
$lang['material_item_image'] = '缩略图';
$lang['material_item_add'] = '增加一条';
$lang['material_item_openimage'] = '封面图';
$lang['material_item_link'] = '链接';
$lang['material_item_image_size'] = '建议尺寸';
$lang['material_not_null'] = '提交内容不能为空';
$lang['material_delete_tips'] = '删除后不可恢复，继续吗？';
$lang['material_type'] = array(
	1 => '单图文',
	2 => '多图文'
);

//公共语言
$lang['reply_type'] = '回复类型';
$lang['reply_type_name'] = array('文字消息','图文消息','链接网址','我的二维码');
$lang['reply_content'] = '回复内容';
$lang['reply_material'] = '回复图文';
$lang['reply_link'] = '链接网址';
$lang['open_btn'] = '开启';
$lang['close_btn'] = '关闭';
$lang['material_select_btn'] = '选择图文';
$lang['wechat_keywords'] = '关键词';
$lang['wechat_select_all'] = '全部';
$lang['reply_pattern_type'] = '匹配模式';
$lang['reply_pattern_type_name'] = array('精确匹配','模糊匹配');
$lang['not_info_id'] = '请选择信息';
$lang['info_not_exist'] = '信息不存在';
$lang['not_info_keywords'] = '请填写关键词';
$lang['info_keywords_exits'] = '关键词已存在';
$lang['wechat_patternmethod_notice'] = array('（用户输入的文字和此关键词一样才会触发,一般用于一个关键词.）','（只要用户输入的文字包含此关键词就触）');


//首次关注设置
$lang['nc_wechat_attention'] = '首次关注设置';
$lang['attention_each_keyword'] = '任意关键词';
$lang['attention_each_keyword_tips'] = '开启后，当输入的关键字无相关的匹配内容时，则使用本设置回复';
$lang['attention_user_notice'] = '成为会员提醒';
$lang['attention_user_notice_tips'] = '开启后，用户关注公众收到的消息中会包含会员信息，例如：您好**，您已成为第***位会员。此设置仅对“文字消息”有效';

//关键词管理
$lang['nc_wechat_keywords'] = '关键词设置';
$lang['wechat_keywords_notice'] = '多个关键词,请用 "|" 隔开';

//URL管理
$lang['wechat_url_manage'] = '自定义URL管理';
$lang['wechat_url_select_type'] = array(
	'name'=>'URL名称',
	'link'=>'URL地址'
);
$lang['not_info_url_name'] = '请填写URL名字';
$lang['not_info_url_link'] = '请填写URL链接';
$lang['wechat_url_name'] = 'URL名称';
$lang['wechat_url_link'] = 'URL链接';

//自定义菜单
$lang['wechat_menu_manage'] = '自定义菜单管理';
$lang['wechat_menu_title'] = '菜单标题';
$lang['wechat_is_useful'] = '是否生效';
$lang['wechat_addtime'] = '添加时间';
$lang['wechat_not_title'] = '请填写标题';
$lang['wechat_not_menu'] = '请设置菜单';

$lang['wechat_edit_menu'] = '菜单设置';
$lang['wechat_menu_name'] = '菜单';
$lang['wechat_child_name'] = '子菜单';

$lang['not_appid'] = '还没有配置AppID和AppSecret，请到【接口配置】中进行配置';
$lang['not_menu_data'] = '暂无菜单数据，请先设置菜单';
$lang['get_access_token_fail'] = '获取access_token失败，请检查配置信息';
$lang['menu_publish_success'] = '菜单已同步到微信';
$lang['menu_publish_fail'] = '菜单发布失败';
$lang['menu_publish_to_weixin'] = '同步到微信';
$lang['menu_delete_success'] = '微信菜单成功删除';
$lang['menu_delete_fail'] = '微信菜单删除失败';