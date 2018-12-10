<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>提现方式管理</h3>
      <ul class="tab-base">
        <li><a class="current"><span>提现方式管理</span></a></li>
        <li><a href="index.php?act=distributor_list&op=withdrawmethodadd"><span>新增</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
  <input type="hidden" name="mid" value="<?php echo $_GET['mid']?>" />
    <table class="table tb-type2">
      <tbody>
      	<?php if($output['method_info']['method_code']=='bank'){?>
        <tr>
          <td colspan="2" class="required"><label for="name">银行名称:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input id="name" name="name" value="<?php echo $output['method_info']['method_name'];?>" class="txt" type="text">
          </td>
        </tr>
        <?php }else{?>
        <tr>
          <td colspan="2" class="required">提现方式:</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <?php echo $output['method_info']['method_name'];?>
          </td>
        </tr>
		<?php if($output['method_info']['method_code']=='wxhongbao' || $output['method_info']['method_code']=='wxzhuanzhang'){?>
        <tr>
          <td colspan="2" class="required">是否需要审核</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input type="radio" name="check" value="1"<?php echo $output['method_info']['method_check']==1 ? ' checked' : '';?> /> 是&nbsp;&nbsp;<input type="radio" name="check" value="0"<?php echo $output['method_info']['method_check']==0 ? ' checked' : '';?> /> 否
          </td>
        </tr>
		<?php }?>
        <?php }?>
		<tr>
          <td colspan="2" class="required">每次提现最小额度</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input id="min" name="min" value="<?php echo $output['method_info']['method_min']?>" class="txt" type="text" style="width:80px; text-align:center">
          </td>
        </tr>
		<tr>
          <td colspan="2" class="required">每次提现最大额度</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input id="max" name="max" value="<?php echo $output['method_info']['method_max']?>" class="txt" type="text" style="width:80px; text-align:center">
          </td>
        </tr>
		<?php if($output['method_info']['method_code']!='yue'){?>
		<tr>
          <td colspan="2" class="required">手续费</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input id="fee" name="fee" value="<?php echo $output['method_info']['method_fee']?>" class="txt" type="text" style="width:80px; text-align:center"> %
          </td>
        </tr>
		<tr>
          <td colspan="2" class="required">转入余额比例</td>
        </tr>
		<tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input id="yue" name="yue" value="<?php echo $output['method_info']['method_yue']?>" class="txt" type="text" style="width:80px; text-align:center"> %
          </td>
        </tr>
		<?php }?>
        <tr>
          <td colspan="2" class="required">是否启用</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <input type="radio" name="status" value="1"<?php echo $output['method_info']['method_status']==1 ? ' checked' : '';?> /> 启用&nbsp;&nbsp;<input type="radio" name="status" value="0"<?php echo $output['method_info']['method_status']==0 ? ' checked' : '';?> /> 禁用
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
        	}
        },
        messages : {
        	name: {
        		required : '请填写银行名称'
        	}
        }
	});
})
</script>