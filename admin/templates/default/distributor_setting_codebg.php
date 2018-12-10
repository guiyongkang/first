<script type="text/javascript">
$(document).ready(function(){
    $("#submit").click(function(){
        $("#add_form").submit();
    });
	
	$("#clear").click(function(){
        window.location.href="?act=distributor&op=clear_qrcode";
    });
});
</script>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_distributor_setting'];?></h3>
      <?php echo $output['top_link'];?>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
  <input type="hidden" name="itemid" value="<?php echo $output['setting']['item_id'];?>" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required">二维码背景图</td>
        </tr>
        <tr class="noborder">
			<td class="vatop rowform"><span class="type-file-show"><img class="show_image" src="<?php echo APP_TEMPLATES_URL;?>/images/preview.png">
			<div class="type-file-preview"><?php echo $output['setting']['qrcode_bg'] ? '<img src="'. UPLOAD_SITE_URL .$output['setting']['qrcode_bg'].'" />' : '';?></div>
			</span><span class="type-file-box">
			<input type='text' name='thumb' id='thumb' value="<?php echo $output['setting']['qrcode_bg'] ? UPLOAD_SITE_URL.$output['setting']['qrcode_bg'] : '';?>" class='type-file-text' />
			<input type='button' name='button' id='button1' value='' class='type-file-button' />
			<input name="_pic" type="file" class="type-file-file" id="_pic" size="30" hidefocus="true">
			</span></td>
			<td class="vatop tips"><span class="vatop rowform">最佳显示尺寸为640*1010像素</span></td>
		</tr>
		<tr class="noborder">
          <td colspan="2" class="required">昵称颜色</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<input name="titlecolor" id="titlecolor" value="<?php echo $output['setting']['title_color'];?>" class="txt" type="text" style="width:80px">
          </td>
          <td class="vatop tips"></td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">二维码距离头部</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<input name="qrcodetop" value="<?php echo $output['setting']['qrcode_top'];?>" class="txt" type="text" style="width:80px"> px
          </td>
          <td class="vatop tips">单位px</td>
        </tr>
		<tr class="noborder">
          <td colspan="2" class="required">二维码距离左边</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<input name="qrcodeleft" value="<?php echo $output['setting']['qrcode_left'];?>" class="txt" type="text" style="width:80px"> px
          </td>
          <td class="vatop tips">单位px</td>
        </tr>
		<tr class="noborder">
          <td colspan="2" class="required">二维码宽度</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<input name="qrcodewidth" value="<?php echo $output['setting']['qrcode_width'];?>" class="txt" type="text" style="width:80px"> px
          </td>
          <td class="vatop tips">单位px；二维码宽高相同</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2"><a id="submit" href="javascript:void(0)" class="btn"><span><?php echo $lang['nc_submit'];?></span></a>&nbsp;&nbsp;<a id="clear" href="javascript:void(0)" class="btn"><span>清除会员二维码海报</span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/colorpicker/evol.colorpicker.css" rel="stylesheet" type="text/css">
<script src="<?php echo RESOURCE_SITE_URL;?>/js/colorpicker/evol.colorpicker.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript">
$(function(){
	$('#titlecolor').colorpicker({showOn:'both'});
    $('#titlecolor').parent().css("width",'');
    $('#titlecolor').parent().addClass("color");
	
	$('input[class="type-file-file"]').change(uploadChange);
	
	function uploadChange(){
		var filepatd=$(this).val();
		var extStart=filepatd.lastIndexOf(".");
		var ext=filepatd.substring(extStart,filepatd.lengtd).toUpperCase();		
		if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
			alert("file type error");
			$(this).attr('value','');
			return false;
		}
		if ($(this).val() == ''){
			return false;
		}
		ajaxFileUpload();
	}
	
	function ajaxFileUpload(){
		$.ajaxFileUpload
		(
			{
				url:'index.php?act=common&op=pic_upload&form_submit=ok&uploadpath=distributor&width=640&height=1010',
				secureuri:false,
				fileElementId:'_pic',
				dataType: 'json',
				success: function (data, status)
				{
					if (data.status == 1){
						$('.type-file-preview').html('<img src="'+data.url+'" />');
						$('#thumb').val(data.url);
					}else{
						alert(data.msg);
					}
					$('input[class="type-file-file"]').bind('change',uploadChange);
				},
				error: function (data, status, e)
				{
					alert('上传失败');$('input[class="type-file-file"]').bind('change',uploadChange);
				}
			}
		)
	};
});
</script>