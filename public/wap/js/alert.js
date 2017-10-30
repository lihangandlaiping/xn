$(function(){
    $(".back").click(function(){
        history.back();
    });
    load_size_com();
            window.onresize = function () {
                load_size_com();
            }
 });

        function load_size_com() {
            $(".top .title").css('left',($(window).width()-100)/2);
        }
 var remove_qq_alert = function(){
	$('.alertBox').add($('#mask')).remove(); 	
};
        window['alert'] = window['qq_alert'] = function(msg,loading){    
    		if($('.mask').length && loading){ //多次调用loading
    			return 0;	
    		}
    		remove_qq_alert(); 
            if(!loading){
    			clearTimeout(window.alert.time);
                var obj = $('<div class="alertBox">' + msg + '</div>');
                $('body').append(obj);
                window.alert.time = setTimeout(function(){$(".alertBox").remove()},2000);
            }else{
                $(document.body).append($('<div class="alertBox"><div class="box_loading"><div class="loading_mask"></div></div>' + msg + '</div><div id="mask" class="mask"></div>'));
                $('.alertBox').css({"webkitAnimationName":"boxfade_loading","opacity":0.8});
                $('#mask').height(window.innerHeight + 'px');
        }
};
        function back(){
            history.back();
        }

var dateCONFIG={
    oneDay: 3600 * 24,
    oneMonth: 3600 * 24 * 30,
    oneYear: 3600 * 24 * 365
};
function calcTime(ts){
    
    var C = dateCONFIG,
            dur, t1 = new Date(),
            t2 = new Date();
    //t1.setTime(ts * 1000);
    t1=new Date(ts);
    dur = Math.ceil((t2.getTime() - t1.getTime()) / 1000);
    //return dur;
    if (dur > C.oneYear) {
    return Math.floor(dur / C.oneYear) + '年前';
    }
    else if (dur > C.oneMonth) {
        return Math.floor(dur / C.oneMonth) + '月前';
    }
    else if (dur > C.oneDay) {
            return Math.floor(dur / C.oneDay) + '天前';
        }
    else if (dur > 3600) {
            return Math.floor(dur / 3600) + '小时前';
        }
    else if (dur > 60) {
            return Math.floor(dur / 60) + '分钟前';
        }
    else {
            return dur + '秒前';
        }
};

//添加cookie
function addCookie(objName, objValue, objHours) {//添加cookie
    var str = objName + "=" + escape(objValue);
    if (objHours > 0) {//为0时不设定过期时间，浏览器关闭时cookie自动消失
        var date = new Date();
        var ms = objHours * 3600 * 1000;
        date.setTime(date.getTime() + ms);
        str += "; expires=" + date.toGMTString();
    }
    document.cookie = str;
}

//获取cookie
function getCookie(objName) {//获取指定名称的cookie的值
    var arrStr = document.cookie.split("; ");
    for (var i = 0; i < arrStr.length; i++) {
        var temp = arrStr[i].split("=");
        if (temp[0] == objName) return unescape(temp[1]);
    }
}

//删除cookie
function delCookie(name) {//为了删除指定名称的cookie，可以将其过期时间设定为一个过去的时间
    var date = new Date();
    date.setTime(date.getTime() - 10000);
    document.cookie = name + "=a; expires=" + date.toGMTString();
}