<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['wechat_menu_manage'];?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_manage'];?></span></a></li>
        <li><a href="index.php?act=wechat&op=menu_add" ><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="listform" action="index.php" method='post'>
    <input type="hidden" name="act" value="wechat" />
    <input type="hidden" id="listop" name="op" value="menu_del" />
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="w24 align-center">&nbsp;</th>
          <th class="align-center"><?php echo $lang['wechat_menu_title'];?></th>
          <th class="w300 align-center"><?php echo $lang['wechat_is_useful'];?></th>
          <th class="w150 align-center"><?php echo $lang['wechat_addtime'];?></th>
          <th class="w300 align-center"><?php echo $lang['nc_handle'];?></th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['menu_list']) && is_array($output['menu_list'])){ ?>
        <?php foreach($output['menu_list'] as $key => $value){ ?>
        <tr class="hover edit row">
          <td><input type="checkbox" name='mid[]' value="<?php echo $value['menu_id'];?>" class="checkitem"></td>
          <td class="align-center"><?php echo trim($value['menu_name']);?></td>
          <td class="align-center"><?php echo $value['menu_status']==0 ? '<span style="background:#D9534F; color:#FFF; padding:2px 30px; border-radius:5px;">未启用</span>' : '<span style="background:#5CB85C; color:#FFF; padding:5px 30px; border-radius:5px;">已在微信端生效</span>';?></td>
          <td class="align-center"><?php echo date('Y-m-d H:i:s',$value['menu_addtime']);?></td>
          <td class="align-center">
			<?php if($value['menu_status']==0){?>
			<a href="index.php?act=wechat&op=menu_publish&mid=<?php echo $value['menu_id'];?>"><?php echo $lang['menu_publish_to_weixin'];?></a>&nbsp;|&nbsp;
			<?php }?>
          	<a href="index.php?act=wechat&op=menu_edit&mid=<?php echo $value['menu_id'];?>"><?php echo $lang['nc_edit'];?></a>&nbsp;|&nbsp;<a href="javascript:void(0)" onclick="if(confirm('<?php echo $lang['nc_ensure_del'];?>')){location.href='index.php?act=wechat&op=menu_del&mid=<?php echo $value['menu_id'];?>';}"><?php echo $lang['nc_del'];?></a>
          </td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="10"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <?php if(!empty($output['menu_list']) && is_array($output['menu_list'])){ ?>
        <tr class="tfoot">
          <td><input type="checkbox" class="checkall" id="checkallBottom" name="chkVal"></td>
          <td colspan="16"><label for="checkallBottom"><?php echo $lang['nc_select_all']; ?></label>
            &nbsp;&nbsp;<a href="JavaScript:void(0);" class="btn" onclick="submit_form('del');"><span><?php echo $lang['nc_del'];?></span></a>
            <div class="pagination"> <?php echo $output['show_page'];?> </div></td>
        </tr>
        <?php } ?>
      </tfoot>
    </table>
  </form>
</div>
<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/jquery.ui.js";?>"></script> 
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/i18n/zh-CN.js";?>" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script>
<script type="text/javascript">
function submit_form(op){
	if(op=='del'){
		if(!confirm('<?php echo $lang['nc_ensure_del'];?>')){
			return false;
		}
	}
	$('#listform').submit();
}
</script>