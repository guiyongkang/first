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
		if($('#name').val()==""){
			alert('请填写级别名称');
			$('#name').focus();
			return false;
		}
        $("#add_form").submit();
    });
});
</script>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_distributor_level'];?></h3>
      <ul class="tab-base"><li><a href="javascript:void(0);" class="current"><span>新增</span></a></li></ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
  <input type="hidden" name="cometype" value="<?php echo $output['setting']['dis_come_type'];?>" />
    <table class="table tb-type2">
      <tbody>
      	<tr class="noborder">
          <td colspan="2" class="required">级别名称</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <input id="name" name="name" value="" class="txt" type="text">
          </td>
          <td class="vatop tips">&nbsp;</td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required">级别图标</td>
        </tr>
        <tr class="noborder">
			<td class="vatop rowform"><span class="type-file-show"><img class="show_image" src="<?php echo APP_TEMPLATES_URL;?>/images/preview.png">
			<div class="type-file-preview"></div>
			</span><span class="type-file-box">
			<input type='text' name='thumb' id='thumb' class='type-file-text' />
			<input type='button' name='button' id='button1' value='' class='type-file-button' />
			<input name="_pic" type="file" class="type-file-file" id="_pic" size="30" hidefocus="true">
			</span></td>
			<td class="vatop tips"><span class="vatop rowform">最佳显示尺寸为200*200像素</span></td>
		</tr>
        <tr class="noborder">
          <td colspan="2">成为该级别分销商条件</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
          	<?php if($output['setting']['dis_come_type']==1){?>
            <div style="padding:12px 0px;">
          		一次性消费<input name="comevalue" value="" class="txt" type="text" style="width:60px; text-align:center; margin-left:10px">积分即可成为该级别的分销商
          	</div>
            <?php }elseif($output['setting']['dis_come_type']==2){?>
            <div style="padding:12px 0px;">
          		总消费额达到<input name="comevalue" value="" class="txt" type="text" style="width:60px; text-align:center; margin-left:10px">积分即可成为该级别的分销商
          	</div>
            <?php }elseif($output['setting']['dis_come_type']==3){?>
            <div>
          		购买以下的任一商品即可成为该级别的分销商
          	</div>
            <div style="padding:12px 0px;">
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
                        
                     </select>
                     <input type="hidden" name="comevalue" value="" />
                   </div>
                   
                   <div class="options_buttons">
                        <button type="button" class="button_remove">移除</button>
                        <button type="button" class="button_empty">清空</button>
                   </div>
                </div>
            </div>
            <?php }elseif($output['setting']['dis_come_type']==5){?>
            <div style="padding:12px 0px;">
          		价格：<input name="comevalue" value="" class="txt" type="text" style="width:60px; text-align:center; margin-left:10px">
          	</div>
            <?php }?>
          </td>
        </tr>
        <?php if($output['setting']['dis_come_type']==5){?>
        <tr class="noborder">
          <td colspan="2">购买分销级别获得佣金明细</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          </td>
        </tr>
        <?php }?>
        <tr class="noborder">
          <td colspan="2">分销商升级至该级别条件</td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
          <select name="updatetype" style="width:150px;">
            <option value="3">购买指定商品</option>
          </select>
          <div id="updatetype_3" style="padding:12px 0px;">
          	<div class="products_option">
            	<div class="search_div">
                 <span class="search_cate" id="update_search_cate">
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
                 	
                 </select>
                 <input type="hidden" name="updatevalue" value="" />
               </div>
               
               <div class="options_buttons">
               		<button type="button" class="button_remove">移除</button>
                    <button type="button" class="button_empty">清空</button>
               </div>
            </div>
          </div>
          </td>
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
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript">
$(function(){
	$('input[class="type-file-file"]').change(uploadChange);
	
	function uploadChange(){
		var filepatd=$(this).val();
		var extStart=filepatd.lastIndexOf(".");
		var ext=filepatd.substring(extStart,filepatd.lengtd).toUpperCase();		
		if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
			alert("file type error");
			$(this).attr('value','');
			return false;
		}
		if ($(this).val() == ''){
			return false;
		}
		ajaxFileUpload();
	}
	
	function ajaxFileUpload(){
		$.ajaxFileUpload
		(
			{
				url:'index.php?act=common&op=pic_upload&form_submit=ok&uploadpath=distributor',
				secureuri:false,
				fileElementId:'_pic',
				dataType: 'json',
				success: function (data, status)
				{
					if (data.status == 1){
						$('.type-file-preview').html('<img src="'+data.url+'" />');
						$('#thumb').val(data.url);
					}else{
						alert(data.msg);
					}
					$('input[class="type-file-file"]').bind('change',uploadChange);
				},
				error: function (data, status, e)
				{
					alert('上传失败');$('input[class="type-file-file"]').bind('change',uploadChange);
				}
			}
		)
	};
	
	$('select[name=updatetype]').change(function(){
		var id = $(this).val();
		if(id==3){
			$('#updatetype_5').hide();
			$('#updatetype_3').show();
		}else{
			$('#updatetype_3').hide();
			$('#updatetype_5').show();
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
	gcategoryInitDiv('update_search_cate');
});

/* 商品分类选择函数 */
function gcategoryInitDiv(divId)
{
	if($("#" + divId).size()>0){
		$("#" + divId + " > select").get(0).onchange = gcategoryChangeDiv; // select的onchange事件
		window.onerror = function(){return true;}; //屏蔽jquery报错
	}
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
