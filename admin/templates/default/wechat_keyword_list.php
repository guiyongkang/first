<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_wechat_keywords'];?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_manage'];?></span></a></li>
        <li><a href="index.php?act=wechat&op=keyword_add" ><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch">
    <input type="hidden" name="act" value="wechat">
    <input type="hidden" name="op" value="keyword_manage">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="keywords"><?php echo $lang['wechat_keywords']; ?></label></th>
          <td><input type="text" name="keywords" id="keywords" class="txt" value='<?php echo empty($_GET['keywords']) ? '' : $_GET['keywords'];?>'></td>
          <td><select name="type">
            <option value="0"><?php echo $lang['wechat_select_all']?></option>
          	<?php foreach($lang['reply_type_name'] as $k=>$v){ if($k>=2){continue;}?>
              <option value="<?php echo $k+1;?>" <?php echo !empty($_GET['type']) && $_GET['type']==($k+1) ? ' selected' : '';?>><?php echo $v;?></option>
            <?php }?>
            </select>
          </td>
          <td><a href="javascript:document.formSearch.submit();" class="btn-search " title="<?php echo $lang['nc_query']; ?>">&nbsp;</a></td>
        </tr>
      </tbody>
    </table>
  </form>
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th class="nobg" colspan="12"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
    </tbody>
  </table>
  <form id="listform" action="index.php" method='post'>
    <input type="hidden" name="act" value="wechat" />
    <input type="hidden" id="listop" name="op" value="keyword_del" />
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="w24">&nbsp;</th>
          <th class="w270"><?php echo $lang['wechat_keywords'];?></th>
          <th class="w96"><?php echo $lang['reply_type'];?></th>
          <th class="align-center"><?php echo $lang['reply_content'];?></th>
          <th class="w150 align-center"><?php echo $lang['reply_pattern_type'];?></th>
          <th class="w150 align-center"><?php echo $lang['nc_handle'];?></th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['reply_list']) && is_array($output['reply_list'])){ ?>
        <?php foreach($output['reply_list'] as $key => $value){ ?>
        <tr class="hover edit row">
          <td><input type="checkbox" name='rid[]' value="<?php echo $value['reply_id'];?>" class="checkitem"></td>
          
          <td class="name"><?php echo trim($value['reply_keywords'],'|');?></td>
          <td class="name"><?php echo $lang['reply_type_name'][$value['reply_msgtype']];?></td>
          <td class="align-center"><?php echo $value['reply_msgtype']==1 ? $lang['reply_material'] : $value['reply_textcontents'];?></td>
          <td class="align-center"><?php echo $lang['reply_pattern_type_name'][$value['reply_patternmethod']];?></td>
          <td class="align-center">
          	<a href="index.php?act=wechat&op=keyword_edit&rid=<?php echo $value['reply_id'];?>"><?php echo $lang['nc_edit'];?></a>&nbsp;|&nbsp;
          	<a href="javascript:void(0)" onclick="if(confirm('<?php echo $lang['nc_ensure_del'];?>')){location.href='index.php?act=wechat&op=keyword_del&rid=<?php echo $value['reply_id'];?>';}"><?php echo $lang['nc_del'];?></a>
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
        <?php if(!empty($output['reply_list']) && is_array($output['reply_list'])){ ?>
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