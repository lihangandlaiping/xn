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

class Interfaceparemater extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='interface_paremater';
        config('parent_temple','Admin/Index/base');
    }
    function index($interface='',$name='',$title='')
    {
        $_GET['interface']=$interface;
        $where=array('m1.face_id'=>$interface);
        if($name!='')$where['name']=array('like','%'.$name.'%');
        if($title!='')$where['title']=array('like','%'.$title.'%');
        if($interface=='')$this->error('缺少接口标识');
        $list= $this->getListData($this->model_name.' m1',$where,'m1.*,m2.face_name,m2.face_title','',null,array(array('interface m2','m1.face_id=m2.id','left')));
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
            $interface=input('post.interface','');
            if($action=='edit')
            {
                $id=input('post.id','');
                $valid=MasterModel::inIt($this->model_name)->getCount(array('face_id'=>$interface,'name'=>$data['name'],'id'=>array('neq',$id)));
                if($valid>0)$this->error('该参数标识已存在');
                $line=MasterModel::inIt($this->model_name)->updateData($data,array('id'=>$id));
                if($line){
                    $this->success('修改成功',url('index',array('interface'=>$interface)));
                }
                else $this->error('修改失败');
                return false;
            }
            $valid=MasterModel::inIt($this->model_name)->getCount(array('face_id'=>$interface,'name'=>$data['name']));
            if($valid>0)$this->error('该参数标识已存在');
            $data['face_id']=$interface;
            $id= MasterModel::inIt($this->model_name)->insertData($data);
            if($id){
                $this->success('添加成功',url('index',array('interface'=>$interface)));
            }
            else $this->error('添加失败');


        }
        else
        {
            $_GET=input();
            $_GET['action']=$action;
            if($action=='edit')
            {
                $info=MasterModel::inIt($this->model_name.' m1')->joinData(array(array('interface m2','m1.face_id=m2.id','left')))->field('m1.*,m2.face_name')->getOne(array('m1.id'=>$id));
                $this->assign('info',$info);
            }
            $module=MasterModel::inIt('module')->getListData(array('is_setup'=>1));
            $this->assign('module',$module);
            return view('edit');
        }
    }

    /**
     * 批量添加
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function add_all()
    {
        if(request()->isPost())
        {
            $datas=input('post.data');
            $interface=input('post.interface','');
            foreach($datas as $data)
            {
                $valid=MasterModel::inIt($this->model_name)->getCount(array('face_id'=>$interface,'name'=>$data['name']));
                if($valid>0)continue;
                if(!$data['name'])continue;
                $data['face_id']=$interface;
                $id= MasterModel::inIt($this->model_name)->insertData($data);
            }
            $this->success('添加成功',url('index',array('interface'=>$interface)));

        }
        else
        {
            $_GET=input();
            $_GET['action']='add';
            $module=MasterModel::inIt('module')->getListData(array('is_setup'=>1));
            $this->assign('module',$module);
            return view('add_all');
        }

    }

    /**
     * 删除
     * @param string $id
     */
    function delete($id='')
    {
        if(!$id)$this->error('缺少数据id');
        if(!$this->model_name)$this->error('缺少数据库模型名称');
        $data=array();
        if(strpos($id,',')!==FALSE)
        {
            $data=MasterModel::inIt($this->model_name)->where(array('id'=>array('in',$id)))->select();
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>array('in',$id)))->delete();
        }
        else
        {
            $data=MasterModel::inIt($this->model_name)->where(array('id'=>$id))->select();
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>$id))->delete();
        }

        if($line)$this->success('删除成功',url('index',array('interface'=>$data[0]['face_id'])));
        else $this->error('删除失败');
    }
   

}
