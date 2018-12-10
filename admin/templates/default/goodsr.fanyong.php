<?php defined('SAFE_CONST') or exit('Access Invalid!');?>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3>全返明细</h3>
			<ul class="tab-base">
				<li><a href="JavaScript:void(0);" class="current"><span>全返明细</span></a></li>
			</ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form method="get" action="<?php echo urlAdmin('goodsr', 'getfanyong');?>" name="formSearch" id="formSearch">
		<input type="hidden" name="act" value="goodsr" />
		<input type="hidden" name="op" value="getfanyong" />
		<table class="tb-type1 noborder search">
			<tbody>			
				<tr>
					<th><label for="query_start_time">下单时间</label></th>
					<td><input class="txt date" type="text" value="<?php echo isset($_GET['query_start_time']) ? $_GET['query_start_time'] : '';?>" id="query_start_time" name="query_start_time">
						<label for="query_start_time">~</label>
						<input class="txt date" type="text" value="<?php echo isset($_GET['query_end_time']) ? $_GET['query_end_time'] : '';?>" id="query_end_time" name="query_end_time"/></td>
					<th>买家</th>
					<td><input class="txt-short" type="text" name="buyer_name" value="<?php echo isset($_GET['buyer_name']) ? $_GET['buyer_name'] : '';?>" /></td>
								
					<td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
				</tr>
			</tbody>
		</table>
	</form>
	
	
	<table class="table tb-type2 nobdb">
		<thead>
			<tr class="thead">
				<th>订单编号</th>
			    <th>商品编号</th>
			    <th>总返天数</th>
			    <th>每天返(%)</th>
				<th>买家</th>
				<th class="align-center">全返时间</th>
				<th class="align-center">返金额</th>
			</tr>
		</thead>
            
		<tbody>
			<?php if(count($output['order_list'])>0){?>
			<?php foreach($output['order_list'] as $order){?>
			<tr class="hover">
				<td><?php echo $order['order_sn'];?></td>
				<td><?php echo $order['goods_id'];?></td>
				<td><?php echo $order['pointsdays'];?></td>	
				<td><?php echo $order['points'];?></td>
				<td><?php echo $order['buyer_name'];?></td>
				<td class="nowrap align-center"><?php echo date('Y-m-d H:i:s',$order['points_time']);?></td>
				<td class="align-center"><?php echo $order['points_price'];?></td>			
			</tr>
			<?php }?>
			<?php }else{?>
			<tr class="no_data">
				<td colspan="15">没有记录</td>
			</tr>
			<?php }?>
		</tbody>
		<tfoot>
			<tr class="tfoot">
				<td colspan="15" id="dataFuncs"><div class="pagination"> <?php echo $output['show_page'];?> </div></td>
			</tr>
		</tfoot>
	</table>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript">
$(function(){
    $('#query_start_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('getfanyong');
    	$('#formSearch').submit();
    });
});
</script>