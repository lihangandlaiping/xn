<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
/**
 * 后台权限验证
 */
function adminAouth($roleid, $menuid)
{
    $rules = \My\MasterModel::inIt('user_role')->field('rules')->getOne(array('roleid' => $roleid));
    $rules = explode(',', $rules['rules'] ?: '');
    if (in_array($menuid, $rules)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 异步请求
 */
function notfy_function($get)
{
    $fp = fsockopen("localhost", 80, $errno, $errstr, 30);
    if (!$fp) {
        //echo $errstr;exit;
    } else {
        stream_set_blocking($fp, 0);//开启非阻塞模式
        $out = "GET http://" . $get . " HTTP/1.1\r\n";
        $out .= "Host: localhost\r\n";
        $out .= "Connection: Close\r\n\r\n";

        $s = fwrite($fp, $out);
        /*忽略执行结果
        while (!feof($fp)) {
            echo fgets($fp, 128);
        }*/
        usleep(1000);//针对linux
        fclose($fp);
    }
}

/**
 * 删除文件夹及下面的文件
 * @param $dirname
 * @return bool
 */
function rmdirr($dirname)
{

    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }
    $dir->close();
    return rmdir($dirname);
}

/**
 * @param $list
 * @param int $level
 */
function get_menu_select($list, $val = 0, $level = 0)
{
    foreach ($list as $key => $row) {
        $kg = '';
        for ($i = 0; $i < $level; $i++) {
            $kg .= '&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        if ($kg != '') $kg = $kg . '|-';
        $is_selected = $row['id'] == $val ? 'selected=selected' : '';
        $html = "<option valusd='{$row['title']}' {$is_selected} value='{$row['id']}'>{$kg}{$row['title']}</option>";
        echo $html;

        $newlist = \My\MasterModel::inIt('menu')->getListData(array('pid' => $row['id'], 'hide' => 1));
        if ($list) get_menu_select($newlist, $val, $level + 1);
    }
}

/**
 * 创建一个模块
 * @param $dirname 模块路径
 * @return bool
 */
function create_module($dirname)
{
    if (file_exists($dirname)) return false;
    if (@mkdir($dirname, 0777)) {
        file_put_contents($dirname . '/common.php', '<?php ');
        file_put_contents($dirname . '/config.php', '<?php return [];');
        $data['Controller'] = $dirname . '/controller';
        $data['Model'] = $dirname . '/model';
        $data['View'] = $dirname . '/view';
        foreach ($data as $key => $row) {
            if (mkdir($row, 0777)) {
                $newdir = $row . '/index.html';
                file_put_contents($newdir, '<html></html>');
            } else {
                @unlink($dirname);
                return false;
            }
        }
        return true;
    }
    return false;
}

/**
 * 获取 字段类型
 * @param string $key
 * @return array
 */
function getFieldType($key = '')
{
    $arr = array(
        'string' => 'varchar(255) NOT NULL',
        'textarea' => 'text NOT NULL',
        'password' => 'char(32) NOT NULL',
        'editor' => 'text NOT NULL',
        'num' => 'int(10) UNSIGNED NOT NULL',
        'money' => 'decimal(10,2)',
        'date' => 'int(10) NOT NULL',
        'datetime' => 'int(10) NOT NULL',
        'bool' => 'tinyint(2) NOT NULL',
        'select' => 'char(50) NOT NULL',
        'linkage' => 'varchar(100) NOT NULL',
        'radio' => 'char(10) NOT NULL',
        'checkbox' => 'varchar(100)',
        'thumb' => 'varchar(100) NOT NULL',
        'images' => 'mediumtext',
        'attach' => 'varchar(255) NOT NULL',
        'attachs' => 'mediumtext',
        'tablefield' => 'varchar(255) NOT NULL',

    );
    if ($key != '') return $arr[$key];
    else return $arr;
}

/**
 * 获取表 的关联查询对象
 * @param $name
 * @return mixed
 */
function tableRelation($name)
{
    $list = \My\MasterModel::inIt('field f')->field('f.*')->joinData(array(array('model m', 'm.id=f.model_id', 'left')))->getListData(array('m.name' => $name, 'f.type' => 'tablefield'));
    $join = array();
    $tablearr = array();
    foreach ($list as $row) {
        if (!$row['extra']) continue;
        $extra = array_filter(preg_split('/[\r\n]+/s', $row['extra']));
        $newarray = array();
        foreach ($extra as $r) {
            $tmp = explode(':', $r);
            if (isset($tmp[1]))
                $newarray[trim($tmp[0])] = trim($tmp[1]);
        }
        if (!isset($newarray['db_table'])) continue;
        if (isset($tablearr[$newarray['db_table']])) {
            $tablearr[$newarray['db_table']] = $tablearr[$newarray['db_table']] + 1;
        } else {
            $tablearr[$newarray['db_table']] = 1;
        }
        $join[] = array("{$newarray['db_table']} {$newarray['db_table']}", "{$name}.{$row['field']}={$newarray['db_table']}.{$newarray['primary_key']}", 'left');
    }
    return $join;
}

/**
 * 获取参数验证类型
 * @param string $key
 * @return array
 */
function getValidType($key = '')
{
    $arr = array(
        '1' => '',
        '2' => '',
        '3' => '',
        '4' => '',
        '5' => ''
    );
    if ($key != '') return $arr[$key];
    else return $arr;
}

/**
 * 生成模型文件
 * @param $mudel
 * @param $model
 */
function createControllerView($mudel, $model)
{
    $model2 = $model;
    $model = explode('_', $model);
    $model = strtolower(implode('', $model));
    $model = strtoupper(substr($model, 0, 1)) . substr($model, 1);

    $app_path = str_replace('\\', '/', APP_PATH);
    $filepath1 = $app_path . "{$mudel}/controller/{$model}_admin.php";
    $filepath2 = $app_path . "{$mudel}/view/" . strtolower($model . '_admin') . "/index.html";
    $filepath3 = $app_path . "{$mudel}/view/" . strtolower($model . '_admin') . "/edit.html";
    $filepath4 = $app_path . "{$mudel}/model/{$model}.php";
    $homefilepath1 = $app_path . "{$mudel}/controller/{$model}_home.php";
    $homefilepath2 = $app_path . "{$mudel}/view/" . strtolower($model . '_home') . "/index.html";
    $homefilepath3 = $app_path . "{$mudel}/view/" . strtolower($model . '_home') . "/details.html";
    @mkdir($app_path . "{$mudel}/view/" . strtolower($model . '_Admin') . "/", 0777);
    @mkdir($app_path . "{$mudel}/view/" . strtolower($model . '_home') . "/", 0777);

    $base_controller = \My\MasterModel::inIt('config')->field('content')->getOne(array('name' => 'base_controller'));
    $base_model = \My\MasterModel::inIt('config')->field('content')->getOne(array('name' => 'base_model'));
    $controller = "<?php namespace app\\{$mudel}\\controller;

use My\MasterModel;

use Admin\AdminController;
use app\admin\model\Form;

class {$model}Admin extends AdminController
{
    function __construct()
    {
        parent::__construct();
        " . '$' . "this->model_name = '{$model2}';
        config('parent_temple', 'Admin/Index/base');
    }
    {$base_controller['content']}
}

?>";
    $models = "<?php namespace app\\{$mudel}\\model;

use My\MasterModel;

class {$model} extends MasterModel
{
    function __construct()
    {
        parent::__construct('{$model2}');
    }
    {$base_model['content']}
}

?>";
    $indexd = \My\MasterModel::inIt('config')->field('content')->getOne(array('name' => 'base_view_index'));
    $editd = \My\MasterModel::inIt('config')->field('content')->getOne(array('name' => 'base_view_edit'));
    file_put_contents($filepath2, $indexd['content']);
    file_put_contents($filepath3, $editd['content']);
    file_put_contents($filepath1, $controller);
    file_put_contents($filepath4, $models);
    //前台
    $homes = \My\MasterModel::inIt('config')->field('content')->getOne(array('name' => 'home_controller'));
    $homecontroller = "<?php
/**
 * {$model}
 * User: qls
 */
namespace app\\{$mudel}\\controller;
use Home\\HomeController;
use My\\MasterModel;

class {$model}Home extends HomeController
{
    protected " . '$model_name' . "='{$model2}';
    function __construct()
    {
        parent::__construct();
        config('parent_temple', '');
    }
    {$homes['content']}

}";
    file_put_contents($homefilepath1, $homecontroller);
    file_put_contents($homefilepath2, '');
    file_put_contents($homefilepath3, '');
}

/*
$mobile   号码。字符串或者数组
$content  内容
$time   定时发送 [年-月-日 时:分:秒]
$cell   扩展号
@return array
*/
function sendSMS($mobile, $content, $time = '', $Cell = '')
{
    $back_array = array(
        '0' => '发送成功',
        '–1' => '账号未注册',
        '–2' => '其他错误',
        '–3' => '帐号或密码错误',
        '–5' => '余额不足，请充值',
        '–6' => '定时发送时间不是有效的时间格式',
        '-7' => '提交信息末尾未签名，请添加中文的企业签名【】',
        '–8' => '发送内容需在1到300字之间',
        '-9' => '发送号码为空',
        '-10' => '定时时间不能小于系统当前时间',
        '-100' => '限制IP访问',
    );
    $http = 'http://sdk.mobilesell.cn/ws/BatchSend.aspx';  //短信接口
    $CorpID = 'YANYU005443'; //用户名
    $Pwd = 'Chongwuwangguo@2016'; //用户明文密码
    $content = iconv('UTF-8', 'GB2312', $content);
    if (!empty($time)) $time = date("YmdHis", strtotime($time));
    if (is_array($mobile)) $mobile = implode(",", array_unique($mobile));
    $gateway = "http://sdk.mobilesell.cn/ws/batchSend.aspx?CorpID={$CorpID}&Pwd={$Pwd}&Mobile={$mobile}&Content={$content}&Cell={$Cell}&SendTime=";
    $result = file_get_contents($gateway);
    if ($result === "0" || $result == 1) {
        return array('result' => 1, 'msg' => $back_array[0]);
    } else {
        return array('result' => $result, 'msg' => $back_array[$result]);
    }
}

/**
 * 产生随机字符串
 *
 * @param    int $length 输出长度
 * @param    string $chars 可选的 ，默认为 0123456789
 * @return   string     字符串
 */
function random($length = 32, $chars = '0123456789abcdefghijklmnopqrstuvwxyz')
{
    $hash = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}


function checkmobile($mobile)
{

    if (!preg_match("/1[34578]{1}\d{9}$/", $mobile) || strlen($mobile) != 11) {
        return false;
    } else {
        return true;
    }

}

function checkemail($inAddress)
{
    return (preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/", $inAddress));
}

function checkjson($json)
{
    if (json_decode($json, true)) {
        return true;
    } else return false;
}