var curpage = 1;
var hasMore = true;
var footer = false;
var level = 1;
var reset = true;
var orderKey = "";
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
    })
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

function select_level(id){
	level = id;
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
        if (!hasMore) {
            return false
        }
        hasMore = false;
        $.ajax({
            type: "post",
            url: ApiUrl + "/index.php?act=public_term&curpage=" + curpage,
            data: {
                key: getCookie("key"),
                level: level,
				area_id : area_id
            },
            dataType: "json",
            success: function(e) {
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
                curpage++;
				$('#datastastics b').html(e.datas.curinfo);
				$('#datastastics span').html(e.datas.total);
                hasMore = e.datas.hasmore;
                if (!hasMore) {
                    get_footer()
                }
                if (e.datas.term_list.length <= 0) {
                    $("#footer").addClass("posa")
                } else {
                    $("#footer").removeClass("posa")
                }
                var t = e;
                t.WapSiteUrl = WapSiteUrl;
                t.ApiUrl = ApiUrl;
                t.key = getCookie("key");
                template.helper("$getLocalTime",
                function(e) {
                    var t = new Date(parseInt(e) * 1e3);
                    var r = "";
                    r += t.getFullYear() + "年";
                    r += t.getMonth() + 1 + "月";
                    r += t.getDate() + "日 ";
                    r += t.getHours() + ":";
                    r += t.getMinutes();
                    return r
                });
                template.helper("p2f",
                function(e) {
                    return (parseFloat(e) || 0).toFixed(2)
                });
                template.helper("parseInt",
                function(e) {
                    return parseInt(e)
                });
				$("#list-items-scroll").html(template.render("search_items", t));
                var r = template.render("term-list-tmpl", t);
                if (reset) {
                    reset = false;
                    $("#term-list").html(r)
                } else {
                    $("#term-list").append(r)
                }
            }
        })
}