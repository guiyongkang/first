var key = getCookie('key');
$(function() {
	$('#pd_zz_btn').click(function() {
		if($('#userno').val()==''){
			$.sDialog({
				skin: 'red',
				content: '请输入收款会员ID',
				okBtn: false,
				cancelBtn: false
			});
			return false;
		}
		
		if(isNaN($('#userno').val())){
			$.sDialog({
				skin: 'red',
				content: '收款会员ID必须是整数',
				okBtn: false,
				cancelBtn: false
			});
			return false;
		}
		if($('#amount').val()==''){
			$.sDialog({
				skin: 'red',
				content: '请输入转账金额',
				okBtn: false,
				cancelBtn: false
			});
			return false;
		}
		
		if(isNaN($('#amount').val())){
			$.sDialog({
				skin: 'red',
				content: '转账金额必须是数字',
				okBtn: false,
				cancelBtn: false
			});
			return false;
		}
		
			$.ajax({
				type: 'post',
				url: ApiUrl + '/index.php?act=member_fund&op=pdzz',
				data: {
					key: key,
					amount: $('#amount').val(),
					userno: $('#userno').val()
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
					}else{
						$.sDialog({
							skin: "block",
							content: e.datas.message,
							okFn: function() {
								window.location.href = WapSiteUrl + "/tmpl/member/pdzzlist.html"
							},
							cancelFn: function() {
								window.location.href = WapSiteUrl + "/tmpl/member/pdzzlist.html"
							}
						});
					}					
				}
			})
    })
});