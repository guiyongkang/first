$(function () {
    var a = getQueryString("meid");
    var e = getCookie("key");
    $.ajax({
        type: "post",
        url: ApiUrl + "/index.php?act=withdraw&op=method_info",
        data: {
            key: e,
            meid: a
        },
        dataType: "json",
        success: function (a) {
            checkLogin(a.login);
			$("#title").val(a.datas.method_info.title);
            $("#name").val(a.datas.method_info.name);
            $("#no").val(a.datas.method_info.no);
            var e = a.datas.method_info.is_default == "1" ? true : false;
            $("#is_default").prop("checked", e);
            if (e) {
                $("#is_default").parents("label").addClass("checked")
            }
        }
    });
    $.sValid.init({
        rules: {
            name: "required",
            no: "required"
        },
        messages: {
            name: "姓名必填！",
            no: "账号必填！"
        },
        callback: function (a, e, r) {
            if (a.length > 0) {
                var i = "";
                $.map(e, function (a, e) {
                    i += "<p>" + a + "</p>"
                });
                errorTipsShow(i)
            } else {
                errorTipsHide()
            }
        }
    });
    $(".btn").click(function () {
        if ($.sValid()) {
            var r = $("#name").val();
            var d = $("#no").val();
            var o = $("#is_default").attr("checked") ? 1 : 0;
            $.ajax({
                type: "post",
                url: ApiUrl + "/index.php?act=withdraw&op=method_edit",
                data: {
                    key: e,
                    name: r,
                    no: d,
                    is_default: o,
                    meid: a
                },
                dataType: "json",
                success: function (a) {
                    location.href = WapSiteUrl + "/tmpl/distributor/withdraw_method.html"
                }
            })
        }
    });
    
});