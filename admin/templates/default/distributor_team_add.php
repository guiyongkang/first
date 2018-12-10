<script type="text/javascript">
$(document).ready(function(){
    $("#submit").click(function(){
		if($('#name').val()==""){
			alert('请填写级别名称');
			$('#name').focus();
			return false;
		}
		if($('#invitenum').val()==""){
			alert('请填写直推人数');
			$('#invitenum').focus();
			return false;
		}
        $("#add_form").submit();
    });
});
</script>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>股东分红</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=team&op=lists" class="current"><span>股东级别管理</span></a></li>
        <li><a href="index.php?act=team&op=record"><span>分红明细</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
      	<tr class="noborder">
          <td colspan="2" class="required">级别名称</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input id="name" name="name" value="" class="txt" type="text">
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">升级条件</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          直推人数需达到 <input id="invitenum" name="invitenum" value="" class="txt" type="text" style="width:80px"> 人
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