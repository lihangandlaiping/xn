<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/19
 * Time: 13:54
 */
namespace app\admin\model;
use My\MasterModel;

class Form
{
    protected $is_initedit = false;
    protected $is_initTime = false;
    protected $is_initselect = false;
    protected $is_checkbox=false;

    function __construct()
    {

    }

    /**
     *引入编辑器
     */
    function importEdit()
    {

        $url = __ROOT__ . '/public/ueditor/';
        $html = '
    <script>window.UEDITOR_HOME_URL="' . $url . '"; var  filePathDir="/public/ueditor/";</script>
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
     *
     */
    function importSelect()
    {
        $url = __ROOT__ . '/public/admin/';
        $html = '
     <link rel="stylesheet" href="' . $url . 'css/plugins/chosen/chosen.css"/>
    <script src="' . $url . 'js/plugins/chosen/chosen.jquery.js"></script>';
        $this->is_initselect = true;
        return $html;
    }

    /**
     * 引入时间插件
     */
    function importTime()
    {
        $url = __ROOT__ . '/public/admin/';
        $html = '
       <link rel="stylesheet" href="' . $url . 'css/bootstrap-datetimepicker.min.css"/>
    <script src="' . $url . 'js/bootstrap-datetimepicker.js" type="text/javascript"></script>';
        $this->is_initTime = true;
        return $html;
    }

    /**
     * 实例化编辑器
     * @param $name
     * @return string
     */
    function initEdit($name, $content = '')
    {
        $html = '';
        if (!$this->is_initedit) $html = $this->importEdit();
        $html .= '<textarea class="form-control" aria-required="true" id="' . $name . '_id" name="' . $name . '">' . $content . '</textarea>
<script type="text/javascript">
    var ' . $name . '_1 = UE.getEditor("' . $name . '_id", options);

</script>';
        return $html;
    }

    /**
     * 实例化图片上传
     * @param $name
     * @param string $src
     * @param int $type
     * @return string
     */
    function initImgUpload($name, $src = '', $type = 1)
    {
        $html = '';
        if (!$this->is_initedit) $html .= $this->importEdit();
        //$type==2多图
        $str1 = $type == 1 ? " uploadImgCallBack{$name}(arg[0]);" : " uploadImgCallBack{$name}(arg);";
        $str2 = '';
        if ($src != '') {
            $src = explode(',', $src);
            if (count($src) > 1) {
                foreach ($src as $row) {
                    $str2 .= "<img src='".__ROOT__."{$row}' style='width: 120px;'><input type='hidden' value='{$row}' name='{$name}[]'>";
                }
            } else {
                $str2 .= "<img src='".__ROOT__."{$src[0]}' style='width: 120px;'><input type='hidden' name='{$name}' value='{$src[0]}'>";
            }


        }
        $str3 = $type == 2 ? 'function uploadImgCallBack' . $name . '(src){
console.log(src);
var leng=src.length;
        var html="";
        for(var i=0;i<leng;i++)
        {
        html+="<img src=\'"+src[i].src+"\' style=\'width:120px;\'/><input type=\'hidden\' value=\'"+src[i].data+"\' name=\'' . $name . '[]\'/>";
        }

        $("#upload_imgs' . $name . '").html(html);

    }' : 'function uploadImgCallBack' . $name . '(src){

        var html="";
       html+="<img src=\'"+src.src+"\' style=\'width:120px;\'/><input type=\'hidden\'value=\'"+src.data+"\' name=\'' . $name . '\'/>";

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

    /**
     * 实例化文件上传
     * @param $name
     * @param string $src
     * @param int $type
     * @return string
     */
    function initFileUpload($name, $src = '', $type = 1)
    {
        $html = '';
        if (!$this->is_initedit) $html .= $this->importEdit();
        //$type==2多图
        $str1 = $type == 1 ? " uploadFileCallBack{$name}(arg[0]);" : " uploadFileCallBack{$name}(arg);";
        $str2 = '';
        if ($src != '') {
            $src = explode(',', $src);
            if (count($src) > 1) {
                foreach ($src as $row) {
                    $str2 .= "<span><i class='fa fa-sticky-note-o'></i>{$row}</span><input type='hidden' value='{$row}' name='{$name}[]'>";
                }
            } else {
                $str2 .= "<span><i class='fa fa-sticky-note-o'></i>{$src[0]}</span><input type='hidden' name='{$name}' value='{$src[0]}'>";
            }


        }
        $str3 = $type == 2 ? 'function uploadFileCallBack' . $name . '(src){
console.log(src);
var leng=src.length;
        var html="";
        for(var i=0;i<leng;i++)
        {
        html+="<span><i class=\'fa fa-sticky-note-o\'></i>\'"+src[i].original+"\'</span><input type=\'hidden\' value=\'"+src[i].url+"\' name=\'' . $name . '[]\'/>";
        }

        $("#upload_file' . $name . '").html(html);

    }' : 'function uploadFileCallBack' . $name . '(src){
console.log(src);
        var html="";
       html+="<span><i class=\'fa fa-sticky-note-o\'></i>\'"+src.original+"\'</span><input type=\'hidden\'value=\'"+src.url+"\' name=\'' . $name . '\'/>";

        $("#upload_file' . $name . '").html(html);

    }';
        $html .= '
    <div id="upload_file' . $name . '">' . $str2 . '</div>
    <button class="btn btn-outline btn-success  dim" onclick="upFiles_' . $name . '()" type="button"><i class="fa fa-upload"></i>
                    </button><span class="help-block m-b-none">（文件上传）</span>
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


        //侦听文件上传，取上传文件列表中第一个上传的文件的路径
        _editor' . $name . '.addListener("afterUpfile", function (t, arg) {
             ' .
            $str1
            . '
        })
    });

    //弹出文件上传的对话框
    function upFiles_' . $name . '() {
        var myFiles = _editor' . $name . '.getDialog("attachment");
        myFiles.open();
    }
    //图片
    ' . $str3 . '
</script>';
        return $html;
    }

    /**
     * 复选框
     * @param $name
     * @param array $val
     * @param string $value
     * @return string
     */
    function checkbox($name, $val = array(array()), $value = '')
    {
        $value=explode(',',$value);
        $html = '';
        foreach ($val as $row) {
            if (!$row[0] || !$row[1]) continue;
            $ischecked = in_array($row[0],$value) ? 'checked=checked' : '';
            $ischeckclass = in_array($row[0],$value) ? 'checked' : '';
            $html .= '<label class="checkbox-inline i-checks">
                                        <div class="icheckbox_square-green " style="position: relative;"><input  ' . $ischecked . ' type="checkbox" name="' . $name . '[]" value="' . $row[0] . '" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; border: 0px; opacity: 0; background: rgb(255, 255, 255);"></ins></div>' . $row[1] . '</label>';
        }
        if(!$this->is_checkbox)$html.='<script>$(function(){$(".i-checks").iCheck({checkboxClass:"icheckbox_square-green",radioClass:"iradio_square-green"})});</script>';
        return $html;

    }

    /**
     * 单选
     * @param $name
     * @param array $val
     * @param string $value
     * @return string
     */
    function redio($name, $val = array(array()), $value = '')
    {
        $html = '';
        foreach ($val as $row) {
            if (!$row[0] || !$row[1]) continue;
            $ischecked = $value == $row[0] ? 'checked=checked' : '';
            $ischeckclass = $value == $row[0] ? 'checked' : '';
            $html .= '<label class="i-checks">
            <div class="iradio_square-green " style="position: relative;"><input type="radio" ' . $ischecked . ' value="' . $row[0] . '" name="' . $name . '" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; border: 0px; opacity: 0; background: rgb(255, 255, 255);"></ins></div> <i></i> ' . $row[1] . '</label>';
        }
        if(!$this->is_checkbox)$html.='<script>$(function(){$(".i-checks").iCheck({checkboxClass:"icheckbox_square-green",radioClass:"iradio_square-green"})});</script>';
        return $html;
    }

    /**
     * 下拉选择
     * @param $name
     * @param array $val
     * @param string $value
     * @return int|string
     */
    function selectform($name, $val = array(array()), $value = '',$iswidth='')
    {
        $html = '';
        if (!$this->is_initselect) $html .= $this->importSelect();
        $html .= '<div class="input-group">
                                        <select name="'.$name.'" data-placeholder="请选择..." class="chosen-select"';
        if($iswidth=='')$html.=' style="width:350px;"';
        $html.=' tabindex="2">
                                     <option value="">请选择</option>
                                   %s
                                </select>
                                    </div>';
        if($iswidth)
        {

        }
        $option = '';
        foreach ($val as $key => $row) {
            if (!$row[0] || !$row[1]) continue;
            $is_check=$value==$row[0]?'selected=selected':'';
            $option .= '<option '.$is_check.' value="' . $row[0] . '" hassubinfo="true">' . $row[1] . '</option>';
        }
        $html = sprintf($html, $option);
        // var_dump($html);exit;
        return $html;
    }

    /**
     *  输入框
     * @param $fieldList
     */
    function inputform($names, $value = '')
    {
        $html = '<input type="text" class="form-control" name="' . $names . '" value="' . $value . '">';
        return $html;
    }

    /**
     *  输入框密码
     * @param $fieldList
     */
    function inputpasswordform($name, $value = '')
    {
        $html = '<input type="password" class="form-control" name="' . $name . '" value="' . $value . '">';
        return $html;
    }

    /**
     * 文本框
     * @param $name
     * @param string $value
     * @return string
     */
    function textearaform($name, $value = '')
    {
        $html = '<textarea name="' . $name . '" class="form-control" required="" aria-required="true">' . $value . '</textarea>';
        return $html;
    }

    /**
     * 时间选择
     * @param $name
     * @param string $value
     * @return string
     */
    function timeform($name, $value = '',$is_earch=0,$type=1)
    {
        $date=$type==1?"{  weekStart: 1,
        todayBtn:  1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		forceParse: 0,
        showMeridian: 1}":"{format: 'yyyy-mm-dd',
            weekStart: 1,
            todayBtn:  1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 2,
            forceParse: 0}";
        $html = '';
        if (!$this->is_initTime) $html = $this->importTime();
        if($is_earch==0)
        {
            $str = " $('#{$name}').datetimepicker({$date});";
            $html .= '<input style="width:200px;" type="text" id="' . $name . '" class="form-control" name="' . $name . '" value="' . $value . '"> <script>$(function(){%s})</script>';
            return sprintf($html, $str);
        }
        else
        {
            $value=explode(',',$value);
            $str = " $('#{$name}start').datetimepicker({$date});$('#{$name}end').datetimepicker({$date});";
            $html .= '<input  type="text" id="' . $name . 'start" class="form-control" name="' . $name . 'start" value="' . ($value[0]?:'') . '"> <span class="input-group-addon">到</span><input  type="text" id="' . $name . 'end" class="form-control" name="' . $name . 'end" value="' . ($value[1]?:'' ). '"><script>$(function(){%s})</script>';
            return sprintf($html, $str);
        }

    }

    /**
     * 联动菜单
     * @param $row
     * @param $vals
     * @param $value
     * @return string
     */
    function linkPage($row,$vals,$value)
    {
        if(is_array($value))$value=implode(',',$value);
        $html='<style>#'.$row['field'].' select{width:150px; float:left;}</style><div id="'.$row['field'].'"></div><script>$(function(){
qls.cascade("'.$row['field'].'","'.$vals['table'].'","'.$vals['relation_field'].'",0,
{"name":"'.$vals['name'].'","id":"'.$vals['id'].'"},"'.$value.'");
                    })</script>';
        return $html;
    }

    /**
     * 生成表单
     * @param $fieldList 字段培训
     * @param array $value 字段数据
     * @param string $field 级联关系字段
     * @param array $hide 隐藏字段
     * @return string
     */
    function createFrom($fieldList, $value = array(),$field='',$hide=array())
    {
        $html = '';
        foreach ($fieldList as $row) {
            if($field==$row['field'])
            {
                $html.='<input type="hidden" name="'.$row['field'].'" value="'.input($row['field'],0).'">';continue;
            }
            if(in_array($row['field'],$hide))continue;
            $str = '<div class="form-group">
                                        <label class="col-sm-1 control-label">' . $row['name'] . '</label>
                                        <div class="col-sm-6">%s<span class="help-block m-b-none">'.$row['remark'].'</span></div></div>';
            $value[$row['field']] = isset($value[$row['field']]) ?$value[$row['field']]: '';
            switch ($row['type']) {
                case 'string':
                    $this->inputform($row['field'], $value[$row['field']] ?: '');
                    $html .= sprintf($str, $this->inputform($row['field'], $value[$row['field']] ?: ''));
                    break;
                case 'textarea':
                    $html .= sprintf($str, $this->textearaform($row['field'], $value[$row['field']] ?: ''));
                    break;
                case 'password':
                    $html .= sprintf($str, $this->inputpasswordform($row['field'], $value[$row['field']] ?: ''));
                    break;
                case 'editor':
                    $html .= sprintf($str, $this->initEdit($row['field'], $value[$row['field']] ?: ''));
                    break;
                case 'num':
                    $html .= sprintf($str, $this->inputform($row['field'], $value[$row['field']] ?: ''));
                    break;
                case 'money':
                    $html .= sprintf($str, $this->inputform($row['field'], $value[$row['field']] ?: ''));
                    break;
                case 'date':
                    $value[$row['field']]=$value[$row['field']]?date('Y-m-d',$value[$row['field']]):'';
                    $html .= sprintf($str, $this->timeform($row['field'], $value[$row['field']] ?: '',0,2));
                    break;
                case 'datetime':
                    $value[$row['field']]=$value[$row['field']]?date('Y-m-d',$value[$row['field']]):'';
                    $html .= sprintf($str, $this->timeform($row['field'], $value[$row['field']] ?: ''));
                    break;
                case 'bool':
                    $vals = array_filter(preg_split('/[\r\n]+/s', $row['extra']));
                    foreach ($vals as &$v) {
                        $v = explode(':', $v);
                    }

                    $html .= sprintf($str, $this->selectform($row['field'], $vals, $value[$row['field']] ?: $row['value']));
                    break;
                case 'select':
                    $vals = array_filter(preg_split('/[\r\n]+/s', $row['extra']));
                    foreach ($vals as &$v) {
                        $v = explode(':', $v);
                    }
                    $html .= sprintf($str, $this->selectform($row['field'], $vals, $value[$row['field']] ?: $row['value']));
                    break;
                case 'linkage':
                    $valss = array_filter(preg_split('/[\r\n]+/s', $row['extra']));
                    $vals=array();
                    foreach ($valss as $v) {
                        $v = explode(':', $v);
                        $vals[trim($v[0])]=trim($v[1]);
                    }
                    $hh=$this->linkPage($row,$vals,$value[$row['field']]);
                    $html.=sprintf($str,$hh);
                    break;
                case 'radio':
                    $vals = array_filter(preg_split('/[\r\n]+/s', $row['extra']));
                    foreach ($vals as &$v) {
                        $v = explode(':', $v);
                    }
                    $html .= sprintf($str, $this->redio($row['field'], $vals, $value[$row['field']] ?: $row['value']));
                    break;
                case 'checkbox':
                    $vals = array_filter(preg_split('/[\r\n]+/s', $row['extra']));
                    foreach ($vals as &$v) {
                        $v = explode(':', $v);
                    }
                    $html .= sprintf($str, $this->checkbox($row['field'], $vals, $value[$row['field']] ?: ''));
                    break;
                case 'thumb':
                    $html .= sprintf($str, $this->initImgUpload($row['field'], $value[$row['field']] ?: '', 1));
                    break;
                case 'images':
                    $html .= sprintf($str, $this->initImgUpload($row['field'], $value[$row['field']] ?: '', 2));
                    break;
                case 'attach':
                    $html .= sprintf($str, $this->initFileUpload($row['field'], $value[$row['field']] ?: ''));
                    break;
                case 'attachs':
                    $html .= sprintf($str, $this->initFileUpload($row['field'], $value[$row['field']] ?: '',2));
                    break;
                case 'tablefield':
                    $newarray = array();
                    $vals = array_filter(preg_split('/[\r\n]+/s', $row['extra']));
                    foreach ($vals as &$v) {
                        $v = explode(':', $v);
                        if(isset($v[0])&&isset($v[1]))$newarray[trim($v[0])] = $v[1];
                    }
                    $list = MasterModel::inIt($newarray['db_table'])->field("{$newarray['primary_key']},{$newarray['search_field']}")->getListData();
                    $valus = array();
                    foreach ($list as $rows) {
                        $valus[] = array($rows[trim($newarray['primary_key'])], $rows[$newarray['search_field']]);
                    }
                    $html .= sprintf($str, $this->selectform($row['field'], $valus, $value[$row['field']] ?: ''));
                    break;

            }
        }
        return $html;
    }


}