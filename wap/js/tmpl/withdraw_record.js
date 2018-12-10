var page = pagesize;
var curpage = 1;
var hasmore = true;
var footer = false;

$(function() {
	var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/tmpl/member/login.html"
    }
	
	get_list();
	$(window).scroll(function() {
		if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
			get_list()
		}
	});
});

function get_list() {
	$(".loading").remove();
	if (!hasmore) {
		return false
	}
	hasmore = false;
	param = {};
	param.page = page;
	param.curpage = curpage;
	if (key != "") {
		param.key = key
	}
	$.getJSON(ApiUrl + "/index.php?act=withdraw&op=withdraw_record", param, function(e) {
		checkLogin(e.login);
		if (!e) {
			e = [];
			e.datas = [];
			e.datas.record_list = []
		}
		$(".loading").remove();
		curpage++;
		var r = template.render("withdraw-list-tmpl", e);
		$("#withdraw-list").append(r);
		hasmore = e.hasmore
	})
}