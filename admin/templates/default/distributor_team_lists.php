<?php defined('SAFE_CONST') or exit('Access Invalid!');?>
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
  
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="w48 align-center">ID</th>
          <th class="align-center">名称</th>
          <th class="w120 align-center">直推人数</th>
          <th class="w120 align-center">操作</th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['team_list']) && is_array($output['team_list'])){ ?>
        <?php foreach($output['team_list'] as $key => $value){ ?>
        <tr class="hover edit row">
          <td class="align-center"><?php echo $value['team_id'];?></td>
          <td class="align-center"><?php echo $value['team_name'];?></td>
          <td class="align-center"><?php echo $value['team_invitenum'];?></td>
          <td class="align-center">
          	<a href="index.php?act=team&op=editteam&tid=<?php echo $value['team_id'];?>"><?php echo $lang['nc_edit'];?></a>
            &nbsp;|&nbsp;
          	<a href="javascript:void(0)" onclick="if(confirm('<?php echo $lang['nc_ensure_del'];?>')){location.href='index.php?act=team&op=delteam&tid=<?php echo $value['team_id'];?>';}"><?php echo $lang['nc_del'];?></a>
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
        <tr class="tfoot">
          <td colspan="16">
          <a href="index.php?act=team&op=addteam" class="btn"><span>添加级别</span></a>
          <?php if(!empty($output['team_list']) && is_array($output['team_list'])){ ?>
          <div class="pagination"> <?php echo $output['show_page'];?> </div>
          <?php } ?>
          </td>
        </tr>
        
    </table>
</div>
