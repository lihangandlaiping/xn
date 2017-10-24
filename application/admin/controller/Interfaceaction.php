<?php
/**
 * 后台接口管理
 */
namespace app\admin\controller;

use app\admin\model\Interfaced;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
use think\Loader;

class Interfaceaction extends AdminController
{
    function __construct()
    {
        parent::__construct();

        $this->model_name='interface_action';
        config('parent_temple','Admin/Index/base');
    }
    function index($interface='')
    {
        $_GET['interface']=$interface;
        if($interface=='')$this->error('缺少接口标识');
        $list= $this->getListData($this->model_name.' m1',array('m1.face_id'=>$interface),'m1.*,m2.face_name,m2.face_title','m1.sort asc',null,array(array('interface m2','m1.face_id=m2.id','left')));
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
            switch(intval($data['type']))
            {
                case 1:
                    break;
                case 2:

                case 5:

                case 6:
                    $table=input('post.table','');
                    $data['table_relation']=serialize($table);
                    break;
                case 3:
                    $table=input('post.add','');
                    $data['data_bianlian_name']=serialize($table);
                    break;
                case 4:
                    $table=input('post.update','');
                    $data['data_bianlian_name']=serialize($table);
                    $data['where']=$data['wheres'];
                    break;
            }
            unset($data['wheres']);
            if($action=='edit')
            {
                $id=input('post.id','');
                $line=MasterModel::inIt($this->model_name)->updateData($data,array('id'=>$id));
                if($line){
                    $this->success('修改成功',url('index',array('interface'=>$interface)));
                }
                else $this->error('修改失败');
                return false;
            }

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
            $models=MasterModel::inIt('model')->getListData();
            $this->assign('models',$models);
            return view('edit');
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
