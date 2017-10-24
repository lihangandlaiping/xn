<?php
/**
 * 后台接口管理
 */
namespace app\admin\controller;
use app\admin\model\Tabelaction;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
use think\Loader;

class Test extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='test';
        config('parent_temple','Admin/Index/base');
    }





}
