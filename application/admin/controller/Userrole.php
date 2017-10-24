<?php
/**
 * 后台角色
 */
namespace app\admin\controller;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
class Userrole extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='user_role';
        config('parent_temple','Admin/Index/base');
    }
    function index($name='',$status='')
    {
        $where=array();
        if($name!='')$where['rolename']=array('like','%'.$name.'%');
        if($status!='')$where['disabled']=$status;
        $list=$this->getListData($this->model_name,$where,null,'listorder');
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
                $line=MasterModel::inIt('user_role')->updateData($data,array('roleid'=>$id));
                if($line)$this->success('修改成功',url('index'));
                else $this->error('修改失败');
                return false;
            }
            $id= MasterModel::inIt('user_role')->insertData($data);
            if($id)$this->success('添加成功',url('index'));
            else $this->error('添加失败');
        }
        else
        {
            $_GET['action']=$action;
            if($action=='edit')
            {
                $info=MasterModel::inIt('user_role')->getOne(array('roleid'=>$id));
                $this->assign('info',$info);
            }

            return view('edit');
        }
    }

    /**
     * 菜单权限
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function role($id='')
    {
        if(request()->isPost())
        {
            $menu=input('post.menu');
            $line=MasterModel::inIt('user_role')->updateData(array('rules'=>implode(',',$menu)),array('roleid'=>$id));
            if($line)$this->success('授权成功',url('index'));
            else
                $this->error('授权失败');
        }
        else
        {
            $menulist=MasterModel::inIt('user_role')->getOne(array('roleid'=>$id),'rules');
            $menulist=$menulist['rules'];
            $this->assign('menulist',explode(',',$menulist));
            $this->assign('id',$id);
        }
        return view('role');
    }
    function roletest($pid=0,$level=0)
    {
        config('parent_temple','');
        $list=MasterModel::inIt('menu')->getListData(array('pid'=>$pid,'hide'=>1),'sort');
        if(!$list)return '';
        $this->assign('list',$list);
        $this->assign('level',$level);
        return $this->fetch('roleline');
    }

}
