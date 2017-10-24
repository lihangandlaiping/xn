<?php
/**
 * 后台接口分类管理
 */
namespace app\admin\controller;
use app\admin\model\Tabelaction;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
use think\Loader;

class Interfaceclass extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='interface_class';
        config('parent_temple','Admin/Index/base');
    }
    function index()
    {
        $list=$this->getListData( $this->model_name);
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
                   /* $ids=MasterModel::inIt($this->model_name)->field('menuid')->getOne(array('id'=>$id));
                    MasterModel::inIt('menu')->updateData(array('title'=>$data['class_name']),array('id'=>$ids['menuid']));*/
                    $this->success('修改成功',url('index'));
                }
                else $this->error('修改失败');
                return false;
            }
            /*$idsd=MasterModel::inIt('menu')->insertData(array('pid'=>15,'title'=>$data['class_name'],'url'=>'admin/interfaced/index?class_id='.$id));
            $data['menuid']=$idsd;*/
            $id= MasterModel::inIt($this->model_name)->insertData($data);
            if($id){
               // MasterModel::inIt('menu')->updateData(array('url'=>'admin/interfaced/index?class_id='.$id),array('id'=>$idsd));
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
        $arr=array();
        if(strpos($id,',')!==FALSE)
        {
            $arr=MasterModel::inIt($this->model_name)->getListData(array('id'=>array('in',$id)));
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>array('in',$id)))->delete();
        }
        else
        {
            $arr=MasterModel::inIt($this->model_name)->getListData(array('id'=>$id));
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>$id))->delete();
        }
        foreach($arr as $r)
        {
            MasterModel::inIt('menu')->where(array('id'=>$r['menuid']))->delete();
        }
        if($line)$this->success('删除成功',url('index'));
        else $this->error('删除失败');
    }

}
