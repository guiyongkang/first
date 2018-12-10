<!--v3-v12-->
<link type="text/css" href="/admin/resource/weixin/material.css" rel="stylesheet" />
<script type="text/javascript" src="/admin/resource/weixin/blocksit.min.js"></script>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['material_manage'];?></h3>
      <?php echo $output['top_link'];?>
    </div>
  </div>
  
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
  <input type="hidden" value="<?php echo $_GET['act'];?>" name="act">
  <input type="hidden" value="<?php echo $_GET['op'];?>" name="op">
  <table class="tb-type1 noborder search">
  <tbody>
    <tr>
      <th><label>图文类型</label></th>
        <td>
            <select name="material_type">
                <option value=""><?php echo $lang['nc_please_choose'];?>...</option>
                <?php foreach($lang['material_type'] as $k => $v){ ?>
                <option value="<?php echo $k;?>" <?php if(!empty($_GET['material_type']) && $_GET['material_type'] == $k){?>selected<?php }?>><?php echo $v;?></option>
                <?php } ?>
            </select>
        </td>
        <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
    </tr></tbody>
  </table>
  </form>
  <div id="material_list">
 	 <div class="list">
     	<?php if(!empty($output['material_list'])){?>
        <?php foreach($output['material_list'] as $key=>$value){?>
        <?php if($value['material_type']==2){?>
        <div class="item multi">
          <div class="time"><?php echo date("Y-m-d",$value['material_addtime']);?></div>
		  <?php foreach($value['material_content'] as $k=>$v){?>
          <div class="<?php echo $k>0 ? "other" : "first" ?>">
            <div class="info">
              <div class="img"><img src="<?php echo UPLOAD_SITE_URL.$v['ImgPath'] ?>" /></div>
              <div class="title"><?php echo $v['Title'] ?></div>
            </div>
          </div>
		  <?php }?>
          <div class="mod_del">
            <div class="mod"><a href="index.php?act=wechat&op=material_edit&mid=<?php echo $value['material_id'];?>"><img src="/admin/resource/weixin/mod.gif" /></a></div>
            <div class="del"><a href="index.php?act=wechat&op=material_del&mid=<?php echo $value['material_id'];?>" onClick="if(!confirm('<?php echo $lang['material_delete_tips'];?>')){return false};"><img src="/admin/resource/weixin/del.gif" /></a></div>
          </div>
        </div>
        <?php }else{?>
        <div class="item one">
        <?php foreach($value['material_content'] as $k=>$v){?>
          <div class="title"><?php echo $v['Title'] ?></div>
          <div><?php echo date("Y-m-d",$value['material_addtime']) ?></div>
          <div class="img"><img src="<?php echo UPLOAD_SITE_URL.$v['ImgPath'] ?>" /></div>
          <div class="txt"><?php echo str_replace(array("\r\n", "\r", "\n"), "<br />",$v['TextContents']);?></div>
        <?php }?>
          <div class="mod_del">
            <div class="mod"><a href="index.php?act=wechat&op=material_edit&mid=<?php echo $value['material_id'];?>"><img src="/admin/resource/weixin/mod.gif" /></a></div>
            <div class="del"><a href="index.php?act=wechat&op=material_del&mid=<?php echo $value['material_id'];?>" onClick="if(!confirm('<?php echo $lang['material_delete_tips'];?>')){return false};"><img src="/admin/resource/weixin/del.gif" /></a></div>
          </div>
        </div>
        <?php }?>
        <?php }?>
        <?php }?>
     </div>
  </div>
  <table class="table tb-type2">
  	<tfoot>
        <tr class="tfoot">
          <td></td>
          <td colspan="16">
            <div class="pagination"><?php echo $output['page'];?></div></td>
        </tr>
      </tfoot>
  </table>
</div>
<script>
$(function(){
    $('#ncsubmit').click(function(){
    	$('#formSearch').submit();
    });
	
	$(window).load( function() {
		$('#material_list .list').BlocksIt({
			numOfCol: 4,
			offsetX: 8,
			offsetY: 8,
			blockElement: '.item'
		});
	});
	
	//window resize
	var currentWidth = 1460;
	$(window).resize(function() {
		var winWidth = $(window).width();
		var conWidth;
		if(winWidth < 730) {
			conWidth = 365;
			col = 1
		} else if(winWidth < 1095) {
			conWidth = 730;
			col = 2
		} else if(winWidth < 1460) {
			conWidth = 1095;
			col = 4;
		} else{
			conWidth = 1460;
			col = 4;
		}
		if(conWidth != currentWidth) {
			currentWidth = conWidth;
			$('#material_list .list').width(conWidth);
			$('#material_list .list').BlocksIt({
				numOfCol: col,
				offsetX: 8,
				offsetY: 8
			});
		}
	});
});
</script>