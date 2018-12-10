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
	$.getJSON(ApiUrl + "/index.php?act=team", param, function(e) {
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
		$("#teamname").html(e.datas.my_info);
		curpage++;
		var r = template.render("team-list-tmpl", e);
		$("#team-list").append(r);
		hasmore = e.hasmore
	})
}