<?php defined('SAFE_CONST') or exit('Access Invalid!');?>
<?php if(!empty($output['goods_list']) && is_array($output['goods_list'])){ ?>

<ul class="dialog-goodslist-s2">
	<?php foreach($output['goods_list'] as $k => $v){ ?>
	<li>
		<div onclick="select_recommends_goods(<?php echo $v['goods_id'];?>);" class="goods-pic"><span class="ac-ico"></span><span class="thumb size-72x72"><i></i> <img goods_id="<?php echo $v['goods_id'];?>" goods_price="<?php echo $v['goods_price'];?>" market_price="<?php echo $v['goods_marketprice'];?>" 
        title="<?php echo $v['goods_name'];?>" src="<?php echo thumb($v, 240);?>" onload="javascript:DrawImage(this,72,72);" /></span></div>
		<div class="goods-name"><a href="<?php echo APP_URL."/index.php?act=goods&goods_id=".$v['goods_id'];?>" target="_blank"><?php echo $v['goods_name'];?></a></div>
	</li>
	<?php } ?>
	<div class="clear"></div>
</ul>
<div id="show_recommends_goods" class="pagination"> <?php echo $output['show_page'];?> </div>
<?php }else { ?>
<p class="no-record"><?php echo $lang['nc_no_record'];?></p>
<?php } ?>
<div class="clear"></div>
<script type="text/javascript">
	$('#show_recommends_goods .demo').ajaxContent({
		target:'#show_recommends_goods_list'
	});
</script>