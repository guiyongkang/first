var key = getCookie("key");

var meid;
var message = {};
var area_info;
var goods_id;
$(function() {
	$.ajax({
		type: "post",
		url:ApiUrl+"/index.php?act=withdraw&op=get_enabled_money",
		data: {
			key: key
		},
		dataType: "json",
		success: function (e) {
			var i = template.render("pd_count_model", e.datas);
			$("#pd_count").html(i);
		}
	});
	
	//获取提现方式列表
    $("#list-address-valve").click(function() {
        $.ajax({
            type: "post",
            url: ApiUrl + "/index.php?act=withdraw&op=method_list",
            data: {
                key: key
            },
            dataType: "json",
            async: false,
            success: function(e) {
                checkLogin(e.login);
                if (e.datas.method_list == null) {
                    return false
                }
                var a = e.datas;
                a.meid = meid;
                var i = template.render("list-address-add-list-script", a);
                $("#list-address-add-list-ul").html(i)
            }
        });
    });
	
	$.ajax({
		type: "post",
		url:ApiUrl+"/index.php?act=withdraw&op=withdraw_enabled",
		data: {
			key: key
		},
		dataType: "json",
		success: function (e) {
			if(e.datas.withdraw_list==0){
				return false;
			}else{
				select_code(e.datas.withdraw_list[0].code,e.datas.withdraw_list[0].name);
				$("#list-items-scroll").html(template.render("search_items", e));
			}
		}
	});
	
	$.animationLeft({
		valve: "#vtitle",
		wrapper: ".nctouch-full-mask",
		scroll: "#list-items-scroll"
	});
    $.animationLeft({
        valve: "#list-address-valve",
        wrapper: "#list-address-wrapper",
        scroll: "#list-address-scroll"
    });
    $("#list-address-add-list-ul").on("click", "li",
    function() {
        $(this).addClass("selected").siblings().removeClass("selected");
        eval("method_info = " + $(this).attr("data-param"));
        _init(method_info.meid);
        $("#list-address-wrapper").find(".header-l > a").click()
    });
    $.animationLeft({
        valve: "#new-address-valve",
        wrapper: "#new-address-wrapper",
        scroll: ""
    });
    
    
    template.helper("isEmpty",
    function(e) {
        var a = true;
        $.each(e,
        function(e, i) {
            a = false;
            return false
        });
        return a
    });
    
    var _init = function(e) {
        var a = 0;
        $.ajax({
            type: "post",
            url: ApiUrl + "/index.php?act=withdraw&op=method_check",
            dataType: "json",
            data: {
                key: key,
                meid: e
            },
            success: function(e) {
                checkLogin(e.login);
                if (e.datas.error) {
                    $.sDialog({
                        skin: "red",
                        content: e.datas.error,
                        okBtn: false,
                        cancelBtn: false
                    });
                    return false
                }
                
                if ($.isEmptyObject(e.datas.method_info)) {
                    $.sDialog({
                        skin: "block",
                        content: "请添加提现方式",
                        okFn: function() {
                            $("#new-address-valve").click()
                        },
                        cancelFn: function() {
                            history.go( - 1)
                        }
                    });
                    return false
                }
				insertHtmlAddress(e.datas.method_info);
            }
        })
    };
	
    _init();
	
    var insertHtmlAddress = function(e, a) {
        meid = e.meid;
        $("#title").html(e.title);
		if(e.name){
			$('#method-detail').show();
			$('#detail').html(e.name+'&nbsp;'+e.no);
		}else{
			$('#method-detail').hide();
			$('#detail').html('');
		}
		
		if(e.desc){
			$('.withdraw_info').html(e.desc).show();
		}else{
			$('.withdraw_info').html('').hide();
		}
		
		$('.withdraw_info').attr('ret',meid);
    };
    
    $.sValid.init({
        rules: {
            vname: "required",
            vno: "required"
        },
        messages: {
            vname: "姓名必填！",
            vno: "账号必填！"
        },
        callback: function(e, a, i) {
            if (e.length > 0) {
                var t = "";
                $.map(a,
                function(e, a) {
                    t += "<p>" + e + "</p>"
                });
                errorTipsShow(t)
            } else {
                errorTipsHide()
            }
        }
    });
    $("#add_address_form").find(".btn").click(function() {
        var submit_flag = false;
		var code = $('#item_code').attr('value');
		if(code == 'wxhongbao' || code == 'wxzhuanzhang' || code == 'yue'){
			submit_flag = true;
		}else{
			submit_flag = $.sValid();
		}
		
        if (submit_flag) {
            var e = {};
            e.key = key;
            e.name = $("#vname").val();
            e.no = $("#vno").val();
            e.code = $("#item_code").val();
            e.is_default = 0;
            $.ajax({
                type: "post",
                url: ApiUrl + "/index.php?act=withdraw&op=method_add",
                data: e,
                dataType: "json",
                success: function(a) {
                    if (!a.datas.error) {
                        _init(a.datas.method_id);
                        $("#new-address-wrapper,#list-address-wrapper").find(".header-l > a").click()
                    }
                }
            })
        }
    });
	
	$(".withdraw_btn").find("a").click(function() {
		if($('#money').val()==''){
			$('.withdraw_error_info').html('请输入提现金额').show();
		}
		
		if(isNaN($('#money').val())){
			$('.withdraw_error_info').html('提现金额请输入数字').show();
		}
        $.ajax({
            type: "post",
            url: ApiUrl + "/index.php?act=withdraw&op=withdraw_apply",
            data: {
				key:key,
				meid:$('.withdraw_info').attr('ret'),
				money:$('#money').val()
			},
            dataType: "json",
            success: function(a) {
                if (a.datas.result) {
					$.sDialog({
                        skin: "block",
                        content: a.datas.message,
                        okFn: function() {
                           	window.location.href = WapSiteUrl + "/tmpl/distributor/withdraw_record.html"
                        },
                        cancelFn: function() {
                            window.location.href = WapSiteUrl + "/tmpl/distributor/withdraw_record.html"
                        }
                    });
                    
                }else{
					$.sDialog({
                        skin: "block",
                        content: a.datas.errorinfo,
                        okFn: function() {
                           	window.location.href = WapSiteUrl + "/tmpl/distributor/withdraw.html"
                        },
                        cancelFn: function() {
                            window.location.href = WapSiteUrl + "/tmpl/distributor/withdraw.html"
                        }
                    });
				}
            }
        })
    });
	
	$('#money').keyup(function(){
		$('.withdraw_error_info').html('').hide();
	});
});

function select_code(code,name){
	$('#item_code').attr('value',code);
	$('#vtitle').attr('value',name);	
	if(code == 'wxhongbao' || code == 'wxzhuanzhang' || code == 'yue'){
		$('.other_item').hide();
	}else{
		$('.other_item').show();
	}
	$("#new-type-wrapper").addClass("right").removeClass("left");
}