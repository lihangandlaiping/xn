/**
 * Created by Administrator on 2017/5/4.
 */
(function(obj){
    obj.alert=function(title,icon){//icon 1成功 2失败 3警告
        var type=icon==1?'success':(icon==2?'error':(icon==3?'warning':'info'));
        swal({
            title: title,
            text: "我将在2秒内关闭.",
            timer: 10000,
            type:type,
            showConfirmButton: true
        });
    };
    obj.confirm=function(str,succ,erro){
        swal({
                title: str,
                text: "你的本次操作，将会影响数据内容。确定要继续执行吗？",
                type: "info",
                showCancelButton: true,
                confirmButtonColor: "#206ca4",
                confirmButtonText: "确认",
                cancelButtonText: "取消",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(isConfirm){
                sweetAlert.close();
                if (isConfirm) {
                    if(succ!=null)succ();
                } else {
                    if(erro!=null)erro();
                }
            });
    };
    obj.alert=function(tile)
    {
        swal({
            title: tile,
            text: "我将在2秒内关闭.",
            timer: 2000,
            showConfirmButton: false
        });
    };
    var _load=$.fn.load;
    $.fn.extend({'load': function(url,param,calbck)
    {
        //其他操作和处理
        //..
        //此处用apply方法调用原来的load方法，因为load方法属于对象，所以不可直接对象._load（...）
        layer.load(2, {
            shade: [0.5,'#fff'] //0.1透明度的白色背景
        });
        var newparam=param!=null&&typeof param!='function'?param:{};
        return _load.apply(this,[url,newparam,function(){
            layer.closeAll();
            if(calbck!=null&&typeof calbck=='function')calbck();
            else if(param!=null&&typeof param=='function')param();
        }]);
    }
    });

})(window);


function ajaxPostSend(url,data,suc,err)
{
    layer.load(2, {
        shade: [0.5,'#fff'] //0.1透明度的白色背景
    });
    // layer.closeAll();
    $.post(url,data?data:{},function(datas){
        layer.closeAll();
        if(datas.code==1)
        {
            swal(datas.msg, "您的本次操作成功,你的支持我们最大的动力！", "success");
            if(suc!=null&&typeof suc=='function')suc(datas);
        }
        else
        {
            swal(datas.msg, "本次操作错误,你的反馈使我们制胜的关键！", "error");
            if(suc!=null&&typeof err=='function')err(datas);
        }
        setTimeout(function(){
            sweetAlert.close();
            if(datas.url!=null&&datas.url!='')
            {
                location.href=datas.url;
            }
        },2000);

    },'json');
    return false;
}

function ajaxGetSend(url,data,suc,err)
{
    layer.load(2, {
        shade: [0.5,'#fff'] //0.1透明度的白色背景
    });
    layer.closeAll();
    $.get(url,data?data:{},function(datas){
        // layer.closeAll();
        if(datas.code==1)
        {
            swal(datas.msg, "您的本次操作成功,你的支持我们最大的动力！", "success");
            if(suc!=null&&typeof suc=='function')suc(datas);
        }
        else
        {
            swal(datas.msg, "本次操作错误,你的反馈使我们制胜的关键！", "error");
            if(err!=null&&typeof err=='function')err(datas);
        }
        setTimeout(function(){
            sweetAlert.close();
            if(datas.url!=null&&datas.url!='')
            {
                location.href=datas.url;
            }
        },2000);

    },'json');
    return false;
}
function ajaxFormSend(obj,suc,err)
{
    // layer.load(2, {
    //     shade: [0.5,'#fff'] //0.1透明度的白色背景
    // });
    var action=$(obj).attr('action');

    // layer.closeAll();
    $.post(action,$(obj).serialize(),function(datas){

        if(datas.code==1)
        {
            swal(datas.msg, "您的本次操作成功,你的支持我们最大的动力！", "success");
            if(suc!=null&&typeof suc=='function'){
                setTimeout(function(){
                    sweetAlert.close();
                },2000);
                suc(datas);
            }
            else
            {
                setTimeout(function(){
                    sweetAlert.close();
                    if(datas.url!=null&&datas.url!='')
                    {

                        location.href=datas.url;
                    }
                },2000);
            }
        }
        else
        {
            swal(datas.msg, "本次操作错误,你的反馈使我们制胜的关键！", "error");
            if(err!=null&&typeof err=='function'){
                setTimeout(function(){
                    sweetAlert.close();
                },2000);
                err(datas);

            }
            else
            {
                setTimeout(function(){
                    sweetAlert.close();
                    if(datas.url!=null&&datas.url!='')
                    {

                        location.href=datas.url;
                    }
                },2000);
            }
        }


    },'json');
    return false;
}
/**
 * 加载效果
 */
function showloding()
{
    layer.load(1, {
        shade: [0.5,'#fff'] //0.1透明度的白色背景
    });
    setTimeout(function () {
        layer.closeAll();
    },9000)
}
var _windowfunction=null;
/**
 * 弹出框
 * @param urls
 * @param title
 * @param fun
 */
function showdailog(urls,title,fun)
{
    title=title!=null?title:'弹出层';
    _windowfunction=fun!=null?fun:null;
    var htl='<div id="_dailogmodel" class="zent-portal zent-dialog-r-anchor"> ' +
        '<div data-reactroot=""> ' +
        '<div class="zent-dialog-r-backdrop"></div>' +
        '<div class="widget-attachment modal hide in" aria-hidden="false" style="display: block; top:100px;"><div class="js-main-region"><div><div class="modal-header"> ' +
        '<a class="close" data-dismiss="modal" onclick="close_dailogmodel(2)">×</a> ' +
        '<span class="title">'+title+'</span> ' +
        '</div><div class="modal-body"></div><div class="modal-footer clearfix"> <div class="selected-count-region js-selected-count-region hide" style="display: none;">已选择<span class="js-selected-count">2</span>个语音 </div> ' +
        '<div class="text-center"> ' +
        '<button class="ui-btn js-confirm ui-btn-disabled ui-btn-primary"  onclick="close_dailogmodel(1)">确认</button> ' +
        '</div> ' +
        '</div>' +
        '</div></div>';
    $('#_dailogmodel').remove();
    $('body').append(htl);
    $('#_dailogmodel .modal-body').load(urls);
}
/**
 * 关闭弹框
 * @param type
 * @param fu
 */
function close_dailogmodel(type)
{
    if(type==1)
    {
        _windowfunction();
    }
    $('#_dailogmodel').remove();
}
/**
 * 关闭所有弹出层
 */
function closelog()
{
    $('#_dailogmodel').remove();
    $('#_showconfirm').remove();
    layer.closeAll();
    sweetAlert.close();
}


