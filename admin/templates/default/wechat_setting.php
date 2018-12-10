<script type="text/javascript">
$(document).ready(function(){

    $("#submit").click(function(){
        $("#add_form").submit();
    });

});
</script>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_wechat_setting'];?></h3>
      
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
  <input type="hidden" name="wid" value="<?php echo $output['api_account']['wechat_id'];?>" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label for="isuse"><?php echo $lang['wechat_isuse'].$lang['nc_colon'];?></label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform onoff">
          	<label for="isuse_1" class="cb-enable <?php if($output['setting']['wechat_isuse'] == '1'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_open'];?>"><span><?php echo $lang['nc_open'];?></span></label>
            <label for="isuse_0" class="cb-disable <?php if($output['setting']['wechat_isuse'] == '0'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_close'];?>"><span><?php echo $lang['nc_close'];?></span></label>
            <input type="radio" id="isuse_1" name="isuse" value="1" <?php echo $output['setting']['wechat_isuse']==1?'checked=checked':''; ?>>
            <input type="radio" id="isuse_0" name="isuse" value="0" <?php echo $output['setting']['wechat_isuse']==0?'checked=checked':''; ?>></td>
          <td class="vatop tips"><?php echo $lang['wechat_isuse_explain'];?></td>
        </tr>
		<tr class="noborder">
          <td colspan="2" class="required">自定义分享标题</td>
        </tr>
        <tr class="noborder">
			<td class="vatop rowform">
            	<input id="sharetitle" name="sharetitle" value="<?php echo $output['api_account']['wechat_share_title'];?>" class="txt" type="text">
            </td>
			<td class="vatop tips">&nbsp;</td>
		</tr>
        <tr class="noborder">
          <td colspan="2" class="required">自定义分享图标</td>
        </tr>
        <tr class="noborder">
			<td class="vatop rowform"><span class="type-file-show"><img class="show_image" src="<?php echo APP_TEMPLATES_URL;?>/images/preview.png">
			<div class="type-file-preview"><?php echo $output['api_account']['wechat_share_logo'] ? '<img src="'. UPLOAD_SITE_URL .$output['api_account']['wechat_share_logo'].'" />' : '';?></div>
			</span><span class="type-file-box">
			<input type='text' name='thumb' id='thumb' value="<?php echo $output['api_account']['wechat_share_logo'] ? UPLOAD_SITE_URL.$output['api_account']['wechat_share_logo'] : '';?>" class='type-file-text' />
			<input type='button' name='button' id='button1' value='' class='type-file-button' />
			<input name="_pic" type="file" class="type-file-file" id="_pic" size="30" hidefocus="true">
			</span></td>
			<td class="vatop tips"><span class="vatop rowform">最佳显示尺寸为100*100像素</span></td>
		</tr>
        <tr class="noborder">
          <td colspan="2" class="required">自定义分享简介</td>
        </tr>
        <tr class="noborder">
			<td class="vatop rowform">
            	<textarea name="sharedesc" rows="6" class="tarea" id="sharedesc" ><?php echo $output['api_account']['wechat_share_desc'];?></textarea>
            </td>
			<td class="vatop tips">&nbsp;</td>
		</tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2"><a id="submit" href="javascript:void(0)" class="btn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script> 
<script type="text/javascript">
$(function(){
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
				url:'index.php?act=common&op=pic_upload&form_submit=ok&uploadpath=weixinshare',
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