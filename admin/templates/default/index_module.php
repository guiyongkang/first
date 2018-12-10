<?php defined('SAFE_CONST') or exit('Access Invalid!');?>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3>首页模块管理</h3>
			<ul class="tab-base">
				<li><a class="current" href="JavaScript:void(0);"><span>列表</span></a></li>
				<li><a href="index.php?act=mb_special&op=index_module_add"><span>新增</span></a></li>
			</ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form id="form_spec" method="get">
		<input type="hidden" name="act" value="mb_special" />
		<input type="hidden" name="op" value="index_module" />
		<input type="hidden" name="form_submit" value="ok" />
		<table class="table tb-type2">
			<thead>
				<tr class="thead">
					<th></th>
					<th>排序</th>
					<th>名称</th>
					<th>连接</th>
					<th class="align-center">操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( !empty($output['list']) && is_array($output['list']) ) {?>
				<?php foreach ($output['list'] as $val) {?>
				<tr class="hover edit">
					<td class="w24"><input type="checkbox" class="checkitem" name="id[]" value="<?php echo $val['id'];?>" /></td>
					<td class="w48 sort"><?php echo $val['sort'];?></td>
					<td class=""><?php echo $val['name'];?></td>
					<td class="w350 name"><?php echo $val['url'];?></td>
					<td class="w96 align-center"><a href="index.php?act=mb_special&op=index_module_edit&id=<?php echo $val['id'];?>">编辑</a>
					<?php if(!in_array($val['id'], array('1','2','3','4','5','6','7','8'))){?>
					| <a onclick="if(confirm('数据不可恢复，确定删除？')){location.href='index.php?act=mb_special&op=index_module&form_submit=ok&id=<?php echo $val['id'];?>';}else{return false;}" href="javascript:void(0)">删除</a>
					<?php }?>
					</td>
				</tr>
				<?php }?>
				<?php }else{ ?>
				<tr class="no_data">
					<td colspan="10">暂无记录</td>
				</tr>
				<?php }?>
			</tbody>
			<?php if(!empty($output['list']) && is_array($output['list'])){ ?>
			<tfoot>
				<tr>
					<td><input type="checkbox" class="checkall" id="checkallBottom" /></td>
					<td id="dataFuncs" colspan="16">
					    <label for="checkallBottom">全选</label>
						&nbsp;&nbsp; <a class="btn" onclick="submit_form('recommend');" href="JavaScript:void(0);"> <span>删除</span> </a>
					</td>
				<tr>
			</tfoot>
			<?php }?>
		</table>
	</form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script> 
<script type="text/javascript">
function submit_form(type){
	var id = '';
	$('input[type=checkbox]:checked').each(function(){
		if(!isNaN($(this).val())){
			id += $(this).val();
		}
	});
	if(id == ''){
		alert('数据不可恢复，确定删除？');
		return false;
	}
	if(type == 'del'){
		if(!confirm('数据不可恢复，确定删除？')){
			return false;
		}
	}
	$('#form_spec').submit();
}
</script>