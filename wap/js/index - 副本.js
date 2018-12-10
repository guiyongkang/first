$(function() {

    var headerClone = $('#header').clone();
    $(window).scroll(function(){
        if ($(window).scrollTop() <= $('#main-container1').height()) {
            headerClone = $('#header').clone();
            $('#header').remove();
            headerClone.addClass('transparent').removeClass('');
            headerClone.prependTo('.nctouch-home-top');
        } else {
            headerClone = $('#header').clone();
            $('#header').remove();
            headerClone.addClass('').removeClass('transparent');
            headerClone.prependTo('body');
        }
    });
    $.ajax({
        url: ApiUrl + '/index.php?act=index',
        type: 'get',
        dataType: 'json',
        success: function(result) {
            var data = result.datas;
            var html = '';

            $.each(data, function(k, v) {
                $.each(v, function(kk, vv) {
                    switch (kk) {
                        case 'adv_list':
                        case 'home3':
                            $.each(vv.item, function(k3, v3) {
                                vv.item[k3].url = buildUrl(v3.type, v3.data);
                            });
                            break;

                        case 'home1':
                            vv.url = buildUrl(vv.type, vv.data);
                            break;

                        case 'home2':
                        case 'home4':
                            vv.square_url = buildUrl(vv.square_type, vv.square_data);
                            vv.rectangle1_url = buildUrl(vv.rectangle1_type, vv.rectangle1_data);
                            vv.rectangle2_url = buildUrl(vv.rectangle2_type, vv.rectangle2_data);
                            break;
                    }
                    if (k == 0) {
                        $('#main-container1').html(template.render(kk, vv));
                    } else {
                        html += template.render(kk, vv);
                    }
                    return false;
                });
            });

            $('#main-container2').html(html);

            $('.adv_list').each(function() {
                if ($(this).find('.item').length < 2) {
                    return;
                }

                Swipe(this, {
                    startSlide: 2,
                    speed: 400,
                    auto: 3000,
                    continuous: true,
                    disableScroll: false,
                    stopPropagation: false,
                    callback: function(index, elem) {},
                    transitionEnd: function(index, elem) {}
                });
            });

        }
    });
	$.ajax({
        url: ApiUrl + '/index.php?act=index&op=index_module',
        type: 'get',
        dataType: 'json',
        success: function(result) {
            var data = result.datas;
            var html = '';
			if(data){
				$.each(data, function(k, v) {
					
					html += '<li>'+
								'<a href="'+v['url']+'">'+
									'<span style="background-color:'+v['bg_color']+'">'+
										'<i style="background-image: url('+v['bg_img']+');"></i>'+
									'</span>'+
									'<p>'+v['name']+'</p>'+
								'</a>'+
							'</li>'; 
				});
                $('.nctouch-home-nav ul').html(html).show();
			}else{
				$('.nctouch-home-nav ul').html('').show();
			}
        }
    });
	
	
	
});
    var interval = 1;//断开后计时
	var socket = {};
	var connect_url = '';
	var key = getCookie('key');
    $.getJSON(ApiUrl + '/index.php?act=member_chat&op=get_node_info', {
        key: key,
    },
    function(data) {
		var result = data.datas;
		connect_url = result.node_site_url;
    });
	setTimeout('getconnect()', 1000);
	function getconnect(){
		loadJs(connect_url + '/socket.io/socket.io.js');
		clearInterval(interval);
		if ( typeof io === 'function' ) {
			socket = io(connect_url, { 'path': '/socket.io', 'reconnection': false });
			socket.on('connect', function () {  
			    socket.on('get_checkout', function(rs){
					alert(rs.pay_sn);
				});
				socket.on('disconnect', function () {
				    interval = setInterval( getconnect, 60000);//断开1分钟后重新连接服务器
				});
			});
		}
	}
//返利
var uid = window.location.href.split('#V3');
var  fragment = uid[1];
if(fragment){
	if (fragment.indexOf('V3') == 0) {
		document.cookie='uid=0';
	}else {
		document.cookie='uid='+uid[1];
	}
}