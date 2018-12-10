$(function () {
	$.animationLeft({
		valve: "#item_title",
		wrapper: ".nctouch-full-mask",
		scroll: "#list-items-scroll"
	});
	
    var a = getCookie("key");
    $.sValid.init({
        rules: {
            name: "required",
            no: "required"
        },
        messages: {
            name: "姓名必填！",
            no: "账号必填！"
        },
        callback: function (a, e, r) {
            if (a.length > 0) {
                var i = "";
                $.map(e, function (a, e) {
                    i += "<p>" + a + "</p>"
                });
                errorTipsShow(i)
            } else {
                errorTipsHide()
            }
        }
    });
    $("#header-nav").click(function () {
        $(".btn").click()
    });
    $(".btn").click(function () {
		var submit_flag = false;
		var code = $('#item_code').attr('value');
		if(code == 'wxhongbao' || code == 'wxzhuanzhang' || code == 'yue'){
			submit_flag = true;
		}else{
			submit_flag = $.sValid();
		}
		
        if (submit_flag) {
            var e = $("#name").val();
            var r = $("#no").val();
			var c = $("#item_code").val();
            var o = $("#is_default").attr("checked") ? 1 : 0;
            $.ajax({
                type: "post",
                url: ApiUrl + "/index.php?act=withdraw&op=method_add",
                data: {
                    key: a,
                    name: e,
                    no: r,
					code: c,
                    is_default: o
                },
                dataType: "json",
                success: function (a) {
                    if (a) {
                        location.href = WapSiteUrl + "/tmpl/distributor/withdraw_method.html"
                    } else {
                        alert('后台未设置可用提现方式');
                    }
                }
            })
        }
    });
	
	$.ajax({
		type: "post",
		url:ApiUrl+"/index.php?act=withdraw&op=withdraw_enabled",
		data: {
			key: a
		},
    	dataType: "json",
		success: function (e) {
			if(e.datas.withdraw_list==0){
				alert('后台未设置可用提现方式');
				location.href = WapSiteUrl + "/tmpl/distributor/withdraw_method.html"
			}else{
				select_code(e.datas.withdraw_list[0].code,e.datas.withdraw_list[0].name);
				$("#list-items-scroll").html(template.render("search_items", e));
			}
		}
    });
	
	
});

function select_code(code,name){
	$('#item_code').attr('value',code);
	$('#title').attr('value',name);	
	if(code == 'wxhongbao' || code == 'wxzhuanzhang' || code == 'yue'){
		$('.other_item').hide();
	}else{
		$('.other_item').show();
	}
	$(".nctouch-full-mask").addClass("right").removeClass("left");
}

