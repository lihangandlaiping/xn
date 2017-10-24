<?php
/**
 * 后台配置管理
 */
namespace app\admin\controller;
use app\admin\model\Tabelaction;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
use think\Loader;

class Config extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='config';
        config('parent_temple','Admin/Index/base');
    }
    function index($module_id='',$name='',$title='')
    {
        $where=array('is_show'=>1);
        if($module_id!='')$where['m1.module_id']=$module_id;
        if($name!='')$where['m1.name']=array('like','%'.$name.'%');
        if($title!='')$where['m1.title']=array('like','%'.$title.'%');
        $list=$this->getListData( $this->model_name.' m1',$where,'m1.*,m.name as module_name','','',array(array('module m','m.id=m1.module_id','left')));
        $module=MasterModel::inIt('module')->getListData(array('is_setup'=>1));
        $this->assign('module',$module);
        return view('index',array('list'=>$list));
    }

    /**
     * 编辑
     */
    function edit($id='',$action='add')
    {
        if(request()->isPost())
        {
            $data=input('post.data');
            if($action=='edit')
            {
                $id=input('post.id','');
                $line=MasterModel::inIt($this->model_name)->updateData($data,array('id'=>$id));
                if($line){
                    $this->success('修改成功',url('index'));
                }
                else $this->error('修改失败');
                return false;
            }
                $id= MasterModel::inIt($this->model_name)->insertData($data);
                if($id){
                    $this->success('添加成功',url('index'));
                }
                else $this->error('添加失败');


        }
        else
        {
            $_GET['action']=$action;

            if($action=='edit')
            {
                $info=MasterModel::inIt($this->model_name.' m1')->getOne(array('m1.id'=>$id));
                $this->assign('info',$info);
            }
            $module=MasterModel::inIt('module')->getListData(array('is_setup'=>1));
            $this->assign('module',$module);
            return view('edit');
        }
    }




}
