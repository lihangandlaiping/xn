/**
 * Created by Administrator on 2016/9/7.
 */

$(document).on('click','.submitform',function(){
    var serialize=$(this).parents('form').serialize();
    var action=$(this).parents('form').attr('action');
    qls.loading();
    $.post(action,serialize,function(data){
        qls.close();
        if(data.code==1)
        {
            qls.success(data.msg,data.url);
        }
        else
        {
            qls.error(data.msg);
        }
    },'json');
});

$(document).on('click','.deleteall',function(){
   var length= $('input[type="checkbox"].i-checks:checked').length;
    var ids='';
    for(var i=0;i<length;i++)
    {
        if(ids!='')ids+=',';
        ids+=$('input[type="checkbox"].i-checks:checked').eq(i).val();
    }
    qls.waring(function(){
        qls.loading();
        $.get(jsdeletedata,{id:ids},function(data){
            qls.close();
            if(data.code==1)
            {
                swal(data.msg, "您已经永久删除了信息。", "success");
                setTimeout(function(){sweetAlert.close();
                location.href=data.url;
                },2000);
            }
            else
            {
                swal(data.msg, "您执行的删除操作失败。", "error");
                setTimeout(function(){sweetAlert.close();},2000);
            }
        },'json');
    });

});

function closeDilog(url,fun1,type)
{
            $('#modal-dialog .modal-dialog').animate({'margin-top':'-233px'},400,function(){
                $('#modal-dialog').remove();
                if(qls_location_url!='')
                {
                    location.href=qls_location_url;
                }
                else if(qls_location_url==''&&type==1)location.reload();
            })
}
function selectchange(obj,ids,tabelname,column)
{

    var leng=$('#'+ids+' select.form-control.m-b').length;
    var level=$(obj).attr('level');
    for(var i=0;i<leng;)
    {
       if($('#'+ids+' select.form-control.m-b').eq(i).attr('level')>level)
       {
           $('#'+ids+' select.form-control.m-b').eq(i).remove();
           continue;
       }
        i++;
    }

    var filed=$(obj).attr('field');
    $.post(jscascadeUrl,{model:tabelname,column:column,parentId:$(obj).val(),filed:JSON.parse(filed)},function(data){
        if(data.data!=null)
        {
            var htm='<select name="'+ids+'[]" onchange="selectchange(this,\''+ids+'\',\''+tabelname+'\',\''+column+'\')" field=\''+filed+'\' class="form-control m-b" level="'+(parseInt(level)+1)+'"><option value="">--请选择--</option>';
            $.each(data.data,function(i,n){
                htm+='<option  value="'+ n.id+'">'+ n.name+'</option>';
            });
            htm+='</select>';
            if(data.data.length>0)
            $(obj).after(htm);
        }

    },'json');

}

function sendGetAjax(url)
{
    qls.waring(function(){
        qls.loading();
        $.get(url,{},function(data){
            qls.close();
            if(data.code==1)
            {
                swal(data.msg, "你执行的操作已成功，数据已更新。", "success");
                setTimeout(function(){sweetAlert.close();
                    location.href=data.url;
                },2000);
            }
            else
            {
                swal(data.msg, "你执行的操作失败，数据未更新。", "error");
            }
        },'json');
    });

}
var qls_location_url='';
var qls={
  "success":function(msg,url,fun1,fun2){
      qls_location_url=url;
        var html='<div class="modal" id="modal-dialog" style="display:block; background:rgba(0,0,0,0.5)"> ' +
            '<div class="modal-dialog" style="margin-top: -233px;"> ' +
            '<div class="modal-content"> ' +
            '<div class="modal-header alert alert-success"> ' +
            '<h4 class="modal-title" id="myModalLabel">提示信息 </h4> ' +
            '</div> ' +
            '<div class="modal-body">  ' +
            '<div ><i style="font-size: 45px;color: #3c763d;" class="fa fa-smile-o"></i> <span style="position: relative;top: -11px;padding-left: 10px;font-size: 15px;">'+msg+'</span> </div></div> ' +
            '<div class="modal-footer"> ' +
            '<button type="button" class="btn btn-primary" onclick="closeDilog('+url+','+fun1+',1)" data-dismiss="modal">确认 ' +
            '</button> ' +
            '<button type="button" onclick="closeDilog('+url+','+fun2+',1)" class="btn btn-default">取消 </button> ' +
            '</div> ' +
            '</div> ' +
            '</div> ' +
            '</div> ';
      $('#modal-dialog').remove();
      $('body').append(html);
      $('#modal-dialog .modal-dialog').animate({'margin-top':'30px'},400,function(){
          setTimeout(function(){
              $('#modal-dialog .modal-dialog').animate({'margin-top':'-233px'},400,function(){
                  $('#modal-dialog').remove();
                  if(qls_location_url!='')
                  {

                      location.href=qls_location_url;
                  }
                  else if(qls_location_url=='')location.reload();
              })
          },1300);

      });

  }  ,
    "error":function(msg,url,fun1,fun2){
        qls_location_url=url!=null?url:'';
        var html='<div class="modal" id="modal-dialog" style="display:block; background:rgba(0,0,0,0.5)"> ' +
            '<div class="modal-dialog" style="margin-top: -233px;"> ' +
            '<div class="modal-content"> ' +
            '<div class="modal-header alert alert-danger"> ' +
            '<h4 class="modal-title" id="myModalLabel">提示信息 </h4> ' +
            '</div> ' +
            '<div class="modal-body">  ' +
            '<div ><i style="font-size: 45px;color: #a94442;" class="fa fa-smile-o"></i> <span style="position: relative;top: -11px;padding-left: 10px;font-size: 15px;">'+msg+'</span> </div></div> ' +
            '<div class="modal-footer"> ' +
            '<button type="button" class="btn btn-danger" onclick="closeDilog('+url+','+fun1+',2)" data-dismiss="modal">确认 ' +
            '</button> ' +
            '<button type="button" onclick="closeDilog('+url+','+fun2+',2)" class="btn btn-default">取消 </button> ' +
            '</div> ' +
            '</div> ' +
            '</div> ' +
            '</div> ';
        $('#modal-dialog').remove();
        $('body').append(html);
        $('#modal-dialog .modal-dialog').animate({'margin-top':'30px'},400,function(){
           setTimeout(function(){
               $('#modal-dialog .modal-dialog').animate({'margin-top':'-233px'},400,function(){
                   $('#modal-dialog').remove();
                   if(qls_location_url!='')
                   {

                       location.href=qls_location_url;
                   }

               })
           },1300);

        });

    },
    "waring":function(func){//警告
            swal({
                title: "您确定要执行该操作吗？",
                text: "数据操作后将无法还原，请谨慎操作！",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "执行",
                closeOnConfirm: false
            }, func);
    },
    "prompt":function(title,fun,type,val){
        parent.layer.prompt({
            title: title,
            value:val!=null?val:'',
            formType: type!=null?type:2 //prompt风格，支持1-2
        }, fun);
    },
    "confirm":function(title,fun1,fun2){
        var html='<div class="modal" id="modal-dialog" style="display:block; background:rgba(0,0,0,0.5)"></div>';
        $('#modal-dialog').remove();
        $('body').append(html);
        parent.layer.confirm(title, {
            btn: ['确认','取消'], //按钮
            shade: false //不显示遮罩
        }, function(){
           if(fun1!=null) fun1();

        }, function(){
            if(fun2!=null)fun2();

        });
    },
    "loading":function(){
        parent.layer.load(1, {
            shade: [0.5,'#fff'] //0.1透明度的白色背景
        });
    },
    "msg":function(title,icon){//icon 1成功 2失败 3疑问 4锁定
        parent.layer.msg(title, {icon: icon!=null?icon:1});
    },
    "open":function(title,html,wh){
        if(wh==null)
        {
            wh=['420px', '240px'];
        }
        parent.layer.open({
            title:title,
            type: 1,
            area: [wh[0], wh[1]], //宽高
            skin: 'layui-layer-demo', //样式类名
            closeBtn: true, //不显示关闭按钮
            shift: 2,
            shadeClose: true, //开启遮罩关闭
            content: html
        });
    },
    "close":function(){
        parent.layer.closeAll();
    },
    /**
     * 级联
     * @param 元素id
     * @param tabelname 表名
     * @param column 关联键名
     * @param parentId 上级id
     * @param filed 查询的字段对象{"name":"","id":""}
     */
    "cascade":function(slecteds,tabelname,column,parentId,filed,val,html,level)//qls.cascade('slecteds','area','parent_id',0,{"name":'area_name',"id":"area_id"},'15,16');
    {
        var vals=val!=null?val.split(','):[];
        var levels=level!=null?level:0;
        //if(vals.length<=leves+1)return html;
         var htmls=html!=null?html:'';
            var str=JSON.stringify(filed);

        var htm='<select name="'+slecteds+'[]" onchange="selectchange(this,\''+slecteds+'\',\''+tabelname+'\',\''+column+'\')" field=\''+str+'\' class="form-control m-b" level="'+levels+'"><option value="">--请选择--</option>';
        var pid='';
        $.post(jscascadeUrl,{model:tabelname,column:column,parentId:parentId,filed:filed},function(data){
                if(data.data!=null)
                {
                    $.each(data.data,function(i,n){
                        var ische='';
                        if(vals[levels]== n.id)
                        {
                            ische='selected=selected';
                            pid=n.id;
                        }
                        htm+='<option '+ische+' value="'+ n.id+'">'+ n.name+'</option>';
                    });
                    htm+='</select>';
                }
            htmls+=htm;
            if(vals.length>levels&&pid!='')
            {
                qls.cascade(slecteds,tabelname,column,pid,filed,val,htmls,levels+1);
            }
             if(data.data.length>0)
            {

              $('#'+slecteds).html(htmls);
            }
        },'json');
    }
};
