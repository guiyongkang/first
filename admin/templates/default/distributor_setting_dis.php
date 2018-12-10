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
          <td colspan="2" class="required">成为分销商门槛</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <select name="cometype" style="width:150px;">
          <?php
		  foreach($lang['distributor_cometype'] as $k_t=>$v_t){
			  if($output['level_count']>1){
				  if($k_t==4){
					  continue;
				  }
			  }
		  ?>
          	<option value="<?php echo $k_t;?>"<?php echo $output['setting']['dis_come_type']==$k_t ? ' selected' : '';?>><?php echo $v_t;?></option>
          <?php }?>
          </select>
          </td>
          <td class="vatop tips">若分销商级别为多个，则此处的“购买任意商品”条件无法设置；设置完成后，请设置分销商级别</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">分销商产品获得佣金</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform onoff">
          	<label for="goodsopen_1" class="cb-enable <?php if($output['setting']['dis_goods_open'] == '1'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_open'];?>"><span><?php echo $lang['nc_open'];?></span></label>
            <label for="goodsopen_0" class="cb-disable <?php if($output['setting']['dis_goods_open'] == '0'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_close'];?>"><span><?php echo $lang['nc_close'];?></span></label>
            <input type="radio" id="goodsopen_1" name="goodsopen" value="1" <?php echo $output['setting']['dis_goods_open']==1?'checked=checked':''; ?>>
            <input type="radio" id="goodsopen_0" name="goodsopen" value="0" <?php echo $output['setting']['dis_goods_open']==0?'checked=checked':''; ?>></td>
          <td class="vatop tips">若关闭，则说明分销商分销商品不获得佣金</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">获得佣金级数</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<select name="bonuslevel" style="width:80px;">
            	<?php for($i=1;$i<=9;$i++){?>
                <option value="<?php echo $i;?>"<?php echo $output['setting']['dis_bonus_level'] == $i ? ' selected' : '';?>><?php echo $i;?>级</option>
                <?php }?>
            </select>
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">自销获得佣金</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform onoff">
          	<label for="bonusself_1" class="cb-enable <?php if($output['setting']['dis_bonus_self'] == '1'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_open'];?>"><span><?php echo $lang['nc_open'];?></span></label>
            <label for="bonusself_0" class="cb-disable <?php if($output['setting']['dis_bonus_self'] == '0'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_close'];?>"><span><?php echo $lang['nc_close'];?></span></label>
            <input type="radio" id="bonusself_1" name="bonusself" value="1" <?php echo $output['setting']['dis_bonus_self']==1?'checked=checked':''; ?>>
            <input type="radio" id="bonusself_0" name="bonusself" value="0" <?php echo $output['setting']['dis_bonus_self']==0?'checked=checked':''; ?>></td>
          <td class="vatop tips"></td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">分销商名称</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<input id="name" name="name" value="<?php echo $output['setting']['dis_name'];?>" class="txt" type="text">
          </td>
          <td class="vatop tips">在前台显示的名称</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">佣金名称</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input id="bonusname" name="bonusname" value="<?php echo $output['setting']['dis_bonus_name'];?>" class="txt" type="text">
          </td>
          <td class="vatop tips">在前台显示的名称</td>
        </tr>
		
		<tr class="noborder">
          <td colspan="2" class="required">必须通过邀请人才能成为会员</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform onoff">
          	<label for="memberinviter_1" class="cb-enable <?php if($output['setting']['member_inviter'] == '1'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_open'];?>"><span><?php echo $lang['nc_open'];?></span></label>
            <label for="memberinviter_0" class="cb-disable <?php if($output['setting']['member_inviter'] == '0'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_close'];?>"><span><?php echo $lang['nc_close'];?></span></label>
            <input type="radio" id="memberinviter_1" name="memberinviter" value="1" <?php echo $output['setting']['member_inviter']==1?'checked=checked':''; ?>>
            <input type="radio" id="memberinviter_0" name="memberinviter" value="0" <?php echo $output['setting']['member_inviter']==0?'checked=checked':''; ?>></td>
          <td class="vatop tips">若关闭，则说明没有邀请人，用户成为不了会员，包括注册和微信自动授权</td>
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
