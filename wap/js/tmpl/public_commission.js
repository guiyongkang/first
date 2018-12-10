var page = 10;
var curpage = 1;
var hasmore = true;
var footer = false;
var level = 'all';
var reset = true;
var area_id = getQueryString("area_id");
$(function() {
	$.animationLeft({
		valve: "#datastastics",
		wrapper: ".nctouch-full-mask",
		scroll: "#list-items-scroll"
	});
	
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/tmpl/member/login.html"
    }
	
    t();
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 1) {
            t()
        }
    });
});
function get_footer() {
    if (!footer) {
        footer = true;
        $.ajax({
            url: WapSiteUrl + "/js/tmpl/footer.js",
            dataType: "script"
        })
    }
}

function select_level(type){
	level = type;
	reset = true;
	$(".nctouch-full-mask").addClass("right").removeClass("left");
	t();
}

function t() {
	if (reset) {
        curpage = 1;
        hasMore = true
    }
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
		param.key = getCookie("key")
	}
	param.level = level;
	$.getJSON(ApiUrl + "/index.php?act=public_commission", param, function(e) {
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
			e.datas.commission_list = []
		}
		$(".loading").remove();
		curpage++;
		var r = template.render("commission-list-tmpl", e);
		$('#datastastics b').html(e.datas.curinfo);
		$('#datastastics span').html('&yen;'+e.datas.total);
		hasmore = e.hasmore;
		if(hasmore==false){
			get_footer();
		}
		if (reset) {
            reset = false;
            $("#commission-list").html(r)
        } else {
            $("#commission-list").append(r)
        }
	})
}