var key = getCookie('key');
$(function() {
    $.sValid.init({
        rules: {
            amount: 'number',
        },
        messages: {
            amount: '金额不正确！',
        },
        callback: function(e, a, i) {
            if (e.length > 0) {
                var t = '';
                $.map(a,
                function(e, a) {
                    t += '<p>' + e + '</p>'
                });
                errorTipsShow(t);
            } else {
                errorTipsHide();
            }
        }
    });
	$('#recharge_add').click(function() {
		if ($.sValid()) {
			$.ajax({
				type: 'post',
				url: ApiUrl + '/index.php?act=member_fund&op=recharge_add',
				data: {
					key: key,
					amount: $('#amount').val(),
				},
				dataType: 'json',
				success: function(e) {
					checkLogin(e.login);
					if (e.datas.error) {
						$.sDialog({
							skin: 'red',
							content: e.datas.error,
							okBtn: false,
							cancelBtn: false
						});
						return false;
					}
					toPay(e.datas, 'member_fund', 'pd_pay');
				}
			})
		}
    })
});