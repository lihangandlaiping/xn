
    <div class="col-sm-12">
        <div class="ibox float-e-margins">


            <div class="ibox-content timeline">
                <?php foreach($list as $row):?>
                <div class="timeline-item">
                    <div class="row">
                        <div class="col-xs-3 date">
                            <i class="fa fa-file-text"></i> <?php echo $row;?>
                            <br>
                            <!--<small class="text-navy">3小时前</small>-->
                        </div>
                        <div class="col-xs-7 content">
                            <p class="m-b-xs"><strong>模块：{$row}</strong>
                            </p>
                            <p> <?php echo in_array($row,$newmodule)?'模块名已存在不可安装':'待安装';?></p>
                            <?php if(!in_array($row,$newmodule)):?>
                            <a href="javascript:void(0);" onclick="restore('{$row}')" class="btn btn-success btn-rounded">安装
                                        </a>
                            <?php else:?>
                            <a href="javascript:void(0);"  class="btn btn-default btn-rounded">安装</a>
                            <?php endif;?>

                        </div>
                    </div>
                </div>
                <?php endforeach;?>
            </div>
        </div>
    </div>

    <script>

        function closemodal(str,num)
        {
            $('#'+str).remove();
            if(num>0)location.reload();
        }
        function restore(name)
        {
            var url='<?php echo url('installModule');?>?name='+name;
            closemodal('myModal5',0);
            showlogdel(url);
        }
        function showlogdel(url)
        {
            var html='<div class="modal inmodal fade in" id="myModal5" tabindex="-1" role="dialog" aria-hidden="true" style="display: block;    background-color: rgba(0,0,0,0.4);"> ' +
                    '<div class="modal-dialog modal-lg" style="margin-top: -369px;"> ' +
                    '<div class="modal-content"> ' +
                    '<div class="modal-header"> ' +
                    '<button type="button" class="close" data-dismiss="modal" onclick="closemodal('+"'"+'myModal5'+"'"+')"><span aria-hidden="true">×</span>' +
                    '<span class="sr-only">Close</span></button> ' +
                    '<h4 class="modal-title">正在安装模块...</h4> ' +
                    '</div><small class="font-bold"> ' +
                    '<div class="modal-body"> ' +
                    '<iframe frameborder="0" width="100%" height="100%" src="'+url+'"></iframe> ' +
                    '</div> ' +
                    '<div class="modal-footer"> ' +
                    '<button type="button" class="btn btn-white" onclick="closemodal('+"'"+'myModal5'+"',1"+')" data-dismiss="modal">关闭</button> ' +
                    '<!--<button type="button" class="btn btn-primary">保存</button>--> ' +
                    '</div> ' +
                    '</small></div><small class="font-bold"> ' +
                    '</small></div><small class="font-bold"> ' +
                    '</small></div>';
            $('body').append(html);
            $('#myModal5 .modal-dialog').animate({'margin-top':'30px'},200);
        }
    </script>