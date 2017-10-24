document.onreadystatechange=function(){
    if(document.readyState=='complete'||document.readyState=='loading')
    {
        setTimeout(function(){customer_close();},100)
    }
    else
    {
        customer_loading();
        $('#customer_mask').css({'background-color':'rgba(255,255,255,1)'});
    }

};
var M = {
"scroll":null
};
(function(w,d){
    w.alert=function(title, time)
    {
        time=time?time:2000;
        M.dialog1 = jqueryAlert({
            'content' : title,
            'closeTime' : time
        })
    };
    w.opend=function(alertContent,suc){
        if(M.dialog5){
            return M.dialog5.show();
        }
        M.dialog5 = jqueryAlert({
            'content' : alertContent ,
            'modal'   : true,
            'contentTextAlign' : 'left',
            'width'   : '90%',
            'animateType' : 'linear',
            'buttons' :{
                '取消' : function(){
                    M.dialog5.close();
                },
                '确认' : function(){
                    M.dialog5.close();
                    suc();
                }
            }
        })
    };
    var _load=$.fn.load;
    $.fn.extend({'load': function(url,param,calbck)
    {
        //其他操作和处理
        //..
        //此处用apply方法调用原来的load方法，因为load方法属于对象，所以不可直接对象._load（...）
        customer_loading();
        return _load.apply(this,[url,param,function(){
            customer_close();
            if(calbck!=null&&typeof calbck=='function')calbck();
            else if(param!=null&&typeof param=='function')param();
        }]);
    }
    });

})(window,document);



function customer_mask()
{
    var html='<div id="customer_mask" class="customer_mask"></div>';
    $('body').append(html);
}
function customer_loading()
{
    customer_mask();
    $('#customer_mask').html(' <div class="loading3"> <div>loading..</div> <div></div> <div></div> </div>');
}
function customer_close()
{
    $('.customer_mask').remove();
}
/**
 *
 * @param url
 * @param data
 * @param suc
 * @param err
 * @returns {boolean}
 */
function ajaxPostSend(url,data,suc,err)
{
    customer_loading();
    $.post(url,data?data:{},function(datas){

        if(datas.code==1)
        {
            if(suc!=null&&typeof suc=='function')suc(datas);
        }
        else
        {
            alert(datas.msg,1500);
            if(err!=null&&typeof err=='function')err(datas);
        }
        if(datas.url!=null&&datas.url!='')
        {
            location.href=datas.url;
        }
        setTimeout(function(){
            customer_close();
        },500);

    },'json');
    return false;
}

function ajaxGetSend(url,data,suc,err)
{
    customer_loading();
    $.get(url,data?data:{},function(datas){
        customer_close();
        if(datas.code==1)
        {
            if(suc!=null&&typeof suc=='function'){
                suc(datas);
            }
        }
        else
        {
            alert(datas.msg,1500);
            if(err!=null&&typeof err=='function')err(datas);
        }
        if(datas.url!=null&&datas.url!='')
        {
            location.href=datas.url;
        }
        // setTimeout(function(){
        //
        // },2000);

    },'json');
    return false;
}
function ajaxFormSend(obj,suc,err)
{
    customer_loading();
    var action=$(obj).attr('action');
    if(action==null)return false;
    $.post(action,$(obj).serialize(),function(datas){
        customer_close();
        if(datas.code==1)
        {

            if(suc!=null&&typeof suc=='function')suc(datas);
        } else {
            alert(datas.msg,1500);
            if(err!=null&&typeof err=='function')err(datas);
        }

        setTimeout(function(){
            customer_close();
            if(datas.url!=null&&datas.url!='')
            {

                location.href=datas.url;
            }
        },2000);

    },'json');
    return false;
}