<style type="text/css">
h3.dialog_head {
	margin: 0 !important;
}
.dialog_content {
	width: 900px;
	padding-top:10px;
	padding: 10px 15px 15px 15px !important;
	overflow: hidden;
}
</style>
<link type="text/css" href="/admin/resource/weixin/material.css" rel="stylesheet" />
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_wechat_attention'];?></h3>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
  <input type="hidden" name="rid" value="<?php echo $output['attention_account']['reply_id'];?>" />
  <input type="hidden" name="materialid" id="materialid" value="<?php echo $output['attention_account']['reply_materialid'];?>" />
    <table class="table tb-type2">
      <tbody>
        <tr>
          <td colspan="2" class="required"><label for="reply_msgtype"><?php echo $lang['reply_type']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <?php foreach($lang['reply_type_name'] as $key=>$value){?>
           <?php if($key<2){?>
           	<input type="radio" name="msgtype" value="<?php echo $key;?>" id="msgtype_<?php echo $key;?>"<?php echo $output['attention_account']['reply_msgtype']==$key ? ' checked' : '';?> /><label for="msgtype_<?php echo $key;?>"><?php echo $value;?></label>&nbsp;&nbsp;
           <?php }?>
           <?php }?>
          </td>
        </tr>
        <tr class="msgtype_0"<?php echo $output['attention_account']['reply_msgtype']==1 ? ' style="display:none"' : '';?>>
          <td colspan="2" class="required"><label for="textcontents"><?php echo $lang['reply_content'];?>:</label></td>
        </tr>
        <tr class="noborder msgtype_0"<?php echo $output['attention_account']['reply_msgtype']==1 ? ' style="display:none"' : '';?>>
          <td class="vatop rowform">
          	<textarea name="textcontents" id="textcontents" class="tarea"><?php echo $output['attention_account']['reply_textcontents'];?></textarea>
          </td>
          <td class="vatop tips"></td>
        </tr>
        <tr class="msgtype_1"<?php echo $output['attention_account']['reply_msgtype']==0 ? ' style="display:none"' : '';?>>
          <td colspan="2" class="required"><label for="materialid"><?php echo $lang['reply_material'];?>:</label></td>
        </tr>
        <tr class="noborder msgtype_1"<?php echo $output['attention_account']['reply_msgtype']==0 ? ' style="display:none"' : '';?>>
          <td class="vatop rowform">
          	[<a href="JavaScript:show_dialog('material_list');" style="color:#0099D8"><?php echo $lang['material_select_btn'];?></a>]
            <div id="material_confirm" class="material_dialog"<?php echo $output['attention_account']['reply_msgtype']==0 ? ' style="display:none"' : '';?>>
              <div class="list">
            	<?php if(!empty($output['material_info'])){?>
                <?php if($output['material_info']['material_type']==2){?>
                <div class="item multi">
                  <div class="time"><?php echo date("Y-m-d",$output['material_info']['material_addtime']);?></div>
                  <?php foreach($output['material_info']['items'] as $k=>$v){?>
                  <div class="<?php echo $k>0 ? "list" : "first" ?>">
                    <div class="info">
                      <div class="img"><img src="<?php echo UPLOAD_SITE_URL.$v['ImgPath'] ?>" /></div>
                      <div class="title"><?php echo $v['Title'] ?></div>
                    </div>
                  </div>
                  <?php }?>
                </div>
                <?php }else{?>
                <div class="item one">
                <?php foreach($output['material_info']['items'] as $k=>$v){?>
                  <div class="title"><?php echo $v['Title'] ?></div>
                  <div><?php echo date("Y-m-d",$output['material_info']['material_addtime']) ?></div>
                  <div class="img"><img src="<?php echo UPLOAD_SITE_URL.$v['ImgPath'] ?>" /></div>
                  <div class="txt"><?php echo str_replace(array("\r\n", "\r", "\n"), "<br />",$v['TextContents']);?></div>
                <?php }?>
                <?php }?>
                <?php }else{?>
            	<div class="item"></div>
                <?php }?>
              </div>
            </div>
          </td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="subscribe"><?php echo $lang['attention_each_keyword'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input type="radio" name="subscribe" value="1" id="subscribe_1"<?php echo $output['attention_account']['reply_subscribe']==1 ? ' checked' : '';?> /><label for="subscribe_1"><?php echo $lang['open_btn'];?></label>&nbsp;&nbsp;
          <input type="radio" name="subscribe" value="0" id="subscribe_0"<?php echo $output['attention_account']['reply_subscribe']==0 ? ' checked' : '';?> /><label for="subscribe_0"><?php echo $lang['close_btn'];?></label>&nbsp;&nbsp;
          <br />
          <span style="padding:10px 0px; color:#999"><?php echo $lang['attention_each_keyword_tips'];?></span>
          </td>
          <td class="vatop tips"></td>
        </tr>   
        <tr>
          <td colspan="2" class="required"><label for="subscribe"><?php echo $lang['attention_user_notice'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input type="radio" name="membernotice" value="1" id="membernotice_1"<?php echo $output['attention_account']['reply_membernotice']==1 ? ' checked' : '';?> /><label for="membernotice_1"><?php echo $lang['open_btn'];?></label>&nbsp;&nbsp;
          <input type="radio" name="membernotice" value="0" id="membernotice_0"<?php echo $output['attention_account']['reply_membernotice']==0 ? ' checked' : '';?> /><label for="membernotice_0"><?php echo $lang['close_btn'];?></label>&nbsp;&nbsp;
          <br />
          <span style="padding:10px 0px; color:#999"><?php echo $lang['attention_user_notice_tips'];?></span>
          </td>
          <td class="vatop tips"></td>
        </tr>   
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="2" ><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>

<!-- 促销区商品推荐模块 -->
<div id="material_list_dialog" style="display:none;">
  <div class="dialog-show-box">
    <table class="tb-type1 noborder search" style="margin-top:8px;">
      <tbody>
        <tr>
          <td>
          	<select name="material_type" id="material_type">
          	  <option value="0">全部</option>
              <?php foreach($lang['material_type'] as $tid=>$tname){?>
              <option value="<?php echo $tid;?>" ><?php echo $tname;?></option>
              <?php }?>
            </select>
          </td>
          <td>
          <a href="JavaScript:void(0);" onclick="get_material_list();" class="btn-search " title="<?php echo $lang['nc_query'];?>"></a></td>
        </tr>
      </tbody>
    </table>
    <div id="show_material_list"></div>
    <div class="clear"></div>
  </div>
  <div class="clear"></div>
</div>

<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>

<script type="text/javascript">
$(function(){
	$('input[name=msgtype]').click(function(){
		if($(this).val()==0){
			$('.msgtype_1').hide();
			$('.msgtype_0').show();
		}else{
			$('.msgtype_0').hide();
			$('.msgtype_1').show();
		}
	});
	 
	$("#submitBtn").click(function(){
        $("#add_form").submit();
    });
});

DialogManager.close = function(id) {
	__DIALOG_WRAPPER__[id].hide();
	ScreenLocker.unlock();
}

DialogManager.show = function(id) {
	if (__DIALOG_WRAPPER__[id]) {
		__DIALOG_WRAPPER__[id].show();
		ScreenLocker.lock();
		return true;
	}
	return false;
}

var titles = new Array();
titles["material_list"] = '素材列表';

function show_dialog(id) {//弹出框
	if(DialogManager.show(id)) return;
	var d = DialogManager.create(id);//不存在时初始化(执行一次)
	var dialog_html = $("#"+id+"_dialog").html();
	$("#"+id+"_dialog").remove();
	d.setTitle(titles[id]);
	d.setContents('<div id="'+id+'_dialog" class="'+id+'_dialog">'+dialog_html+'</div>');
	d.setWidth(930);
	d.show('center',1);
	get_material_list();
}
function replace_url(url) {//去当前网址
	return url.replace(UPLOAD_SITE_URL+"/", '');
}

function get_material_list(){//查询商品
	var material_type;
	material_type = $('#material_type').val();
	$("#show_material_list").load('index.php?act=wechat&op=material_list&'+$.param({'type':material_type}));
}

function select_material(id,type){//商品选择
	if(type==2){
		$('#material_confirm .list .item').removeClass('one');
		$('#material_confirm .list .item').addClass('multi');
	}else{
		$('#material_confirm .list .item').removeClass('multi');
		$('#material_confirm .list .item').addClass('one');
	}
	$('#material_confirm .list .item').html($('#select_'+id).html());
	$('#material_confirm .list .item .mod_del').hide();
	$('#material_confirm').show();
	$('#materialid').val(id);
	DialogManager.close("material_list");
}
</script>