    var key = getCookie('key');
    if (!key) {
        window.location.href = WapSiteUrl+'/tmpl/member/login.html';
    }
    function showSignin(){
        //检验是否能签到
        $.getJSON(ApiUrl + '/index.php?act=member_signin&op=checksignin', {'key':key}, function(result){
            if(result.code == 200){
                $("#points_signin").html(result.datas.points_signin);
                $("#signinbtn").show();
                $("#completedbtn").hide();
            }else{
                if (result.state == 'isclose') {//如果关闭了签到功能，则不显示签到按钮
                    location.href = WapSiteUrl;
                }else{//如果已经签到完成，则显示已签到
                    $("#signinbtn").hide();
                    $("#completedbtn").show();
                }
            }
        });
    }
    //加载签到日志
    var load_class = new ncScrollLoad();
    function getSigninLog(){
        load_class.loadInit({
            'url':ApiUrl + '/index.php?act=member_signin&op=signin_list',
            'getparam':{key:key},
            'tmplid':'loglist_tpl',
            'containerobj':$("#loglist"),
            'iIntervalId':true
        });
    }

    $(function(){
        showSignin();
        //获取会员元
        $.getJSON(ApiUrl + '/index.php?act=member_index&op=my_asset', {'key':key,'fields':'point'}, function(result){
            $("#pointnum").html(result.datas.point);
        });
        getSigninLog();
        $("#signinbtn").click(function(){
            if ($("#signinbtn").hasClass('loading')) {
                return false;
            }
            $("#signinbtn").addClass('loading');
            //获取详情
            $.getJSON(ApiUrl + '/index.php?act=member_signin&op=signin_add', {'key':key}, function(result){
                if(result.code == 200){
                    $("#pointnum").html(result.datas.point);
                    $("#completedbtn").show();
                    $("#signinbtn").hide();
                    getSigninLog();
                }
                $("#signinbtn").removeClass('loading');
            });
        });
        $("#description_link").click(function(){
            var con = $("#description_info").html();
            $.sDialog({
                content: con,
                "width": 100,
                "height": 100,
                "cancelBtn": false,
                "lock": true
            });
        });
    });