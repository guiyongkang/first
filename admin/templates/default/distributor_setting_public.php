<style type="text/css">
.products_option .search_div{margin-top:8px;}
.products_option .search_div .button_search{padding:3px 6px; cursor:pointer}
.products_option .select_items{margin-top:8px;}
.products_option .select_items .button_add{height:30px; line-height:26px; width:45px; display:block; margin:30px 8px 0px; float:left}
.products_option .select_items .products_show{height:100px; width:300px; display:block; border:1px #dfdfdf solid; overflow:scroll; background:#FFF}
.products_option .select_items .products_show p{height:24px; line-height:24px; width:95%; overflow:hidden; padding:0px; margin:0px auto; cursor:pointer}
.products_option .select_items .products_show .p_cur{background:#39F;}
.products_option .options_buttons{height:100px; width:80px; float:left; margin-left:8px}
.products_option .options_buttons button{display:block; height:30px; line-height:26px; width:100%; text-align:center; cursor:pointer; margin:8px 0px 0px 0px}
.search_cate select{width:120px}
</style>
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
          <td colspan="2" class="required">公排开关</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform onoff">
          	<label for="open_1" class="cb-enable <?php if($output['setting']['public_open'] == '1'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_open'];?>"><span><?php echo $lang['nc_open'];?></span></label>
            <label for="open_0" class="cb-disable <?php if($output['setting']['public_open'] == '0'){ ?>selected<?php } ?>" title="<?php echo $lang['nc_close'];?>"><span><?php echo $lang['nc_close'];?></span></label>
            <input type="radio" id="open_1" name="open" value="1" <?php echo $output['setting']['public_open']==1?'checked=checked':''; ?>>
            <input type="radio" id="open_0" name="open" value="0" <?php echo $output['setting']['public_open']==0?'checked=checked':''; ?>></td>
          <td class="vatop tips">若关闭，则说明公排一切功能失效</td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required">公排时获得见点奖级数</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<select name="bonuslevel" style="width:80px;">
            	<?php for($i=1;$i<=15;$i++){?>
                <option value="<?php echo $i;?>"<?php echo $output['setting']['public_bonus_level'] == $i ? ' selected' : '';?>><?php echo $i;?>级</option>
                <?php }?>
            </select>
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required">排位递增形式</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<select name="times" style="width:150px;"<?php echo $output['setting']['public_status']==1 ? ' disabled="disabled"' : ''?>>
            	<?php foreach($lang['distributor_times'] as $k_t=>$v_t){?>
          		<option value="<?php echo $k_t;?>"<?php echo $output['setting']['public_times']==$k_t ? ' selected' : '';?>><?php echo $v_t;?></option>
          		<?php }?>
            </select>
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required">多点卡位</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
            <input type="radio" id="multi_1" name="multi" value="1" <?php echo $output['setting']['public_multi']==1?'checked=checked':''; ?>> 开启&nbsp;&nbsp;
            <input type="radio" id="multi_0" name="multi" value="0" <?php echo $output['setting']['public_multi']==0?'checked=checked':''; ?>> 关闭&nbsp;&nbsp;</td>
          <td class="vatop tips">若开启，则说明分销商可以多次参与排位</td>
        </tr>
        
        <tr class="noborder multi_0"<?php echo $output['setting']['public_multi'] == '1' ? ' style="display:none"' : '';?>>
          <td colspan="2" class="required">出局后重新排位门槛</td>
        </tr>
        <tr class="noborder multi_0"<?php echo $output['setting']['public_multi'] == '1' ? ' style="display:none"' : '';?>>
          <td class="vatop rowform" colspan="2">
          <select name="returntype" style="width:150px;">
          <?php foreach($lang['distributor_cometype'] as $k_t=>$v_t){if($k_t<5 && $k_t != 2){?>
          	<option value="<?php echo $k_t;?>"<?php echo $output['setting']['public_return_type']==$k_t ? ' selected' : '';?>><?php echo $v_t;?></option>
          <?php }}?>
          	<option value="6"<?php echo $output['setting']['public_return_type']==6 ? ' selected' : '';?>>不支持重新排位</option>
          </select>
          <div id="returntype_1" style="padding:12px 0px;<?php echo $output['setting']['public_return_type'] != 1 ? ' display:none' : '';?>">
          	一次性消费<input name="returnvalue[1]" value="<?php echo $output['setting']['return_value'][1];?>" class="txt" type="text" style="width:60px; text-align:center; margin-left:10px">积分即可参与排位
          </div>
          <div id="returntype_2" style="padding:12px 0px;<?php echo $output['setting']['public_return_type'] != 2 ? ' display:none' : '';?>">
          	总消费额达到<input name="returnvalue[2]" value="<?php echo $output['setting']['return_value'][2];?>" class="txt" type="text" style="width:60px; text-align:center; margin-left:10px">积分即可参与排位
          </div>
          <div id="returntype_3" style="padding:12px 0px;<?php echo $output['setting']['public_return_type'] != 3 ? ' display:none' : '';?>">
          	<div class="products_option">
            	<div class="search_div">
                 <span class="search_cate" id="return_search_cate">
                 	<select class="class-select">
						<option value="0"><?php echo $lang['nc_please_choose'];?>...</option>
						<?php if(!empty($output['gc_list'])){ ?>
						<?php foreach($output['gc_list'] as $k => $v){ ?>
						<?php if ($v['gc_parent_id'] == 0) {?>
						<option value="<?php echo $v['gc_id'];?>"><?php echo $v['gc_name'];?></option>
						<?php } ?>
						<?php } ?>
						<?php } ?>
					</select>
                 </span>
                 <input type="text" placeholder="关键字" value="" class="form_input" size="35" maxlength="30" />
                 <input type="hidden" class="cate_id" value="0"/>
                 <button type="button" class="button_search">搜索</button>
               </div>
               
               <div class="select_items">
               	 <select size='10' class="select_product0" style="width:300px; height:100px; display:block; float:left">
                 </select>
                 <button type="button" class="button_add">=></button>
                 <select size='10' class="select_product1" multiple style="width:300px; height:100px; display:block; float:left">
                 	<?php if(!empty($output['goods_list_return'])){?>
                    <?php foreach($output['goods_list_return'] as $v_r){?>
                    <option value="<?php echo $v_r['goods_commonid'];?>"><?php echo $v_r['goods_name'];?></option>
                    <?php }?>
                    <?php }?>
                 </select>
                 <input type="hidden" name="returnvalue[3]" value="<?php echo empty($output['setting']['return_value'][3]) ? '' : ','.$output['setting']['return_value'][3].',';?>" />
               </div>
               
               <div class="options_buttons">
               		<button type="button" class="button_remove">移除</button>
                    <button type="button" class="button_empty">清空</button>
               </div>
            </div>
          </div>
          </td>
        </tr>
        
        <tr class="noborder multi_1"<?php echo $output['setting']['public_multi'] == '0' ? ' style="display:none"' : '';?>>
          <td colspan="2" class="required">参与公排门槛</td>
        </tr>
        <tr class="noborder multi_1"<?php echo $output['setting']['public_multi'] == '0' ? ' style="display:none"' : '';?>>
          <td class="vatop rowform" colspan="2">
          <select name="cometype" style="width:150px;">
          <?php foreach($lang['distributor_cometype'] as $k_t=>$v_t){if($k_t<5 && $k_t!=2){?>
          	<option value="<?php echo $k_t;?>"<?php echo $output['setting']['public_come_type']==$k_t ? ' selected' : '';?>><?php echo $v_t;?></option>
          <?php }}?>
          </select>
          <div id="cometype_1" style="padding:12px 0px;<?php echo $output['setting']['public_come_type'] != 1 ? ' display:none' : '';?>">
          	一次性消费<input name="comevalue[1]" value="<?php echo $output['setting']['come_value'][1];?>" class="txt" type="text" style="width:60px; text-align:center; margin-left:10px">积分即可参与排位
          </div>
          <div id="cometype_2" style="padding:12px 0px;<?php echo $output['setting']['public_come_type'] != 2 ? ' display:none' : '';?>">
          	总消费额<input name="comevalue[2]" value="<?php echo $output['setting']['come_value'][2];?>" class="txt" type="text" style="width:60px; text-align:center; margin-left:10px">积分即可参与排位
          </div>
          <div id="cometype_3" style="padding:12px 0px;<?php echo $output['setting']['public_come_type'] != 3 ? ' display:none' : '';?>">
          	<div class="products_option">
            	<div class="search_div">
                 <span class="search_cate" id="come_search_cate">
                 	<select class="class-select">
						<option value="0"><?php echo $lang['nc_please_choose'];?>...</option>
						<?php if(!empty($output['gc_list'])){ ?>
						<?php foreach($output['gc_list'] as $k => $v){ ?>
						<?php if ($v['gc_parent_id'] == 0) {?>
						<option value="<?php echo $v['gc_id'];?>"><?php echo $v['gc_name'];?></option>
						<?php } ?>
						<?php } ?>
						<?php } ?>
					</select>
                 </span>
                 <input type="text" placeholder="关键字" value="" class="form_input" size="35" maxlength="30" />
                 <input type="hidden" class="cate_id" value="0"/>
                 <button type="button" class="button_search">搜索</button>
               </div>
               
               <div class="select_items">
               	 <select size='10' class="select_product0" style="width:300px; height:100px; display:block; float:left">
                 </select>
                 <button type="button" class="button_add">=></button>
                 <select size='10' class="select_product1" multiple style="width:300px; height:100px; display:block; float:left">
                 	<?php if(!empty($output['goods_list_come'])){?>
                    <?php foreach($output['goods_list_come'] as $v_r){?>
                    <option value="<?php echo $v_r['goods_commonid'];?>"><?php echo $v_r['goods_name'];?></option>
                    <?php }?>
                    <?php }?>
                 </select>
                 <input type="hidden" name="comevalue[3]" value="<?php echo empty($output['setting']['come_value'][3]) ? '' : ','.$output['setting']['come_value'][3].',';?>" />
               </div>
               
               <div class="options_buttons">
               		<button type="button" class="button_remove">移除</button>
                    <button type="button" class="button_empty">清空</button>
               </div>
            </div>
          </div>
          </td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required">出局层级</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          	<select name="outlevel" style="width:80px;">
            	<?php for($i=1;$i<=15;$i++){?>
                <option value="<?php echo $i;?>"<?php echo $output['setting']['public_out_level'] == $i ? ' selected' : '';?>><?php echo $i;?>级</option>
                <?php }?>
            </select>
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
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript">
$(function(){
	$('input[name=multi]').click(function(){
		var id = $(this).attr('value');
		for(var i=0; i<2; i++){
			if(i==id){
				$('.multi_'+id).show();
			}else{
				$('.multi_'+i).hide();
			}
		}
	});
	
	$('select[name=returntype]').change(function(){
		var id = $(this).val();
		for(var i=1; i<=3; i++){
			if(i==id){
				$('#returntype_'+id).show();
			}else{
				$('#returntype_'+i).hide();
			}
		}
	});
	
	$('select[name=cometype]').change(function(){
		var id = $(this).val();
		for(var i=1; i<=3; i++){
			if(i==id){
				$('#cometype_'+id).show();
			}else{
				$('#cometype_'+i).hide();
			}
		}
	});
	
	$(".products_option .search_div .button_search").click(function(){
		var object = $(this).parent();
		var catid = object.children("input.cate_id").val()
		var keyword = object.children("input").val();
			
		var param = {cate_id:catid,keyword:keyword,branch:'get_goodlist'};
		$.getJSON('index.php?act=distributor&op=ajax', param, function(data){
			object.parent().children(".select_items").children(".select_product0").html(data.html);
		});
	});
		
	$(".products_option .select_items .button_add").click(function(){
		var text = $(this).parent().children(".select_product0").find("option:selected").text();
		var value = $(this).parent().children(".select_product0").find("option:selected").val();
		if($(this).parent().children(".select_product1").find("option:contains("+text+")").length == 0 && typeof(value)!='undefined'){
			$(this).parent().children(".select_product1").append("<option value='"+value+"'>"+text+"</option>");
		}
			
		var strids = $(this).parent().children("input").val();
		if(typeof(value)!='undefined'){
			if(strids == ''){
				$(this).parent().children("input").val(','+value+',');
			}else{
				strids = strids.replace(','+value+',',",");
				$(this).parent().children("input").val(strids+value+',');
			}
		}
	});
		
	$(".products_option .options_buttons .button_remove").click(function(){//移除选项		
		var select_obj = $(this).parent().parent().children(".select_items").children(".select_product1").find("option:selected");
		var input_obj = $(this).parent().parent().children(".select_items").children("input");
		var strids = input_obj.val();
		select_obj.each(function(){
			$(this).remove();
			strids = strids.replace(','+$(this).val()+',',",");
		});
		if(strids==','){
			strids = '';
		}
		input_obj.val(strids);
	});
		
	$(".products_option .options_buttons .button_empty").click(function(){//清空选项
		 $(this).parent().parent().children(".select_items").children(".select_product1").empty();
		 $(this).parent().parent().children(".select_items").children("input").val('');
	});
	
	gcategoryInitDiv('come_search_cate');
	gcategoryInitDiv('return_search_cate');
});

/* 商品分类选择函数 */
function gcategoryInitDiv(divId)
{
    $("#" + divId + " > select").get(0).onchange = gcategoryChangeDiv; // select的onchange事件
    window.onerror = function(){return true;}; //屏蔽jquery报错
}

function gcategoryChangeDiv()
{
    // 删除后面的select
    $(this).nextAll("select").remove();

    // 计算当前选中到id和拼起来的name
    var selects = $(this).siblings("select").andSelf();
    var id = 0;
    var names = new Array();
    for (i = 0; i < selects.length; i++)
    {
        sel = selects[i];
        if (sel.value > 0)
        {
            id = sel.value;
            name = sel.options[sel.selectedIndex].text;
            names.push(name);
        }
    }

    // ajax请求下级分类
    if (this.value > 0)
    {
        var _self = this;
		$(this).parent().parent().children('input.cate_id').attr('value',this.value);
		
        var url = SITEURL + '/index.php?act=index&op=josn_class&callback=?';
        $.getJSON(url, {'gc_id':this.value}, function(data){
            if (data)
            {
                if (data.length > 0)
                {
                    $("<select class='class-select'><option>-请选择-</option></select>").change(gcategoryChangeDiv).insertAfter(_self);
                    var data  = data;
                    for (i = 0; i < data.length; i++)
                    {
                        $(_self).next("select").append("<option data-explain='" + data[i].commis_rate + "' value='" + data[i].gc_id + "'>" + data[i].gc_name + "</option>");
                    }
                }
            }
        });
    }
}
</script>
