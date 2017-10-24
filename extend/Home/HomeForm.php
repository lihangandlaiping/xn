<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/29
 * Time: 10:36
 */
namespace Home;
class HomeForm
{
    function __construct()
    {
        parent::__construct();

    }
    /**
     *引入编辑器
     */
    private function importEdit()
    {
        $url = __ROOT__ . '/public/ueditor/';
        $html = '
    <script>window.UEDITOR_HOME_URL="' . $url . '";</script>
    <script src="' . $url . 'ueditor.config.js" type="text/javascript"></script>
    <script src="' . $url . 'ueditor.all.js" type="text/javascript"></script>
    <script>
     var param="paremater=qls";
    var options = {
        initialFrameWidth:"100%",
        initialFrameHeight: 400,
        imageUrl:window.UEDITOR_CONFIG.UEDITOR_HOME_URL+"php/imageUp.php?"+param,
        fileUrl:window.UEDITOR_CONFIG.UEDITOR_HOME_URL+"php/fileUp.php?"+param,
        imageManagerUrl:window.UEDITOR_CONFIG.UEDITOR_HOME_URL + "php/imageManager.php?"+param

    };</script>
    ';
        $this->is_initedit = true;
        return $html;
    }

    /**
     * 实例化图片上传
     * @param $name
     * @param string $src
     * @param int $type
     * @return string
     */
    function initImgUpload($name, $src = '', $type = 1,$function='')
    {
        $html = '';
        if (!$this->is_initedit) $html .= $this->importEdit();
        //$type==2多图
        $str1 = $type == 1 ? " uploadImgCallBack{$name}(arg[0].src);" : " uploadImgCallBack{$name}(arg);";
        $str2 = '';
        if ($src != '') {
            $src = explode(',', $src);
            if (count($src) > 1) {
                foreach ($src as $row) {
                    $str2 .= "<img src='{$row}' style='width: 120px;'><input type='hidden' value='{$row}' name='{$name}[]'>";
                }
            } else {
                $str2 .= "<img src='{$src[0]}' style='width: 120px;'><input type='hidden' name='{$name}' value='{$src[0]}'>";
            }


        }
        $str3 = $type == 2 ? 'function uploadImgCallBack' . $name . '(src){
'.$function.'
    }' : 'function uploadImgCallBack' . $name . '(src){

        var html="";
       html+="<img src=\'"+src+"\' style=\'width:120px;\'/><input type=\'hidden\'value=\'"+src+"\' name=\'' . $name . '\'/>";

        $("#upload_imgs' . $name . '").html(html);

    }';
        $html .= '
    <div id="upload_imgs' . $name . '">' . $str2 . '</div>
    <button class="btn btn-outline btn-success  dim" onclick="upImage_' . $name . '()" type="button"><i class="fa fa-upload"></i>
                    </button><span class="help-block m-b-none">（图片上传）</span>
    <script type="text/javascript">
    var param="paremater=qls";
    var options = {
        initialFrameWidth:"100%",
        initialFrameHeight: 300,
        imageUrl:window.UEDITOR_CONFIG.UEDITOR_HOME_URL+"php/imageUp.php?"+param,
        fileUrl:window.UEDITOR_CONFIG.UEDITOR_HOME_URL+"php/fileUp.php?"+param,
        imageManagerUrl:window.UEDITOR_CONFIG.UEDITOR_HOME_URL + "php/imageManager.php?"+param

    };
</script>
    <script type="text/plain" id="upload_ue_' . $name . '" style="display: none;"></script>
<script type="text/javascript">

    //重新实例化一个编辑器，防止在上面的editor编辑器中显示上传的图片或者文件
    var _editor' . $name . ' = UE.getEditor("upload_ue_' . $name . '",options);
    console.log(_editor' . $name . ');
    _editor' . $name . '.ready(function () {
        //设置编辑器不可用
        _editor' . $name . '.setDisabled();
        //隐藏编辑器，因为不会用到这个编辑器实例，所以要隐藏
        _editor' . $name . '.hide();
        //侦听图片上传
        _editor' . $name . '.addListener("beforeInsertImage", function (t, arg) {
            //将地址赋值给相应的input,只去第一张图片的路径
            //$("#thumb").attr("value", arg[0].src);
            //$("#thumb").addClass("hide");
            //图片预览
            //$("#thumb_img").attr("src", arg[0].src);
            //$("#thumb_img").show();
            ' .
            $str1
            . ' });

        //侦听文件上传，取上传文件列表中第一个上传的文件的路径
        /*_editor.addListener("afterUpfile", function (t, arg) {
            $("#file").attr("value", _editor.options.filePath + arg[0].url);
        })*/
    });
    //弹出图片上传的对话框
    function upImage_' . $name . '() {
        var myImage = _editor' . $name . '.getDialog("insertimage");
        myImage.open();
    }
    //弹出文件上传的对话框
    /*function upFiles() {
        var myFiles = _editor' . $name . '.getDialog("attachment");
        myFiles.open();
    }*/
    //图片
    ' . $str3 . '
</script>';
        return $html;
    }

}