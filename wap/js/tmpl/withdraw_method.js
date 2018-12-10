$(function(){
	var e=getCookie("key");
	if(!e){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html"
	}
	function s(){
		$.ajax({
			type:"post",
			url:ApiUrl+"/index.php?act=withdraw&op=withdraw_method",
			data:{
				key:e
			},
			dataType:"json",
			success:function(e){
				checkLogin(e.login);
				if(e.datas.withdraw_list==null){
					return false
				}
				var s=e.datas;
				var t=template.render("saddress_list",s);
				$("#address_list").empty();
				$("#address_list").append(t);
				$(".deladdress").click(function(){
					var e=$(this).attr("meid");
					$.sDialog({
						skin:"block",
						content:"确认删除吗？",
						okBtn:true,
						cancelBtn:true,
						okFn:function(){
							a(e)
						}
					})
				})
			}
		})
	}
	s();
	function a(a){
		$.ajax({
			type:"post",
			url:ApiUrl+"/index.php?act=withdraw&op=withdraw_del",
			data:{
				meid:a,
				key:e
			},
			dataType:"json",
			success:function(e){
				checkLogin(e.login);
				if(e){
					s()
				}
			}
		})
	}
});