<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['wechat_url_manage'];?></h3>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
        <tr>
          <td colspan="2" class="required"><label for="keywords"><?php echo $lang['wechat_url_name']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input id="name" name="name" value="" class="txt" type="text">
          </td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="keywords"><?php echo $lang['wechat_url_link']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input id="urllink" name="urllink" value="" class="txt" type="text">
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
<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/jquery.ui.js";?>"></script> 
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/i18n/zh-CN.js";?>" charset="utf-8"></script>
<script type="text/javascript">
$(function(){	 
	$("#submitBtn").click(function(){
		if($("#add_form").valid()){
			$("#add_form").submit();
		}
    });
	
	$("#add_form").validate({
		errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
        },
        rules : {
        	name: {
        		required : true
        	},
        	urllink: {
        		required : true
        	}
        },
        messages : {
        	name: {
        		required : '<?php echo $lang['not_info_url_name'];?>'
        	},
        	urllink: {
        		required : '<?php echo $lang['not_info_url_link'];?>'
        	}
        }
	});
})
</script>