<?php defined('SAFE_CONST') or exit('Access Invalid!');?>
<?php echo getChat($layout);?>
<div id="footer" class="wrapper" style="padding-top:10px;">
	<?php echo html_entity_decode($output['setting_config']['statistics_code'],ENT_QUOTES); ?><br/>
	<?php echo $output['setting_config']['icp_number']; ?>
</div>
<?php if ($output['setting_config']['debug'] == 1){?>
<div id="think_page_trace" class="trace">
	<fieldset id="querybox">
		<legend><?php echo $lang['nc_debug_trace_title'];?></legend>
		<div> <?php print_r(core\tpl::showTrace());?> </div>
	</fieldset>
</div>
<?php }?>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.cookie.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/qtip/jquery.qtip.min.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.lazyload.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/qtip/jquery.qtip.min.css" rel="stylesheet" type="text/css">
<script src="<?php echo APP_RESOURCE_SITE_URL;?>/js/compare.js"></script> 
<script type="text/javascript">
$(function(){
	// Membership card
	$('[nctype="mcard"]').membershipCard({type:'shop'});
});
</script>