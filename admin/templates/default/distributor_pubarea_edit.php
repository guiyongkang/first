<script type="text/javascript">
$(document).ready(function(){
    $("#submit").click(function(){
		if($('#name').val()==""){
			alert('请填写分区名称');
			$('#name').focus();
			return false;
		}
        $("#add_form").submit();
    });
});
</script>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>公排分区</h3>
      <ul class="tab-base"><li><a href="index.php?act=distributor_list&op=pubareasadd" ><span><?php echo $lang['nc_new'];?></span></a></li></ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
  <input type="hidden" name="tid" value="<?php echo $output['area_info']['item_id'];?>" />
    <table class="table tb-type2">
      <tbody>
      	<tr class="noborder">
          <td colspan="2" class="required">分区名称</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input id="name" name="name" value="<?php echo $output['area_info']['item_name'];?>" class="txt" type="text">
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        <?php if($output['area_info']['item_default']==0){?>
        <tr class="noborder" style="display:none">
          <td colspan="2" class="required">升级条件</td>
        </tr>
        <tr class="noborder" style="display:none">
          <td class="vatop rowform">
          下级满 <input id="condition" name="condition" value="<?php echo $output['area_info']['item_condition'];?>" class="txt" type="text" style="width:60px; text-align:center" /> 人可晋级该分区
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        <?php }?>
        <tr class="noborder">
          <td colspan="2" class="required">没有参与公排本区，访问相关页面提示语</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input id="note" name="note" value="<?php echo $output['area_info']['item_note'];?>" class="txt" type="text">
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">本区所得奖励是否可提现</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input type="radio" name="withdraw" value="1"<?php echo $output['area_info']['is_withdraw']==1 ? ' checked' : '';?> /> 可提现&nbsp;&nbsp;<input type="radio" name="withdraw" value="0"<?php echo $output['area_info']['is_withdraw']==0 ? ' checked' : '';?> /> 禁止提现
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