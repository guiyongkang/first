$(function() {
	var e = getCookie("key");
	var r = getQueryString("refund_id");
	template.helper("isEmpty", function(e) {
		for (var r in e) {
			return false
		}
		return true
	});
	$.getJSON(ApiUrl + "/index.php?act=member_return&op=get_return_info", {
		key: e,
		return_id: r
	}, function(e) {
		$("#return-info-div").html(template.render("return-info-script", e.datas))
	})
});