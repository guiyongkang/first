<?php defined('SAFE_CONST') or exit('Access Invalid!');?>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3><?php echo $lang['nc_member_predepositmanage'];?></h3>
			<ul class="tab-base">
				<li><a href="index.php?act=predeposit&op=predeposit"><span><?php echo $lang['admin_predeposit_rechargelist']?></span></a></li>
				<li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['admin_predeposit_cashmanage']; ?></span></a></li>
			</ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<table class="table tb-type2 nobdb">
		<tbody>
			<tr class="noborder">
				<td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_sn'];?>:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo $output['info']['pdc_sn']; ?></td>
				<td class="vatop tips"></td>
			</tr>
			<tr>
				<td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_membername'];?>:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo $output['info']['pdc_member_name']; ?></td>
				<td class="vatop tips"></td>
			</tr>
			<tr>
				<td colspan="2" class="required"><label>提现总额:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo $output['info']['pdc_amount']; ?>&nbsp;<?php echo $lang['currency_zh'];?></td>
				<td class="vatop tips"></td>
			</tr>
            <tr>
				<td colspan="2" class="required"><label>手续费:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo $output['info']['pdc_fee']; ?>&nbsp;<?php echo $lang['currency_zh'];?></td>
				<td class="vatop tips"></td>
			</tr>
            <tr>
				<td colspan="2" class="required"><label>应付款:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo $output['info']['pdc_avabled']; ?>&nbsp;<?php echo $lang['currency_zh'];?></td>
				<td class="vatop tips"></td>
			</tr>
			<tr>
				<td colspan="2" class="required"><label>提现方式:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo $output['info']['pdc_bank_name']; ?></td>
				<td class="vatop tips"></td>
			</tr>
			<tr>
				<td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_shoukuanaccount'];?>:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo $output['info']['pdc_bank_no']; ?></td>
				<td class="vatop tips"></td>
			</tr>
			<tr>
				<td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_shoukuanname']?>:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo $output['info']['pdc_bank_user']; ?></td>
				<td class="vatop tips"></td>
			</tr>
            <tr>
				<td colspan="2" class="required"><label>状态:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo str_replace(array('0','1','2'), array('申请中','已支付','已驳回'), $output['info']['pdc_payment_state']); ?></td>
				<td class="vatop tips"></td>
			</tr>
			<?php if (intval($output['info']['pdc_payment_time'])) {?>
			<tr>
				<td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_paytime']; ?>:</label></td>
			</tr>
			<tr class="noborder">
				<td class="vatop rowform"><?php echo @date('Y-m-d',$output['info']['pdc_payment_time']); ?> ( <?php echo $lang['admin_predeposit_adminname'];?>: <?php echo $output['info']['pdc_payment_admin'];?> ) </td>
				<td class="vatop tips"></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
