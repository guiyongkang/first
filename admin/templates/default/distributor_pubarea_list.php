<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>公排分区</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=distributor_list&op=pubareasadd" ><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="w48 align-center">ID</th>
          <th class="align-center">分区名称</th>
          <th class="w200 align-center">是否可提现</th>
          <th class="w120 align-center">操作</th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['area_list']) && is_array($output['area_list'])){ ?>
        <?php foreach($output['area_list'] as $key => $value){ ?>
        <tr class="hover edit row">
          <td class="align-center"><?php echo $value['item_id'];?></td>
          <td class="align-center"><?php echo trim($value['item_name']);?></td>
          <td class="align-center">
          	<?php if($value['is_withdraw']==1){?>可提现<?php }else{?>禁止提现<?php }?>
          </td>
          <td class="align-center">
          	<a href="index.php?act=distributor_list&op=pubareasedit&tid=<?php echo $value['item_id'];?>"><?php echo $lang['nc_edit'];?></a>
            <?php if($value['item_default']==0){?>
            &nbsp;|&nbsp;
          	<a href="javascript:void(0)" onclick="if(confirm('<?php echo $lang['nc_ensure_del'];?>')){location.href='index.php?act=distributor_list&op=pubareasdel&tid=<?php echo $value['item_id'];?>';}"><?php echo $lang['nc_del'];?></a>
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
        <?php if(!empty($output['area_list']) && is_array($output['area_list'])){ ?>
        <tr class="tfoot">
          <td>&nbsp;</td>
          <td colspan="16">
            <div class="pagination"> <?php echo $output['show_page'];?> </div></td>
        </tr>
        <?php } ?>
      </tfoot>
    </table>
</div>
