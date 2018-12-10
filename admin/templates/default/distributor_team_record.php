<?php defined('SAFE_CONST') or exit('Access Invalid!');?>
<link href="<?php echo APP_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo APP_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome-ie7.min.css">
<![endif]-->
<style type="text/css">
.border-right{border-right:1px #DFE4EA dotted}
</style>
<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3>股东分红</h3>
              <ul class="tab-base">
                <li><a href="index.php?act=team&op=lists"><span>股东级别管理</span></a></li>
                <li><a href="index.php?act=team&op=record" class="current"><span>分红明细</span></a></li>
              </ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form method="get" name="formSearch" id="formSearch">
		<input type="hidden" name="act" value="team">
		<input type="hidden" name="op" value="record">
        
		<table class="tb-type1 noborder search">
			<tbody>
				<tr>
					<th><label>获奖者昵称</label></th>
					<td><input type="text" value="<?php echo isset($output['search']['membername']) ? $output['search']['membername'] : '';?>" name="membername" id="membername" class="txt"></td>
                    <th><label>商品名称</label></th>
					<td><input type="text" value="<?php echo isset($output['search']['goodname']) ? $output['search']['goodname'] : '';?>" name="goodname" id="goodname" class="txt"></td>
					<th><label>股东类型</label></th>
					<td>
                    	<select name="teamid">
                        	<option value="0">全部</option>
                            <?php if(!empty($output['team_list']) && is_array($output['team_list'])){?>
                            <?php foreach($output['team_list'] as $teamid=>$teaminfo){?>
                            <option value="<?php echo $teamid;?>"<?php echo isset($output['search']['teamid']) && $output['search']['teamid']==$teamid ? ' selected' : '';?>><?php echo $teaminfo;?></option>
                            <?php }?>
                            <?php }?>
                        </select>
                    </td>
                    <td ><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
				</tr>
			</tbody>
		</table>
	</form>
		<table class="table tb-type2">
			<thead>
				<tr class="thead">
					<th class="w60 border-right">序号</th>
					<th colspan="2" class="align-center border-right">获奖者信息</th>
                    <th colspan="2" class="align-center border-right">商品信息</th>
                    <th class="w120 align-center border-right">股东级别</th>
                    <th class="w84 align-center border-right">获奖金额</th>
                    <th class="w120 align-center border-right">获奖时间</th>
					<th class="w108 align-center"><?php echo $lang['nc_handle'];?> </th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($output['record_lists']) && is_array($output['record_lists'])) { ?>
				<?php foreach ($output['record_lists'] as $key => $value) {?>
				<tr class="hover edit">
					<td class="border-right"><?php echo $value['detail_id'];?></td>					
                    <td class="w48 picture"><div class="size-44x44"><span class="thumb size-44x44"><i></i><img src="<?php echo $value['member_avatar'];?>" width="44" /></span></div></td>
					<td class="border-right"><?php echo $value['member_name'];?></td>
                    <td class="w48 picture"><div class="size-44x44"><?php if($value['goodimg']){?><span class="thumb size-44x44"><i></i><img src="<?php echo $value['goodimg'];?>" width="44" /></span><?php }?></div></td>
					<td class="border-right"><?php if(!$value['goodname']){?>暂无<?php }else{?><?php echo $value['goodname'];?><?php }?></td>
                    <td class="align-center border-right"><?php echo $value['team_name'];?></td>
                    <td class="align-center border-right" style="color:#F00">&yen;<?php echo $value['detail_bonus'];?></td>
                    <td class="align-center border-right"><?php echo date('Y-m-d H:i:s',$value['addtime']);?></td>
					<td class="align-center">
                    <?php if($value['order_type']==0){?>
                    <a href="index.php?act=order&op=show_order&order_id=<?php echo $value['order_id'];?>">相关订单</a>
                    <?php }else{?>
                    <a href="index.php?act=vr_order&op=show_order&order_id=<?php echo $value['order_id'];?>">相关订单</a>
                    <?php }?>
                    </td>
				</tr>
				<?php } ?>
                <tr>
					<td class="border-right">统计</td>
                    <td colspan="15" style="color:#F00">总金额：&yen;<?php echo $output['total_amount'];?></td>
				</tr>
				<?php } else { ?>
				<tr class="no_data">
					<td colspan="15"><?php echo $lang['nc_no_record'];?></td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr class="tfoot">
                	<td>&nbsp;</td>
					<td colspan="16"><div class="pagination"> <?php echo $output['show_page'];?> </div></td>
				</tr>
			</tfoot>
		</table>
</div>

<script type="text/javascript">
$(function(){
    $('#ncsubmit').click(function(){
        $('#formSearch').submit();
    });
});
</script> 