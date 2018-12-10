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
			<h3>分销记录</h3>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form method="get" name="formSearch" id="formSearch">
		<input type="hidden" name="act" value="distributor_list">
		<input type="hidden" name="op" value="disrecord">
        
		<table class="tb-type1 noborder search">
			<tbody>
				<tr>
					<th><label>
                    <select name="fields">
                    	<option value="buyer"<?php echo isset($output['search']['fields']) && $output['search']['fields']=='buyer' ? ' selected' : '';?>>购买者昵称</option>
                        <option value="owner"<?php echo isset($output['search']['fields']) && $output['search']['fields']=='owner' ? ' selected' : '';?>>获奖者昵称</option>
                    </select>
                    </label></th>
					<td><input type="text" value="<?php echo isset($output['search']['membername']) ? $output['search']['membername'] : '';?>" name="membername" id="membername" class="txt"></td>
                    <th><label>商品名称</label></th>
					<td><input type="text" value="<?php echo isset($output['search']['goodname']) ? $output['search']['goodname'] : '';?>" name="goodname" id="goodname" class="txt"></td>
					<th><label>状态</label></th>
					<td>
                    	<select name="status">
                        	<option value="0">全部</option>
                            <option value="10"<?php echo isset($output['search']['status']) && $output['search']['status']==10 ? ' selected' : '';?>>未付款</option>
                            <option value="20"<?php echo isset($output['search']['status']) && $output['search']['status']==20 ? ' selected' : '';?>>已付款</option>
                            <option value="30"<?php echo isset($output['search']['status']) && $output['search']['status']==30 ? ' selected' : '';?>>已发货</option>
                            <option value="40"<?php echo isset($output['search']['status']) && $output['search']['status']==40 ? ' selected' : '';?>>已完成</option>
                        </select>
                    </td>
                    <td ><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
				</tr>
			</tbody>
		</table>
	</form>
		<table class="table tb-type2">
			<thead>
				<tr class="thead">
					<th class="w60 border-right">序号</th>
					<th colspan="2" class="align-center border-right">购买者信息</th>
                    <th colspan="2" class="align-center border-right">商品信息</th>
                    <th class="align-center border-right">佣金明细</th>
                    <th class="w84 align-center border-right">状态</th>
                    <th class="w120 align-center border-right">分销时间</th>
					<th class="w108 align-center"><?php echo $lang['nc_handle'];?> </th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($output['record_lists']) && is_array($output['record_lists'])) { ?>
				<?php foreach ($output['record_lists'] as $key => $value) {?>
				<tr class="hover edit">
					<td class="border-right"><?php echo $value['record_id'];?></td>					
                    <td class="w48 picture"><div class="size-44x44"><span class="thumb size-44x44"><i></i><img src="<?php echo $value['buyer_avatar'];?>" width="44" /></span></div></td>
					<td class="border-right"><?php echo $value['buyer_name'];?></td>
                    <td class="w48 picture"><div class="size-44x44"><?php if($value['goodimg']){?><span class="thumb size-44x44"><i></i><img src="<?php echo $value['goodimg'];?>" width="44" /></span><?php }?></div></td>
					<td class="border-right"><?php if(!$value['goodname']){?>暂无<?php }else{?><?php echo $value['goodname'];?><?php }?></td>
                    <td class="align-center border-right">
                    	<?php if(!empty($value['prize_level'])){?>
                        <?php foreach($value['prize_level'] as $k_0=>$v_0){?>
                        <?php echo $k_0==0 ? '' : '<br />';?>
                        <?php echo $k_0==0 ? '自销' : $k_0.' 级';?>&nbsp;&nbsp;<?php echo $v_0['member_name'];?>&nbsp;&nbsp;<font style="color:#ff0000; font-size:14px;">&yen;<?php echo $v_0['money'];?></font>
                        <?php }?>
                        <?php }?>
                    </td>
                    <td class="align-center border-right">
                    <?php if($value['record_status']==10){?>
                    <font style="padding:8px 15px; color:#FFF; background:#D9534F; border-radius:5px;">未付款</font>
                    <?php }elseif($value['record_status']==20){?>
                    <font style="padding:8px 15px; color:#FFF; background:#5CB85C; border-radius:5px;">已付款</font>
                    <?php }elseif($value['record_status']==30){?>
                    <font style="padding:8px 15px; color:#FFF; background:#F60; border-radius:5px;">已发货</font>
                    <?php }else{?>
                    <font style="padding:8px 15px; color:#FFF; background:#E41F16; border-radius:5px;">已完成</font>
                    <?php }?>
                    </td>
                    <td class="align-center border-right"><?php echo date('Y-m-d H:i:s',$value['record_addtime']);?></td>
					<td class="align-center">
                    <?php if($value['order_type']==0){?>
                    <a href="index.php?act=order&op=show_order&order_id=<?php echo $value['order_id'];?>">相关订单</a>
                    <?php }else{?>
                    <a href="index.php?act=vr_order&op=show_order&order_id=<?php echo $value['order_id'];?>">相关订单</a>
                    <?php }?>
                    </td>
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
                	<td>&nbsp;</td>
					<td colspan="16"><div class="pagination"> <?php echo $output['show_page'];?> </div></td>
				</tr>
			</tfoot>
		</table>
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