<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['wechat_url_manage'];?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_manage'];?></span></a></li>
        <li><a href="index.php?act=wechat&op=url_add" ><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch">
    <input type="hidden" name="act" value="wechat">
    <input type="hidden" name="op" value="url_manage">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th>
          <select name="fields">
          <?php foreach($lang['wechat_url_select_type'] as $f_k=>$f){?>
          <option value="<?php echo $f_k;?>"<?php echo !empty($_GET['fields']) && $_GET['fields']==$f_k ? ' selected' : '';?>><?php echo $f;?></option>
          <?php }?>
          </th>
          <td><input type="text" name="keywords" id="keywords" class="txt" value='<?php echo empty($_GET['keywords']) ? '' : $_GET['keywords'];?>'></td>          
          <td><a href="javascript:document.formSearch.submit();" class="btn-search " title="<?php echo $lang['nc_query']; ?>">&nbsp;</a></td>
        </tr>
      </tbody>
    </table>
  </form>
  <form id="listform" action="index.php" method='post'>
    <input type="hidden" name="act" value="wechat" />
    <input type="hidden" id="listop" name="op" value="url_del" />
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="w24 align-center">&nbsp;</th>
          <th class="w300 align-center"><?php echo $lang['wechat_url_name'];?></th>
          <th class="align-center"><?php echo $lang['wechat_url_link'];?></th>
          <th class="w150 align-center"><?php echo $lang['nc_handle'];?></th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['url_list']) && is_array($output['url_list'])){ ?>
        <?php foreach($output['url_list'] as $key => $value){ ?>
        <tr class="hover edit row">
          <td><input type="checkbox" name='urlid[]' value="<?php echo $value['url_id'];?>" class="checkitem"></td>
          <td class="align-center"><?php echo trim($value['url_name']);?></td>
          <td class="align-center"><?php echo trim($value['url_link']);?></td>
          <td class="align-center">
          	<a href="index.php?act=wechat&op=url_edit&urlid=<?php echo $value['url_id'];?>"><?php echo $lang['nc_edit'];?></a>&nbsp;|&nbsp;
          	<a href="javascript:void(0)" onclick="if(confirm('<?php echo $lang['nc_ensure_del'];?>')){location.href='index.php?act=wechat&op=url_del&urlid=<?php echo $value['url_id'];?>';}"><?php echo $lang['nc_del'];?></a>
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
        <?php if(!empty($output['url_list']) && is_array($output['url_list'])){ ?>
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
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.goods_class.js" charset="utf-8"></script>
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