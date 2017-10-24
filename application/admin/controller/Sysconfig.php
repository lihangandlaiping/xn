<?php
/**
 *  系统配置管理
 */
namespace app\admin\controller;
use app\admin\model\Tabelaction;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
use think\Loader;

class Sysconfig extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='config';
        config('parent_temple','Admin/Index/base');
    }

    /**
     * 编辑
     */
    function edit()
    {
        if(request()->isPost())
        {
            $data=input('post.data');
            foreach($data as $key=>$val)
            {
                MasterModel::inIt($this->model_name)->updateData(array('content'=>$val),array('name'=>$key));
            }
            $this->success('编辑成功',url('edit'));
        }
        else
        {
            $info=MasterModel::inIt($this->model_name.' m1')->getListData(array('m1.name'=>array('in','admin_action_log,web_seo_title,web_seo_keyword,web_seo_describe,web_url,admin_footer,home_page_size,admin_page_size,backup_size,push_appKey,push_masterSecret')));
            $newinfo=array();
            foreach($info as $row)
            {
                $newinfo[$row['name']]=$row['content'];
            }
            $this->assign('info',$newinfo);
            return view('edit');
        }
    }




}
