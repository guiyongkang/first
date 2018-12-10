<?php
$lang['distributor_not_install'] = '您没有安装分销模块';

$lang['distributor_isuse'] = '模块开关';
$lang['distributor_isuse_explain'] = '关闭后系统将不再启用分销和公排相关功能';

/*门槛类型*/
$lang['distributor_cometype'] = array(
	1=>'一次性消费',
	2=>'总消费额',
	3=>'购买指定商品',
	4=>'购买任意商品',
	//5=>'直接购买',
);

/*排位递增形式*/
$lang['distributor_times'] = array(
	2=>'二二复制',
	3=>'三三复制',
	4=>'四四复制',
	5=>'五五复制',
	6=>'六六复制',
	7=>'七七复制',
	8=>'八八复制',
	9=>'九九复制'
);

//公共语言

$lang['not_info_id'] = '请选择信息';
$lang['info_not_exist'] = '信息不存在';
$lang['submit_no_permission'] = '非法提交';

/*公排设置*/
$lang['comevalue_is_number'] = '参与公排门槛限制值必须为数字';
$lang['comevalue_is_notnull'] = '参与公排门槛限制值不能为空';
$lang['returnvalue_is_number'] = '出局后重新排位门槛限制值必须为数字';
$lang['returnvalue_is_notnull'] = '出局后重新排位门槛限制值不能为空';
$lang['dis_name_is_not_null'] = '请填写分销商级别名称';
$lang['dis_comevalue_is_number'] = '成为分销商限制必须为数字';
$lang['dis_comevalue_is_notnull'] = '成为分销商限制不能为空';
$lang['addlevel_no_permission'] = '当前分销设置中设置的分销商门槛不支持多分销商级别';
$lang['first_dislevel_not_delete'] = '第一个分销商级别不能删除';
$lang['dis_updatevalue_is_notnull'] = '分销商升级限制不能为空';

/*分销商级别*/
$lang['nc_distributor_level'] = '分销商级别管理';
$lang['nc_distributor_title'] = array('一','二','三','四','五','六','七','八','九','十');
$lang['not_level_id'] = '请选择级别';
$lang['public_prize_title'] = array(
	'parent'=>'懒人奖',
	'invite'=>'推荐奖',
	'thankful'=>'感恩奖',
);
$lang['not_info_method_name'] = '请填写银行名称';