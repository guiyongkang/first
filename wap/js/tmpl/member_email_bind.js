$(function() {
    var e = getCookie('key');
    if (!e) {
        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
        return
    }
    loadSeccode();
    $('#refreshcode').bind('click',
    function() {
        loadSeccode()
    });
    $.ajax({
        type: 'get',
        url: ApiUrl + '/index.php?act=member_account&op=get_email_info',
        data: {
            key: e
        },
        dataType: 'json',
        success: function(e) {
            if (e.datas.state) {
                $('#email').val(e.datas.email)
            }
        }
    });
    $.sValid.init({
        rules: {
            captcha: {
                required: true,
                minlength: 4
            },
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            captcha: {
                required: '请填写图形验证码',
                minlength: '图形验证码不正确'
            },
            email: {
                required: '请填写邮箱',
                email: '邮箱不正确'
            }
        },
        callback: function(e, a, t) {
            if (e.length > 0) {
                var o = '';
                $.map(a,
                function(e, a) {
                    o += '<p>' + e + '</p>'
                });
                errorTipsShow(o)
            } else {
                errorTipsHide()
            }
        }
    });
    $('#nextform').click(function() {
		if ($.sValid()) {
			if (!$(this).parent().hasClass('ok')) {
				return false
			}
			var a = $.trim($('#email').val());
			var t = $.trim($('#captcha').val());
            var o = $.trim($('#codekey').val());
			if (a) {
				$.ajax({
					type: 'post',
					url: ApiUrl + '/index.php?act=member_account&op=bind_email_step1',
					data: {
						key: e,
						email: a,
						captcha: t,
                        codekey: o
					},
					dataType: 'json',
					success: function(e) {
						if (e.code == 200 && e.datas == 1 && typeof e.datas.error == 'undefined') {
							$.sDialog({
								skin: 'block',
								content: '绑定成功',
								okBtn: false,
								cancelBtn: false
							});
							setTimeout('location.href = WapSiteUrl+\'/tmpl/member/member_account.html\'', 2e3)
						} else {
							errorTipsShow('<p>' + e.datas.error + '</p>')
						}
					}
				})
			}
		}
    })
});