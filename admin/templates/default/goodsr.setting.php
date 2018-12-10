<?php defined('SAFE_CONST') or exit('Access Invalid!');?>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3><?php echo $lang['goods_index_goods']?></h3>
			<ul class="tab-base">
				<li><a href="<?php echo urlAdmin('goods', 'goods');?>" ><span><?php echo $lang['goods_index_all_goods'];?></span></a></li>
				<li><a href="<?php echo urlAdmin('goods', 'goods', array('type' => 'lockup'));?>"><span><?php echo $lang['goods_index_lock_goods'];?></span></a></li>
				<li><a href="<?php echo urlAdmin('goods', 'goods', array('type' => 'waitverify'));?>"><span>等待审核</span></a></li>
				<li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_goods_set']?></span></a></li>
			</ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form method="post" name="form_goodsverify">
		<input type="hidden" name="form_submit" value="ok" />
		<input type="hidden" name="commonids" value="<?php echo $output['goodscommon_info']['goods_commonid'];?>">
		<table class="table tb-type2">
			<tbody>
			
				<tr class="noborder">
					<td colspan="2" ><label class="gc_name validation" for="gc_name">商品名称: <?php echo $output['goodscommon_info']['goods_name'];?></label></td>
				</tr>
				
				<tr class="noborder">
					<td colspan="2" class="required"><label class="gc_name validation" for="gc_name">每天返利:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="text" maxlength="20" value="<?php echo $output['goodscommon_info']['points'];?>" name="points" id="points" class="txt">%</td>
					<td class="vatop tips"></td>
				</tr>
				<tr class="noborder">
					<td colspan="2" class="required"><label class="gc_name validation" for="gc_name">返利(天):</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input type="text" maxlength="20" value="<?php echo $output['goodscommon_info']['pointsdays'];?>" name="pointsdays" id="pointsdays" class="txt">天</td>
					<td class="vatop tips"></td>
				</tr>
			</tbody>
			<tfoot>
				<tr class="tfoot">
					<td colspan="2" ><a href="JavaScript:void(0);" class="btn" onclick="document.form_goodsverify.submit()"><span><?php echo $lang['nc_submit'];?></span></a></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>