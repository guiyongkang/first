<?php defined('SAFE_CONST') or exit('Access Invalid!');?>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3>店铺管理</h3>
			<ul class="tab-base">
				<li><a href="index.php?act=ownshop&op=list"><span>管理</span></a></li>
				<li><a href="javascript:;" class="current"><span>新增</span></a></li>
			</ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<table class="table tb-type2" id="prompt">
		<tbody>
			<tr class="space odd">
				<th colspan="12"><div class="title">
						<h5><?php echo $lang['nc_prompts'];?></h5>
						<span class="arrow"></span></div>
				</th>
			</tr>
			<tr>
				<td>
				    <ul>
						<li>平台可以在此处添加店铺，新增的店铺默认为开启状态</li>
						<li>新增店铺默认绑定所有经营类目，可以手动设置绑定其经营类目</li>
						<li>新增店铺将自动创建店主会员账号（用于登录网站会员中心）以及商家账号（用于登录商家中心）</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<form id="store_form" method="post">
		<input type="hidden" name="form_submit" value="ok" />
		<input type="hidden" name="store_id" value="<?php echo isset($output['store_array']['store_id']) ? $output['store_array']['store_id'] : 0; ?>" />
		<table class="table tb-type2">
			<tbody>
				<tr>
					<td colspan="2" class="required"><label class="validation" for="store_name">店铺名称:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="text" value="" id="store_name" name="store_name" class="txt" /></td>
					<td class="vatop tips"></td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label><?php echo $lang['belongs_class'];?>:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform">
					    <select name="sc_id">
							<option value="0"><?php echo $lang['nc_please_choose'];?>...</option>
							<?php if(is_array($output['class_list'])){ ?>
							<?php foreach($output['class_list'] as $k => $v){ ?>
							<option value="<?php echo $v['sc_id']; ?>"><?php echo $v['sc_name']; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</td>
					<td class="vatop tips"></td>
				</tr>
				<tr class="noborder">
					<td colspan="2" class="required"><label class="validation" for="member_name">店主账号:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="text" value="" id="member_name" name="member_name" class="txt" /></td>
					<td class="vatop tips">用于登录会员中心</td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label class="validation" for="seller_name">店主卖家账号:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="text" value="" id="seller_name" name="seller_name" class="txt" /></td>
					<td class="vatop tips">用于登录商家中心，可与店主账号不同</td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label class="validation" for="member_passwd">登录密码:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="password" value="" id="member_passwd" name="member_passwd" class="txt" /></td>
					<td class="vatop tips"></td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label class="validation" for="member_passwd2">确认密码:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="password" value="" id="member_passwd2" name="member_passwd2" class="txt" /></td>
					<td class="vatop tips"></td>
				</tr>
			</tbody>
			<tfoot>
				<tr class="tfoot">
					<td colspan="15"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<script type="text/javascript">
$(function(){
    //按钮先执行验证再提交表单
    $("#submitBtn").click(function(){
        if($("#store_form").valid()){
            $("#store_form").submit();
        }
    });

    $('#store_form').validate({
        errorPlacement: function(error, element){
            error.appendTo(element.parentsUntil('tr').parent().prev().find('td:first'));
        },
        rules : {
            store_name: {
                required : true,
                remote : '<?php echo urlAdmin('ownshop', 'ckeck_store_name')?>'
            },
            member_name: {
                required : true,
                minlength : 3,
                maxlength : 15,
                remote   : {
                    url : 'index.php?act=ownshop&op=check_member_name',
                    type: 'get',
                    data:{
                        member_name : function(){
                            return $('#member_name').val();
                        }
                    }
                }
            },
            seller_name: {
                required : true,
                minlength : 3,
                maxlength : 15,
                remote   : {
                    url : 'index.php?act=ownshop&op=check_seller_name',
                    type: 'get',
                    data:{
                        seller_name : function(){
                            return $('#seller_name').val();
                        }
                    }
                }
            },
            member_passwd : {
                required : true,
                minlength: 6
            },
			member_passwd2: {
				required: true,
				minlength: 6,
				equalTo: '#member_passwd'
			},
        },
        messages : {
            store_name: {
                required: '请输入店铺名称',
                remote : '店铺名称已存在'
            },
            member_name: {
                required : '请输入店主账号',
                minlength : '店主账号最短为3位',
                maxlength : '店主账号最长为15位',
                remote   : '此名称已被其它店铺会员占用，请重新输入'
            },
            seller_name: {
                required : '请输入店主卖家账号',
                minlength : '店主卖家账号最短为3位',
                maxlength : '店主卖家账号最长为15位',
                remote   : '此名称已被其它店铺占用，请重新输入'
            },
            member_passwd : {
                required : '请输入登录密码',
                minlength: '登录密码长度不能小于6'
            },
			member_passwd2 : {
                required: '请输入密码',
				minlength: '登录密码长度不能小于6',
				equalTo: '两次密码输入不一致'
            }
        }
    });
});
</script> 
