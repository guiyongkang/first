<?php defined('SAFE_CONST') or exit('Access Invalid!');?>
<link href="<?php echo APP_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo APP_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome-ie7.min.css">
<![endif]-->
<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3><?php echo $lang['goods_index_goods'];?></h3>
			<ul class="tab-base">
				<li><a href="index.php?act=distributor&op=goodslist"><span><?php echo $lang['goods_index_all_goods'];?></span></a></li>
				<li><a href="JavaScript:void(0);" class="current"><span>导入商品</span></a></li>
			</ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form method="get" name="formSearch" id="formSearch">
		<input type="hidden" name="act" value="distributor">
		<input type="hidden" name="op" value="goodsimport">
		<table class="tb-type1 noborder search">
			<tbody>
				<tr>
					<th><label for="search_goods_name"> <?php echo $lang['goods_index_name'];?></label></th>
					<td><input type="text" value="<?php echo isset($output['search']['search_goods_name']) ? $output['search']['search_goods_name'] : '';?>" name="search_goods_name" id="search_goods_name" class="txt"></td>
					<th><label><?php echo $lang['goods_index_class_name'];?></label></th>
					<td id="searchgc_td"></td>
					<input type="hidden" id="choose_gcid" name="choose_gcid" value="0"/>
                    <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
				</tr>
			</tbody>
		</table>
	</form>
	
	<form method='post' id="form_goods" action="index.php?act=distributor&op=importgoods">
		<input type="hidden" name="form_submit" value="ok" />
		<table class="table tb-type2">
			<thead>
				<tr class="thead">
					<th class="w24"></th>
					<th class="w24"></th>
					<th class="w60 align-center">平台货号</th>
					<th colspan="2"><?php echo $lang['goods_index_name'];?></th>
					<th><?php echo $lang['goods_index_brand'];?>&<?php echo $lang['goods_index_class_name'];?></th>
					<th class="w72 align-center">价格(积分)</th>
					<th class="w72 align-center">库存</th>
					<th class="w108 align-center"><?php echo $lang['nc_handle'];?> </th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($output['goods_list']) && is_array($output['goods_list'])) { ?>
				<?php foreach ($output['goods_list'] as $k => $v) {?>
				<tr class="hover edit">
					<td><input type="checkbox" name="gid[]" value="<?php echo $v['goods_commonid'];?>" class="checkitem"></td>
					<td><i class="icon-plus-sign" style="cursor: pointer;" nctype="ajaxGoodsList" data-comminid="<?php echo $v['goods_commonid'];?>" title="点击展开查看此商品全部规格；规格值过多时请横向拖动区域内的滚动条进行浏览。"></i></td>
					<td class="align-center"><?php echo $v['goods_commonid'];?></td>
					<td class="w60 picture"><div class="size-56x56"><span class="thumb size-56x56"><i></i><img src="<?php echo thumb($v, 60);?>" onload="javascript:DrawImage(this,56,56);"/></span></div></td>
					<td><?php echo $v['goods_name'];?></td>
					<td><p><?php echo $v['gc_name'];?></p>
						<p class="goods-brand">品牌：<?php echo $v['brand_name'];?></p></td>
					<td class="align-center"><?php echo $v['goods_price']?></td>
					<td class="align-center"><?php echo isset($output['storage_array'][$v['goods_commonid']]['sum']) ? $output['storage_array'][$v['goods_commonid']]['sum'] : 0?></td>
					<td class="align-center"><a href="index.php?act=distributor&op=importgoods&gid=<?php echo $v['goods_commonid'];?>">导入</a></td>
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
						&nbsp;&nbsp;<a href="JavaScript:void(0);" class="btn" nctype="import_batch"><span>导入</span></a>
						<div class="pagination"> <?php echo $output['page'];?> </div></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript">
var SITEURL = "<?php echo APP_URL; ?>";
$(function(){
	//商品分类
	init_gcselect(<?php echo $output['gc_choose_json'];?>,<?php echo $output['gc_json']?>);

    $('#ncsubmit').click(function(){
        $('#formSearch').submit();
    });

    // 导入处理
    $('a[nctype="import_batch"]').click(function(){
        $('#form_goods').submit();
    });

    // ajax获取商品列表
    $('i[nctype="ajaxGoodsList"]').toggle(
        function(){
            $(this).removeClass('icon-plus-sign').addClass('icon-minus-sign');
            var _parenttr = $(this).parents('tr');
            var _commonid = $(this).attr('data-comminid');
            var _div = _parenttr.next().find('.ncsc-goods-sku');
            if (_div.html() == '') {
                $.getJSON('index.php?act=goods&op=get_goods_list_ajax' , {commonid : _commonid}, function(date){
                    if (date != 'false') {
                        var _ul = $('<ul class="ncsc-goods-sku-list"></ul>');
                        $.each(date, function(i, o){
                            $('<li><div class="goods-thumb" title="商家货号：' + o.goods_serial + '"><image src="' + o.goods_image + '" ></div>' + o.goods_spec + '<div class="goods-price">价格：<em title="￥' + o.goods_price + '">￥' + o.goods_price + '</em></div><div class="goods-storage">库存：<em title="' + o.goods_storage + '">' + o.goods_storage + '</em></div></li>').appendTo(_ul);
                            });
                        _ul.appendTo(_div);
                        _parenttr.next().show();
                        // 计算div的宽度
                        _div.css('width', document.body.clientWidth-54);
                        _div.perfectScrollbar();
                    }
                });
            } else {
            	_parenttr.next().show()
            }
        },
        function(){
            $(this).removeClass('icon-minus-sign').addClass('icon-plus-sign');
            $(this).parents('tr').next().hide();
        }
    );
});
</script> 