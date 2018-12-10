var order_id = getQueryString('order_id');
var store_id = '';
var map_index_id = '';
var map_list = [];
$(function(){
	var key = getCookie('key');
	if(!key){
		window.location.href = WapSiteUrl+'/tmpl/member/login.html';
	}
    $.getJSON(ApiUrl + '/index.php?act=member_vr_order&op=order_info',{key:key,order_id:order_id}, function(result) {
    	if (result.datas.error) {
    		return ;
    	} 
    	result.datas.order_info.WapSiteUrl = WapSiteUrl;
    	$('#order-info-container').html(template.render('order-info-tmpl',result.datas.order_info));
    	$('#buyer_phone').val(result.datas.order_info.buyer_phone);

        // 取消
        $(".cancel-order").click(cancelOrder);
        // 评价
        $(".evaluation-order").click(evaluationOrder);
        // 全部退款
        $('.all_refund_order').click(allRefundOrder);
        $('#resend').click(reSend);
        $('#tosend').click(toSend);
        
        $.animationLeft({
            valve : '#store_addr_list',
            wrapper : '#list-address-wrapper',
            scroll : '#list-address-scroll'
        });
    });

    //取消订单
    function cancelOrder(){
        var order_id = $(this).attr("order_id");

        $.sDialog({
            content: '确定取消订单？',
            okFn: function() { cancelOrderId(order_id); }
        });
    }

    function cancelOrderId(order_id) {
        $.ajax({
            type:"post",
            url:ApiUrl+"/index.php?act=member_vr_order&op=order_cancel",
            data:{order_id:order_id,key:key},
            dataType:"json",
            success:function(result){
                if(result.datas && result.datas == 1){
                	window.location.reload();
                }
            }
        });
    }

    function reSend(){
        // 从下到上动态显示隐藏内容
    	$.animationUp({valve:'',scroll:''});
    	$('#buyer_phone').on('blur',function(){
    		if ($(this).val() != '' && ! /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test($(this).val())) {
    			$(this).val(/\d+/.exec($(this).val()));
    		}
    	});
    };

    function toSend(){
    	var buyer_phone = $('#buyer_phone').val();
    	$.ajax({
            type:"post",
            url:ApiUrl+"/index.php?act=member_vr_order&op=resend",
            data:{order_id:order_id,buyer_phone:buyer_phone,key:key},
            dataType:"json",
            success:function(result){
                if(result.datas && result.datas == 1){
                	$('.nctouch-bottom-mask').addClass('down').removeClass('up');
                } else {
                	$('.rpt_error_tip').html(result.datas.error).show();
                }
            }
        });
    }

    // 评价
    function evaluationOrder() {
        var orderId = $(this).attr('order_id');
        location.href = WapSiteUrl + '/tmpl/member/member_vr_evaluation.html?order_id=' + orderId;
        
    }
    // 全部退款
    function allRefundOrder() {
        var orderId = $(this).attr('order_id');
        location.href = WapSiteUrl + '/tmpl/member/refund_all.html?order_id=' + orderId;
    }

    $('#list-address-scroll').on('click','dl > a,#map_all',map);
    $('#map_all').on('click',map);

    function map(){
    	  $('#map-wrappers').removeClass('hide').removeClass('right').addClass('left');
    	  $('#map-wrappers').on('click', '.header-l > a', function(){
    		  $('#map-wrappers').addClass('right').removeClass('left');
    	  });
    	  $('#baidu_map').css('width', document.body.clientWidth);
    	  $('#baidu_map').css('height', document.body.clientHeight);
    	  map_index_id = $(this).attr('index_id');
    	  if (typeof map_index_id != 'string'){
    		  map_index_id = '';
    	  }
          if (typeof(map_js_flag) == 'undefined') {
              $.ajax({
                  url: WapSiteUrl+'/js/map.js',
                  dataType: "script",
                  async: false
              });
          }
    	if (typeof BMap == 'object') {
    	    baidu_init();
    	} else {
    	    load_script();
    	}
   }
});