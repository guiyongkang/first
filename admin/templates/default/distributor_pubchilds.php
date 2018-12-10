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
			<h3>公排卡位列表</h3>
            <ul class="tab-base">
            <?php foreach($output['public_area'] as $areainfo){?>
            	<li><a href="index.php?act=<?php echo $_GET['act'];?>&op=publists&area_id=<?php echo $areainfo['item_id'];?>"<?php echo $areainfo['item_id'] == $output['area_id'] ? ' class="current"' : '';?>><span><?php echo $areainfo['item_name'];?></span></a></li>
            <?php }?>
            </ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form method="get" name="formSearch" id="formSearch">
		<input type="hidden" name="act" value="distributor_list">
		<input type="hidden" name="op" value="pubchilds">
        <input type="hidden" name="area_id" value="<?php echo $output['area_id'];?>">
        <input type="hidden" name="aid" value="<?php echo $_GET['aid']?>">
        
		<table class="tb-type1 noborder search">
			<tbody>
				<tr>
					<th><label>会员昵称</label></th>
					<td><input type="text" value="<?php echo isset($output['search']['membername']) ? $output['search']['membername'] : '';?>" name="membername" id="membername" class="txt"></td>
					<th><label>状态</label></th>
					<td>
                    	<select name="status">
                        	<option value="0">全部</option>
                            <option value="1"<?php echo isset($output['search']['status']) && $output['search']['status']==1 ? ' selected' : '';?>>已出局</option>
                            <option value="2"<?php echo isset($output['search']['status']) && $output['search']['status']==2 ? ' selected' : '';?>>正常</option>
                        </select>
                    </td>
                    <th><label>下级级别</label></th>
					<td>
                    	<select name="level">
                        	<option value="0">全部</option>
                            <?php if($output['my_child_level']>0){?>
                        	<?php for($i=1; $i<=$output['my_child_level']; $i++){?>
                            	<option value="<?php echo $i;?>"<?php echo isset($output['search']['level']) && $output['search']['level']==$i ? ' selected' : '';?>><?php echo $i;?>级</option>
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
					<th class="w84 border-right">卡位位置</th>
					<th colspan="2" class="w200 align-center border-right">会员信息</th>
                    <th colspan="2" class="w200 align-center border-right">上级信息</th>
                    <th class="align-center border-right">见点奖</th>
                    <th class="align-center border-right">其他奖项</th>
                    <th class="w84 align-center border-right">状态</th>
                    <th class="w120 align-center border-right">加入时间</th>
					<th class="w108 align-center"><?php echo $lang['nc_handle'];?> </th>
				</tr>
			</thead>
			<tbody>
				<?php if (!empty($output['pub_lists']) && is_array($output['pub_lists'])) { ?>
				<?php foreach ($output['pub_lists'] as $key => $value) {?>
				<tr class="hover edit">
					<td class="border-right">第 <?php echo $value['distributor_y'];?> 级<br />第 <?php echo $value['distributor_x'];?> 个</td>					
                    <td class="w48 picture"><div class="size-44x44"><span class="thumb size-44x44"><i></i><img src="<?php echo $value['member_avatar'];?>" width="44" /></span></div></td>
					<td class="border-right"><?php echo $value['member_name'];?></td>
                    <td class="w48 picture"><div class="size-44x44"><?php if($value['parentid']>0){?><span class="thumb size-44x44"><i></i><img src="<?php echo $value['parent_avatar'];?>" width="44" /></span><?php }?></div></td>
					<td class="border-right"><?php if($value['parentid']==0){?>顶级<?php }else{?><?php echo $value['parent_name'];?><?php }?></td>
                    <td class="align-center border-right">
                    	<?php if(!empty($value['prize_level'])){?>
                        <?php foreach($value['prize_level'] as $k_0=>$v_0){?>
                        <?php echo $k_0==1 ? '' : '<br />';?>
                        <?php echo $k_0;?> 级&nbsp;&nbsp;<?php echo $v_0['member_name'];?>&nbsp;&nbsp;<font style="color:#ff0000; font-size:14px;">&yen;<?php echo $v_0['money'];?></font>
                        <?php }?>
                        <?php }?>
                    </td>
                    <td class="align-center border-right">
                    	<?php if(!empty($value['prize_other'])){?>
                        <?php $j=0;foreach($value['prize_other'] as $k_1=>$v_1){$j++;?>
                        <?php echo $j==1 ? '' : '<br />';?>
                        <font style="color:#C81522"><?php echo $lang['public_prize_title'][$k_1];?></font>&nbsp;&nbsp;<?php echo $v_1['member_name'];?>&nbsp;&nbsp;<font style="color:#ff0000;">&yen;<?php echo $v_1['money'];?></font>
                        <?php }?>
                        <?php }?>
                    </td>
                    <td class="align-center border-right">
                    	<?php echo $value['status']==0 ? '<font style="padding:8px 15px; color:#FFF; background:#D9534F; border-radius:5px;">已出局</font>' : '<font style="padding:8px 15px; color:#FFF; background:#5CB85C; border-radius:5px;">正常</font>'?>
                    </td>
                    <td class="align-center border-right"><?php echo date('Y-m-d H:i:s',$value['addtime']);?></td>
					<td class="align-center"><a href="index.php?act=distributor_list&op=pubchilds&area_id=<?php echo $output['area_id'];?>&aid=<?php echo $value['ralate_id'];?>">下级会员</a></td>
				</tr>
				<tr style="display:none;">
					<td colspan="20"><div class="ncsc-goods-sku ps-container"></div></td>
				</tr>
				<?php } ?>
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