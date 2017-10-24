<?php
/**
 * 后台字段管理
 */
namespace app\admin\controller;
use app\admin\model\Tabelaction;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
use think\Loader;

class Field extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='field';
        config('parent_temple','Admin/Index/base');

    }
    function index($model_id='',$name='',$field='')
    {
        $_GET['model_id']=$model_id;
        $where=array();
        if($model_id!='')$where['model_id']=$model_id;
        else $this->error('缺少模型标识');
        if($name!='')$where['name']=array('like','%'.$name.'%');
        if($field!='')$where['field']=array('like','%'.$field.'%');
        $list=$this->getListData( $this->model_name.' m1',$where);
        return view('index',array('list'=>$list));
    }

    /**
     * 编辑
     */
    function edit($id='',$action='add',$model_id='')
    {
        $_GET['model_id']=$model_id;
        if(request()->isPost())
        {
            $data=input('post.data');
            if(!$data['type'])$this->error('请选择数据类型');
            if(!$data['name'])$this->error('请填写字段名');
            if(!preg_match('/^\w+$/',$data['field']))$this->error('字段标识格式错误');
            if($action=='edit')
            {
                $id=input('post.id','');
                $fieldvalid=MasterModel::inIt('field')->getCount(array('model_id'=>$model_id,'field'=>$data['field'],'id'=>array('neq',$id)));
                if($fieldvalid>0)$this->error('该字段标示以存在');
                $fieldold=MasterModel::inIt('field')->getOne(array('id'=>$id));
                $line=MasterModel::inIt($this->model_name)->updateData($data,array('id'=>$id));
                if($line){
                    $pix=config('prefix');
                    $models= MasterModel::inIt('model')->getOne(array('id'=>$data['model_id']));
                    if($data['value']!=='')$defaultval="DEFAULT '{$data['value']}'";
                    else $defaultval="";
                    $type=getFieldType($data['type']);
                    $sql="ALTER TABLE {$pix}{$models['name']} CHANGE COLUMN {$fieldold['field']} {$data['field']} {$type} {$defaultval} COMMENT '{$data['title']} {$data['remark']}'";
                    //exit($sql);
                    @MasterModel::inIt($this->model_name)->execute($sql);
                    $this->success('修改成功',url('index',array('model_id'=>$data['model_id'])));
                }
                else $this->error('修改失败');
                return false;
            }
                $fieldvalid=MasterModel::inIt('field')->getCount(array('model_id'=>$model_id,'field'=>$data['field']));
                if($fieldvalid>0)$this->error('该字段标示以存在');
                $models= MasterModel::inIt('model')->getOne(array('id'=>$data['model_id']));
                $pix=config('prefix');
                if($data['value']!=='')$defaultval="DEFAULT '{$data['value']}'";
                else $defaultval="";
                $type=getFieldType($data['type']);
                $sql="ALTER TABLE {$pix}{$models['name']} ADD COLUMN {$data['field']} {$type} {$defaultval} COMMENT '{$data['title']} {$data['remark']}'";
                MasterModel::inIt($this->model_name)->execute($sql);
                $id= MasterModel::inIt($this->model_name)->insertData($data);
                if($id){
                    $this->success('添加成功',url('index',array('model_id'=>$model_id)));
                }
                else $this->error('添加失败');

        }
        else
        {
            $_GET['action']=$action;

            if($action=='edit')
            {
                $info=MasterModel::inIt($this->model_name.' m1')->joinData(array(array('model m2','m1.model_id=m2.id','left')))->field('m1.*,m2.name as model_name')->getOne(array('m1.id'=>$id));
                $this->assign('info',$info);
            }
            return view('edit');
        }
    }
    /**
     * 批量添加
     */
    function add_all($action='add',$model_id='')
    {
        $_GET['model_id']=$model_id;
        if(request()->isPost())
        {
            $datas=input('post.data');
            $is_true=false;
            foreach($datas as $data)
            {
                if(!$data['type']||!$data['field'])continue;
                if(!$data['name'])$this->error('请填写字段名');
                if(!preg_match('/^\w+$/',$data['field']))continue;
                $fieldvalid=MasterModel::inIt('field')->getCount(array('model_id'=>$model_id,'field'=>$data['field']));
                if($fieldvalid>0)continue;
                $data['title']='';
                $data['remark']='';
                $id= MasterModel::inIt($this->model_name)->insertData($data);
                if($id){
                    $is_true=true;
                    $models= MasterModel::inIt('model')->getOne(array('id'=>$data['model_id']));
                    $pix=config('prefix');
                    if($data['value']!=='')$defaultval="DEFAULT '{$data['value']}'";
                    else $defaultval="";
                    $type=getFieldType($data['type']);
                    try
                    {
                        $sql="ALTER TABLE {$pix}{$models['name']} ADD COLUMN {$data['field']} {$type} {$defaultval} COMMENT '{$data['title']} {$data['remark']}'";
                        @MasterModel::inIt($this->model_name)->execute($sql);
                    }catch (\Exception $e)
                    {

                    }


                }
            }
            if($is_true)
            $this->success('添加成功',url('index',array('model_id'=>$model_id)));
            else
            $this->error('添加失败');
        }
        else
        {
            $_GET['action']=$action;
            return view('add_all');
        }
    }

    /**
     * 删除
     */
    function delete($id='')
    {
        if(!$id)$this->error('缺少数据id');
        if(!$this->model_name)$this->error('缺少数据库模型名称');
        $arr=array();
        if(strpos($id,',')!==FALSE)
        {
            $arr= MasterModel::inIt($this->model_name.' m1')->field('m1.*,m2.name as model_name')->joinData(array(array('model m2','m1.model_id=m2.id','left')))->getListData(array('m1.id'=>array('in',$id)));
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>array('in',$id)))->delete();
        }
        else
        {
            $arr= MasterModel::inIt($this->model_name.' m1')->field('m1.*,m2.name as model_name')->joinData(array(array('model m2','m1.model_id=m2.id','left')))->getListData(array('m1.id'=>$id));
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>$id))->delete();

        }
        if($line){
            $pix=config('prefix');
            try
            {
                foreach($arr as $row)
                {

                    MasterModel::inIt($this->model_name)->execute("alter table {$pix}{$row['model_name']} drop column {$row['field']}");
                }
            }catch (\Exception $e)
            {

            }

            $this->success('删除成功',url('index',array('model_id'=>$arr[0]['model_id'])));
        }
        else $this->error('删除失败');
    }

    /**
     * 表单显示、列表显示 设置
     * @param string $model_id
     * @param string $type
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function getFieldShow($model_id='',$type=1)
    {
        if($model_id=='')
        $this->error('缺少模型标识');
        $order='is_show desc,show_srot asc';
        $list=MasterModel::inIt($this->model_name)->getListData(array('model_id'=>$model_id),$order);
        $this->assign('list',$list);
        return view('field_show');
    }

    /**
     * 修改字段信息
     */
    function updateFieldShow()
    {
        $data=input('post.data','');
            foreach($data as $key=>$val)
            {
                @MasterModel::inIt($this->model_name)->updateData($val,array('id'=>$key));
            }
            $this->success('操作成功');
    }

}
