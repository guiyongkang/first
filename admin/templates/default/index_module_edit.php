<?php defined('SAFE_CONST') or exit('Access Invalid!');?>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3>首页模块</h3>
			<ul class="tab-base">
				<li><a href="index.php?act=mb_special&op=index_module"><span>列表</span></a></li>
				<li><a href="index.php?act=mb_special&op=index_module_add"><span>新增</span></a></li>
				<li><a class="current" href="JavaScript:void(0);"><span>编辑</span></a></li>
			</ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form id="goods_class_form" enctype="multipart/form-data" method="post">
		<input type="hidden" name="form_submit" value="ok" />
		<input type="hidden" name="id" value="<?=$output['info']['id']?>" />
		<table class="table tb-type2">
			<tbody>
				<tr class="noborder">
					<td colspan="2" class="required"><label class="validation" for="gc_name">名称:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="text" value="<?=$output['info']['name']?>" name="name" id="name" maxlength="20" class="txt"></td>
					<td class="vatop tips"></td>
				</tr>
				<tr class="noborder">
					<td colspan="2" class="required"><label for="gc_name">连接:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="text" value="<?=$output['info']['url']?>" name="url" id="url" maxlength="200" class="txt"></td>
					<td class="vatop tips"></td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label for="bg_img">图标:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform">
					    <span class="type-file-show">
						    <img class="show_image" src="<?php echo APP_TEMPLATES_URL;?>/images/preview.png">
						    <div class="type-file-preview"><img src="<?php echo isset($output['info']['bg_img']) ? BASE_SITE_URL . DS . DIR_UPLOAD . DS . ATTACH_COMMON . DS . 'index_icon' . DS . $output['info']['bg_img'] : APP_TEMPLATES_URL . '/images/preview.png';?>"></div>
						</span>
					    <span class="type-file-box">
							<input type='text' name='textfield' id='textfield1' class='type-file-text' />
							<input type='button' name='button' id='button1' value='' class='type-file-button' />
							<input name="bg_img" type="file" class="type-file-file" id="bg_img" size="30" hidefocus="true" nc_type="change_pic">
						</span>
					</td>
					<td class="vatop tips">建议用90px * 90px，超出后自动隐藏</td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label>背景色:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="text" value="<?=$output['info']['bg_color']?>" name="bg_color" id="bg_color" class="txt"></td>
					<td class="vatop tips">例如：#fb6e52</td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label>排序:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="text" value="<?=$output['info']['sort']?>" name="sort" id="sort" class="txt"></td>
					<td class="vatop tips">越小越靠前</td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label>
						<label for="state">状态:</label>
						</label>
					</td>
				</tr>
				<tr class="noborder" style="background: rgb(255, 255, 255) none repeat scroll 0% 0%;">
				    <td class="vatop rowform onoff">
					    <label for="state1" class="cb-enable <?php if($output['info']['status'] == '1'){ ?>selected<?php } ?>" ><span>开启</span></label>
						<label for="state0" class="cb-disable <?php if($output['info']['status'] == '0'){ ?>selected<?php } ?>" ><span>关闭</span></label>
						<input id="state1" name="state" <?php if($output['info']['status'] == '1'){ ?>checked="checked"<?php } ?> value="1" type="radio">
						<input id="state0" name="state" <?php if($output['info']['status'] == '0'){ ?>checked="checked"<?php } ?> value="0" type="radio">
					</td>
					<td class="vatop tips"></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span>提交</span></a></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script> 
<script>
//按钮先执行验证再提交表单
$(function(){
	$('#submitBtn').click(function(){
		if($('#goods_class_form').valid()){
			$('#goods_class_form').submit();
		}
	});
	
	$('#bg_img').change(function(){
		$('#textfield1').val($(this).val());
	});
	$('#goods_class_form').validate({
        errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
        },
        rules : {
            name : {
                required : true,
            },
            
            sort : {
                number   : true
            }
        },
        messages : {
            name : {
                required : '请填写名称',
            },
            gc_sort  : {
                number   : '排序必须是数字'
            }
        }
    });
});
</script> 
