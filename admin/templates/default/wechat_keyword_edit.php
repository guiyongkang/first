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
      <h3><?php echo $lang['nc_wechat_keywords'];?></h3>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
  <input type="hidden" name="rid" id="rid" value="<?php echo $_GET['rid'];?>" />
  <input type="hidden" name="materialid" id="materialid" value="<?php echo $output['reply_info']['reply_materialid'];?>" />
    <table class="table tb-type2">
      <tbody>
        <tr>
          <td colspan="2" class="required"><label for="keywords"><?php echo $lang['wechat_keywords']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input id="keywords" name="keywords" value="<?php echo trim($output['reply_info']['reply_keywords'],'|');?>" class="txt" type="text">
           <span style="padding-top:5px; color:#999"><?php echo $lang['wechat_keywords_notice']?></span>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="msgtype"><?php echo $lang['reply_type']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <?php foreach($lang['reply_type_name'] as $key=>$value){?>
           <?php if($key<2){?>
           	<input type="radio" name="msgtype" value="<?php echo $key;?>" id="msgtype_<?php echo $key;?>"<?php echo $output['reply_info']['reply_msgtype']==$key ? ' checked' : '';?> /><label for="msgtype_<?php echo $key;?>"><?php echo $value;?></label>&nbsp;&nbsp;
           <?php }?>
           <?php }?>
          </td>
        </tr>
        <tr class="msgtype_0"<?php echo $output['reply_info']['reply_msgtype']==1 ? ' style="display:none"' : '';?>>
          <td colspan="2" class="required"><label for="textcontents"><?php echo $lang['reply_content'];?>:</label></td>
        </tr>
        <tr class="noborder msgtype_0"<?php echo $output['reply_info']['reply_msgtype']==1 ? ' style="display:none"' : '';?>>
          <td class="vatop rowform">
          	<textarea name="textcontents" id="textcontents" class="tarea"><?php echo $output['reply_info']['reply_textcontents'];?></textarea>
          </td>
          <td class="vatop tips"></td>
        </tr>
        <tr class="msgtype_1"<?php echo $output['reply_info']['reply_msgtype']==0 ? ' style="display:none"' : '';?>>
          <td colspan="2" class="required"><label for="materialid"><?php echo $lang['reply_material'];?>:</label></td>
        </tr>
        <tr class="noborder msgtype_1"<?php echo $output['reply_info']['reply_msgtype']==0 ? ' style="display:none"' : '';?>>
          <td class="vatop rowform">
          	[<a href="JavaScript:show_dialog('material_list');" style="color:#0099D8"><?php echo $lang['material_select_btn'];?></a>]
            <div id="material_confirm" class="material_dialog"<?php echo $output['reply_info']['reply_msgtype']==0 ? ' style="display:none"' : '';?>>
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
                </div>
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
          <td colspan="2" class="required"><label for="patternmethod"><?php echo $lang['reply_pattern_type'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
          <?php foreach($lang['reply_pattern_type_name'] as $kk=>$vv){?>
           	<input type="radio" name="patternmethod" value="<?php echo $kk;?>" id="patternmethod_<?php echo $kk;?>"<?php echo $output['reply_info']['reply_patternmethod']==$kk ? ' checked' : '';?> /><label for="patternmethod_<?php echo $kk;?>"><?php echo $vv;?></label>&nbsp;&nbsp;<span style="color:#999"><?php echo $lang['wechat_patternmethod_notice'][$kk];?></span><br />
          <?php }?>
          </td>
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
		$('.msgtype_0').hide();
		$('.msgtype_1').hide();
		$('.msgtype_2').hide();
		$('.msgtype_'+$(this).val()).show();
	});
	 
	$("#submitBtn").click(function(){
		if($("#add_form").valid()){
			$("#add_form").submit();
		}
    });
	
	$('#add_form').validate({
        errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
        },
        rules : {
            keywords : {
                required : true,
                remote   : {                
                url :'index.php?act=wechat&op=ajax&branch=check_keywords',
                type:'get',
                data:{
                    keywords : function(){
                        return $('#keywords').val();
                    },
					rid : function(){
                        return $('#rid').val();
                    }
                  }
                }
            }
        },
        messages : {
            keywords : {
                required : '<?php echo $lang['not_info_keywords'];?>',
                remote   : '<?php echo $lang['info_keywords_exits'];?>'
            }
        }
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