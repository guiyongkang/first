<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>提现申请驳回</h3>
      <ul class="tab-base">
        <li><a class="current"><span>提现申请驳回</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
  <input type="hidden" name="rid" value="<?php echo $_GET['rid']?>" />
    <table class="table tb-type2">
      <tbody>
      	
        <tr>
          <td colspan="2" class="required"><label for="note">驳回原因:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <textarea name="note" id="note" class="tarea"></textarea>
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
        	note: {
        		required : true
        	}
        },
        messages : {
        	note: {
        		required : '请输入原因'
        	}
        }
	});
})
</script>