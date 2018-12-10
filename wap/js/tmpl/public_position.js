var page = pagesize;
var curpage = 1;
var hasmore = true;
var footer = false;
var area_id = getQueryString("area_id");

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
	param.area_id = area_id;
	if (key != "") {
		param.key = key
	}
	$.getJSON(ApiUrl + "/index.php?act=public_position", param, function(e) {
		checkLogin(e.login);
		if (e.distributor == 0) {
			$.sDialog({
                skin: 'block',
                content: e.datas.error,
                okFn: function() {
					location.href = WapSiteUrl + '/tmpl/member/member.html';
                },
                cancelFn: function() {
                    location.href = WapSiteUrl + '/tmpl/member/member.html';
                }
            });
			return false;
		}
		if (!e) {
			e = [];
			e.datas = [];
			e.datas.record_list = []
		}
		$(".loading").remove();
		curpage++;
		var r = template.render("position-list-tmpl", e);
		$("#position-list").append(r);
		hasmore = e.hasmore
	})
}