<style type="text/css">
.dislevelcss{float:left;margin:5px 0px 0px 8px;text-align:center;border:solid 1px #858585;padding:5px;}
.dislevelcss th{border-bottom:dashed 1px #858585;font-size:16px;}
</style>

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
  <input type="hidden" name="itemid" value="<?php echo $output['commission_setting']['item_id'];?>" />
    <table class="table tb-type2">
      <tbody>
      	<tr class="noborder">
          <td colspan="2" class="required" style="background:#f1f1f1">分销佣金设置</td>
        </tr>
      	<tr class="noborder">
          <td colspan="2" class="required">分销商品发放佣金占产品利润百分比</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input type="text" id="profit" name="profit" value="<?php echo $output['commission_setting']['profit'];?>" class="text" style="width:60px; text-align:center" /> %
          </td>
          <td class="vatop tips">设置此比例，防止佣金发放溢出；产品利润=网站售价-成本价</td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required">分销商品获得佣金明细</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
          	<div class="red" style="cursor:pointer; padding:10px;" id="allchange">（全部统一）</div>
            <?php
		  	$jsondisidarr = json_encode($output['level_ids'],JSON_UNESCAPED_UNICODE);
		  	$dislevelcont = count($output['level_ids']);	
			foreach($output['level_list'] as $key=>$disinfo){
			?>
			<div class="dislevelcss">
            	<table id="11" class="item_data_table" border="0" cellpadding="3" cellspacing="0">
				<tr><th><?php echo $disinfo['level_name']?></th></tr>
               		<?php
						$level =  $output['setting']['dis_bonus_self'] ? $output['setting']['dis_bonus_level']+1:$output['setting']['dis_bonus_level'];						
						for($i=0;$i<$level;$i++){?>                        
						<tr>
							<td>
              <?php if($output['setting']['dis_bonus_self']==1 && $i==$output['setting']['dis_bonus_level']){?>
              自销
              <?php }else{?>                            
							<?php echo $i+1;?>级
              <?php }?>&nbsp;&nbsp; %
								<input id="dischange<?=$disinfo['level_id'].$i?>" name="discommission[<?=$disinfo['level_id']?>][<?php echo $i;?>]" value="<?php echo !empty($output['commission_setting']['dis_commission'][$disinfo['level_id']][$i]) ? $output['commission_setting']['dis_commission'][$disinfo['level_id']][$i] : 0; ?>" class="form_input" size="5" maxlength="10" type="text">
								(分销商品发放佣金比例的百分比)
							</td>
						</tr>
					<?php }?>
                </table>
				</div>
			<?php } ?>
            <div style="clear:both"></div>
          </td>
        </tr>
		<?php if(!empty($output['team_list']) && is_array($output['team_list'])){?>
        <tr class="noborder">
          <td colspan="2" class="required">股东分红奖励规则</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
          	<table style="width:300px; margin-top:10px; border:1px #dfdfdf solid">
            <?php
			foreach($output['team_list'] as $k_t=>$team){
			?>
				<tr>
                	<td style="width:60px; border-right:1px #dfdfdf solid; text-align:right; padding-right:20px;"><?php echo $team['team_name'];?></td>
                    <td><input type="text" name="teamcommission[<?php echo $team['team_id'];?>]" value="<?php echo empty($output['commission_setting']['team_commission'][$team['team_id']]) ? '' : $output['commission_setting']['team_commission'][$team['team_id']];?>" class="text" style="width:60px; text-align:center; margin-left:10px" /> %</td>
                </tr>
			<?php } ?>
            </table>
            <div style="font-size:12px; color:#999; padding:5px 0px">注：占产品利润的百分比</div>
          </td>
        </tr>
        <?php }?>
        <tr class="noborder">
          <td colspan="2" class="required" style="background:#f1f1f1">公排奖励设置</td>
        </tr>
        <?php foreach($output['public_area'] as $area_id=>$area_name){?>
        <tr class="noborder">
          <td colspan="2" class="required" style="background:#f1f1f1"><?php echo $area_name;?></td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">见点奖</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<table style="width:300px; margin-top:10px; border:1px #dfdfdf solid">
                <?php for($i=0; $i<$output['setting']['public_bonus_level']; $i++){?>
                <tr>
                	<td style="width:60px; border-right:1px #dfdfdf solid; text-align:right; padding-right:20px;"><?php echo $i+1;?>级</td>
                    <td><input type="text" name="publiccommission[<?php echo $area_id;?>][jiandian][<?php echo $i+1;?>]" value="<?php echo empty($output['commission_setting']['public_commission'][$area_id]['jiandian'][$i+1]) ? '' : $output['commission_setting']['public_commission'][$area_id]['jiandian'][$i+1];?>" class="text" style="width:60px; text-align:center; margin-left:10px" /> 积分</td>
                </tr>
                <?php }?>
            </table>
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required">直推奖</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
            <input type="text" name="publiccommission[<?php echo $area_id;?>][inviter]" value="<?php echo empty($output['commission_setting']['public_commission'][$area_id]['inviter']) ? 0 : $output['commission_setting']['public_commission'][$area_id]['inviter'];?>" class="text" style="width:60px; text-align:center" /> 积分
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required">懒人奖</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
            <input type="text" name="publiccommission[<?php echo $area_id;?>][parent]" value="<?php echo empty($output['commission_setting']['public_commission'][$area_id]['parent']) ? 0 : $output['commission_setting']['public_commission'][$area_id]['parent'];?>" class="text" style="width:60px; text-align:center" /> 积分
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required">感恩奖</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
            <input type="text" name="publiccommission[<?php echo $area_id;?>][thankful]" value="<?php echo empty($output['commission_setting']['public_commission'][$area_id]['thankful']) ? 0 : $output['commission_setting']['public_commission'][$area_id]['thankful'];?>" class="text" style="width:60px; text-align:center" /> 积分
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        <?php }?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2"><a id="submit" href="javascript:void(0)" class="btn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>

<script type="text/javascript">
var level = <?=$level?>;
var dislevelcont = <?=$dislevelcont?>;
var disidarr = <?=$jsondisidarr?>;
var fistarr = new Array();

$(document).ready(function(){
    $("#submit").click(function(){
        $("#add_form").submit();
    });

	$("#allchange").click(function(){
		for(i=0;i<dislevelcont;i++){
			if(i == 0){
				for(j=0;j<level;j++){
				fistarr[j] = $("#dischange"+disidarr[i]+j).val();
				}	
			}else{
				for(j=0;j<level;j++){
				$("#dischange"+disidarr[i]+j).val(fistarr[j]);
				}
			}
		}	
	})
});
</script>
