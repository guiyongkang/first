<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_distributor_level'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=distributor&op=addlevel" ><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="w48 align-center">ID</th>
          <th class="w200 align-center">级别名称</th>
          <th class="w120 align-center">级别图标</th>
          <th class="w200 align-center">门槛类型</th>
          <th class="align-center">门槛条件</th>
          <th class="w120 align-center">操作</th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['level_list']) && is_array($output['level_list'])){ ?>
        <?php foreach($output['level_list'] as $key => $value){ ?>
        <tr class="hover edit row">
          <td class="align-center"><?php echo $value['level_id'];?></td>
          <td class="align-center"><?php echo trim($value['level_name']);?></td>
          <td class="align-center"><?php if(!empty($value['level_thumb'])){?><img src="<?php echo UPLOAD_SITE_URL .trim($value['level_thumb']);?>" width="75" /><?php }?></td>
          <td class="align-center">
          <?php if($value['level_come_type']==6){?>
          无门槛
          <?php }elseif($value['level_come_type']==1){?>
          一次性消费
          <?php }elseif($value['level_come_type']==2){?>
          总消费额
          <?php }elseif($value['level_come_type']==3){?>
          购买指定商品
          <?php }elseif($value['level_come_type']==4){?>
          购买任意商品
          <?php }elseif($value['level_come_type']==5){?>
          直接购买
          <?php }?>
          </td>
          <td class="align-center">
          <?php if($value['level_come_type']==6){?>
          
          <?php }elseif($value['level_come_type']==1){?>
          一次性消费<font style="color:red; padding:0px 8px"><?php echo $value['level_come_value'];?></font>积分
          <?php }elseif($value['level_come_type']==2){?>
          总消费额<font style="color:red; padding:0px 8px"><?php echo $value['level_come_value'];?></font>积分
          <?php }elseif($value['level_come_type']==3){?>
          <?php if(!empty($output['goods_list']) && !empty($value['level_come_value'])){$arr = explode(',',$value['level_come_value']);?>
          <div style="line-height:24px;">
          购买以下任一商品
          <?php foreach($arr as $a){?>
          <?php if(!empty($output['goods_list'][$a])){?>
          <br /><?php echo $output['goods_list'][$a];?>
          <?php }?>
          <?php }?>
          </div>
          <?php }?>
          <?php }elseif($value['level_come_type']==4){?>
          
          <?php }elseif($value['level_come_type']==5){?>
          <font style="color:red;"><?php echo $value['level_come_value'];?></font>积分
          <?php }?>
          </td>
          <td class="align-center">
          	<a href="index.php?act=distributor&op=editlevel&lid=<?php echo $value['level_id'];?>"><?php echo $lang['nc_edit'];?></a>
            <?php if($value['level_default']==0){?>
            &nbsp;|&nbsp;
          	<a href="javascript:void(0)" onclick="if(confirm('<?php echo $lang['nc_ensure_del'];?>')){location.href='index.php?act=distributor&op=dellevel&lid=<?php echo $value['level_id'];?>';}"><?php echo $lang['nc_del'];?></a>
            <?php }?>
          </td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="10"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <?php if(!empty($output['level_list']) && is_array($output['level_list'])){ ?>
        <tr class="tfoot">
          <td>&nbsp;</td>
          <td colspan="16">
            <div class="pagination"> <?php echo $output['show_page'];?> </div></td>
        </tr>
        <?php } ?>
      </tfoot>
    </table>
</div>
