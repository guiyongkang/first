<?php defined('SAFE_CONST') or exit('Access Invalid!');?>
<link href="<?php echo APP_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo APP_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome-ie7.min.css">
<![endif]-->
<style type="text/css">
.border-right{border-right:1px #DFE4EA dotted}
</style>
<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3>分销商列表</h3>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form method="get" name="formSearch" id="formSearch">
		<input type="hidden" name="act" value="distributor_list">
		<input type="hidden" name="op" value="childlists">
        <input type="hidden" name="aid" value="<?php echo $output['search']['aid'];?>">
        <input type="hidden" name="level" value="<?php echo isset($output['search']['level']) ? $output['search']['level'] : '';?>">
		<table class="tb-type1 noborder search">
			<tbody>
				<tr>
					<th><label>会员昵称</label></th>
					<td><input type="text" value="<?php echo isset($output['search']['membername']) ? $output['search']['membername'] : '';?>" name="membername" id="membername" class="txt"></td>
					<th><label>分销商级别</label></th>
					<td>
                    	<select name="levelid">
                        	<option value="0">全部</option>
                        	<?php foreach($output['dis_level'] as $l_id=>$l_name){?>
                            <option value="<?php echo $l_id;?>"<?php echo isset($output['search']['levelid']) && ($output['search']['levelid']==$l_id) ? ' selected' : '';?>><?php echo $l_name;?></option>
                            <?php }?>
                        </select>
                    </td>
                    <th><label>下属级数</label></th>
					<td>
                    	<select name="level">
                        	<option value="">全部</option>
                        	<?php for($i=1;$i<=$output['maxlevel'];$i++){?>
                            <option value="<?php echo $i;?>"<?php echo isset($output['search']['level']) && $output['search']['level']==$i ? ' selected' : '';?>><?php echo $lang['nc_distributor_title'][$i-1];?></option>
                            <?php }?>
                        </select>
                    </td>
                    <td ><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
				</tr>
			</tbody>
		</table>
	</form>
	
	<form method='post' id="form_lists" action="index.php?act=distributor_list&op=changelevel">
		<input type="hidden" name="form_submit" value="ok" />
		<table class="table tb-type2">
			<thead>
				<tr class="thead">
					<th class="w24 border-right"></th>
					<th colspan="2" class="align-center border-right">会员信息</th>
                    <th class="w120 align-center border-right">分销级别</th>
                    <th colspan="2" class="align-center border-right">推荐人信息</th>
                    <th class="w108 align-center border-right">分销明细</th>
                    <th class="w108 align-center border-right">佣金明细</th>
                    <th class="w120 align-center border-right">加入时间</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($output['dis_lists']) && is_array($output['dis_lists'])) { ?>
				<?php foreach ($output['dis_lists'] as $k => $v) {?>
				<tr class="hover edit">
					<td class="border-right"><input type="checkbox" name="aid[]" value="<?php echo $v['distributor_id'];?>" class="checkitem"></td>					
                    <td class="w48 picture"><div class="size-44x44"><span class="thumb size-44x44"><i></i><img src="<?php echo $v['member_avatar'];?>" width="44" /></span></div></td>
					<td class="border-right"><?php echo $v['member_name'];?></td>
                    <td class="align-center border-right"><?php echo $v['level_name'];?></td>
                    <td class="w48 picture"><div class="size-44x44"><span class="thumb size-44x44"><i></i><img src="<?php echo $v['inviter_avatar'];?>" width="44" /></span></div></td>
					<td class="border-right"><?php echo $v['inviter_name'];?></td>
                    <td class="align-center border-right">
                    	<a href="JavaScript:show_dialog('dis',<?php echo $v['member_id'];?>);" nctype="record_batch">[查看]</a>
                    </td>
                    <td class="align-center border-right">
                    	<a href="JavaScript:show_dialog('com',<?php echo $v['member_id'];?>);" nctype="commission_batch">[查看]</a>
                    </td>
                    <td class="align-center border-right"><?php echo date('Y-m-d H:i:s',$v['addtime']);?></td>
				</tr>
				<tr style="display:none;">
					<td colspan="20"><div class="ncsc-goods-sku ps-container"></div></td>
				</tr>
				<?php } ?>
				<?php } else { ?>
				<tr class="no_data">
					<td colspan="15"><?php echo $lang['nc_no_record'];?></td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr class="tfoot">
					<td><input type="checkbox" class="checkall" id="checkallBottom"></td>
					<td colspan="16"><label for="checkallBottom"><?php echo $lang['nc_select_all']; ?></label>
						&nbsp;&nbsp;<a href="JavaScript:void(0);" class="btn" nctype="edit_batch"><span>更改级别</span></a>&nbsp;<select name="level_id">
                        	<?php foreach($output['dis_level'] as $l_id=>$l_name){?>
                            <option value="<?php echo $l_id;?>"<?php isset($output['search']['levelid']) && $output['search']['levelid']==$l_id ? ' selected' : '';?>><?php echo $l_name;?></option>
                            <?php }?>
                        </select></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<div id="dis_dialog" style="display:none;">
  <div class="dialog-show-box">
    <div id="show_dis_list"></div>
    <div class="clear"></div>
  </div>
  <div class="clear"></div>
</div>
<div id="com_dialog" style="display:none;">
  <div class="dialog-show-box">
    <div id="show_com_list"></div>
    <div class="clear"></div>
  </div>
  <div class="clear"></div>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>
<script type="text/javascript">
$(function(){
    $('#ncsubmit').click(function(){
        $('#formSearch').submit();
    });

    $('a[nctype="edit_batch"]').click(function(){
       	$('#form_lists').submit();
    });
	
	
});
DialogManager.close = function(id) {
	__DIALOG_WRAPPER__[id].hide();
	ScreenLocker.unlock();
}

DialogManager.show = function(id) {
	if (__DIALOG_WRAPPER__[id]) {
		__DIALOG_WRAPPER__[id].show();
		ScreenLocker.lock();
		return true;
	}
	return false;
}

var titles = new Array();
titles["dis"] = '分销统计';
titles["com"] = '佣金统计';

function show_dialog(id,aid) {//弹出框
	var html_str = '';
	$.post("index.php?act=distributor_list&op=showdetail",{type:id,aid:aid},function(data){
		
		var d = DialogManager.create(id);//不存在时初始化(执行一次)
		var dialog_html = $("#"+id+"_dialog").html();
		$("#"+id+"_dialog").remove();
		d.setTitle('['+data.member_name+']'+titles[id]);
		d.setContents('<div id="'+id+'_dialog" class="'+id+'_dialog">'+data.html+'</div>');
		d.setWidth(500);
		d.show('center',1);
	},"json");
	
	
	
}

</script> 