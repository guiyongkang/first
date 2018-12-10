<?php defined('SAFE_CONST') or exit('Access Invalid!');?>

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
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title"><h5><?php echo $lang['nc_prompts'];?></h5><span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td>
          <ul>
            <li>微信红包、微信转账、支付宝为默认提现方式，若不用，则设置为禁用；用户用微信红包、微信转账提现默认不经过后台审核；增加提现方式仅限银行卡提现。</li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <table class="table tb-type2">
    <thead>
      <tr class="thead">
        <th>提现方式</th>
        <th class="align-center">是否审核</th>
        <th class="align-center">启用</th>
        <th class="align-center"><?php echo $lang['nc_handle'];?></th>
      </tr>
    </thead>
    <tbody>
      <?php if(!empty($output['lists']) && is_array($output['lists'])){ foreach($output['lists'] as $k => $v){ ?>
      <tr class="hover">
        <td><?php echo $v['method_name'];?></td>
        <td class="w25pre align-center">
          <?php echo $v['method_check']==0 ? '<font style="padding:8px 15px; color:#FFF; background:#D9534F; border-radius:5px;">否</font>' : '<font style="padding:8px 15px; color:#FFF; background:#5CB85C; border-radius:5px;">是</font>'?>
        </td>
        <td class="w25pre align-center">
          <?php echo $v['method_status']==0 ? '<font style="padding:8px 15px; color:#FFF; background:#D9534F; border-radius:5px;">禁用</font>' : '<font style="padding:8px 15px; color:#FFF; background:#5CB85C; border-radius:5px;">启用</font>'?>
        </td>
        <td class="w156 align-center">
        	<a href="index.php?act=distributor_list&op=withdrawmethodedit&mid=<?php echo $v['method_id']; ?>"><?php echo $lang['nc_edit']?></a>
            <?php if($v['method_code']=='bank'){?>
            &nbsp;&nbsp;|&nbsp;&nbsp;<a href="index.php?act=distributor_list&op=withdrawmethoddel&mid=<?php echo $v['method_id']; ?>">删除</a>
            <?php }?>
        </td>
      </tr>
      <?php } } ?>
    </tbody>
    <tfoot>
      <tr class="tfoot">
        <td colspan="15"></td>
      </tr>
    </tfoot>
  </table>
</div>