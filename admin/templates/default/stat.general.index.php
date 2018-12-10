<?php defined('SAFE_CONST') or exit('Access Invalid!');?>
<link href="<?php echo APP_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo APP_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome-ie7.min.css">
<![endif]-->
<style>
.items{width:100%; height:150px; background:#60D295; color:#FFF; width:100%; margin:20px 0px; position:relative; cursor:pointer}
.items .items_0,.items .items_1{float:left; overflow:hidden}
.items .items_0{font-size:30px; font-family:"Times New Roman"; padding-top:40px; text-align:center; width:60%;}
.items .items_0 i{display:block; height:20px; line-height:20px; font-size:16px; font-style:normal}
.items .items_1{width:40%}
.items .items_1 li{line-height:30px; font-size:14px;}
.sales_amount ul,.order_amount ul,.commission_amount ul{margin-top:15px;}
.dis_amount ul,.public_amount ul{margin-top:40px;}
.hongbao_amount ul{margin-top:15px;}
.middle_line{position:absolute; width:1px; height:120px; background:#58c88d; top:15px; left:50%}
.items span{display:block; background:url('/admin/resource/weixin/circle.png') no-repeat; position:absolute; top:40px; left:20px; width:50px; height:50px; text-align:center; font-size:30px; line-height:50px;}
.items_current{background:#53ba84}
</style>
<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3><?php echo $lang['nc_statgeneral'];?></h3>
			<?php echo $output['top_link'];?> </div>
	</div>
	<div class="fixed-empty"></div>
    <table width="98%" style="margin:0px auto" cellpadding="0" cellspacing="0" align="center">
    	<tr>
        	<td width="30%">
            	<div class="items sales_amount">
                	<div class="middle_line"></div>
                    <span class="icon-strikethrough"></span>
                	<div class="items_0"><?php echo str_replace('.00','',$output['sales']['amount']);?><i>总营业额</i></div>
                    <div class="items_1">
                    	<ul>
                        	<li>未付款：<?php echo str_replace('.00','',$output['sales']['nopay']);?></li>
                            <li>已付款：<?php echo str_replace('.00','',$output['sales']['payed']);?></li>
							<li>已取消：<?php echo str_replace('.00','',$output['sales']['out']);?></li>
                            <li>退款：<?php echo str_replace('.00','',$output['sales']['return']);?></li>
                        </ul>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </td>
            <td width="5%"></td>
            <td width="30%">
            	<div class="items order_amount">
                	<div class="middle_line"></div>
                	<span class="icon-file"></span>
                	<div class="items_0"><?php echo $output['order']['amount'];?><i>总订单</i></div>
                    <div class="items_1">
                    	<ul>
                        	<li>未付款：<?php echo $output['order']['nopay'];?></li>
                            <li>已付款：<?php echo $output['order']['payed'];?></li>
							<li>已取消：<?php echo str_replace('.00','',$output['order']['out']);?></li>
                            <li>退款：<?php echo $output['order']['return'];?></li>
                        </ul>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </td>
            <td width="5%"></td>
            <td width="30%">
            	<div class="items dis_amount">
                	<div class="middle_line"></div>
                	<span class="icon-sitemap"></span>
                	<div class="items_0"><?php echo $output['dis']['amount'];?><i>分销商</i></div>
                    <div class="items_1">
                    	<ul>
                        	<li>今日加入：<?php echo $output['dis']['today'];?></li>
                            <li>本月加入：<?php echo $output['dis']['month'];?></li>
                        </ul>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </td>
        </tr>
        <tr>
        	<td width="30%">
            	<div class="items public_amount">
                	<div class="middle_line"></div>
                    <span class="icon-credit-card"></span>
                	<div class="items_0"><?php echo $output['public']['amount'];?><i>公排卡位</i></div>
                    <div class="items_1">
                    	<ul>
                        	<li>级数：<?php echo $output['public']['maxlevel'];?></li>
                            <li>参与会员：<?php echo $output['public']['member'];?></li>
                        </ul>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </td>
            <td width="5%"></td>
            <td width="30%">
            	<div class="items commission_amount">
                	<div class="middle_line"></div>
                	<span class="icon-skype"></span>
                	<div class="items_0"><?php echo str_replace('.00','',$output['commission']['amount']);?><i>佣金总额</i></div>
                    <div class="items_1">
                    	<ul>
                        	<li>未付款：<?php echo str_replace('.00','',$output['commission']['nopay']);?></li>
                            <li>已付款：<?php echo str_replace('.00','',$output['commission']['payed']);?></li>
                            <li>已到账：<?php echo str_replace('.00','',$output['commission']['complate']);?></li>
                        </ul>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </td>
            <td width="5%"></td>
            <td width="30%">
            	<div class="items hongbao_amount">
                	<div class="middle_line"></div>
                	<span class="icon-envelope"></span>
                	<div class="items_0"><?php echo str_replace('.00','',$output['hongbao']['amount']);?><i>公排红包</i></div>
                    <div class="items_1">
                    	<ul>
                        	<li>级别奖：<?php echo str_replace('.00','',$output['hongbao']['level']);?></li>
                            <li>推荐奖：<?php echo str_replace('.00','',$output['hongbao']['invite']);?></li>
                            <li>见点奖：<?php echo str_replace('.00','',$output['hongbao']['parent']);?></li>
                            <li>感恩奖：<?php echo str_replace('.00','',$output['hongbao']['thankful']);?></li>
                        </ul>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
$(function(){
	$('.items').mouseover(function(){
		$('.items').removeClass('items_current');
		$(this).addClass('items_current');
	});
	
	$('.items').mouseout(function(){
		$(this).removeClass('items_current');
	});
});
</script>