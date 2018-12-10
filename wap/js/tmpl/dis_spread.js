$(function() {
    var e = getCookie("key");
    if (!e) {
        window.location.href = WapSiteUrl + "/tmpl/member/login.html"
    }
	
	$.ajax({
        type: "post",
        url: ApiUrl + "/index.php?act=distributor_spread&op="+spread_type,
        data: {
            key: e
        },
        dataType: "json",
        success: function(a) {
            checkLogin(a.login);
			if (a.distributor == 0) {
				$.sDialog({
                    skin: 'block',
                    content: a.datas.error,
                    okFn: function() {
						location.href = WapSiteUrl + '/tmpl/member/member.html';
                    },
                    cancelFn: function() {
                        location.href = WapSiteUrl + '/tmpl/member/member.html';
                    }
                });
				return false;
			}
            var e = '<img src="'+a.datas.imgpath+'" />';
            $("#spread_qrcode").html(e);
        }
    })
});