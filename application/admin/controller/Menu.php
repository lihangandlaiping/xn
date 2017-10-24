<?php
/**
 * 后台菜单管理
 */
namespace app\admin\controller;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
class Menu extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='menu';
        config('parent_temple','Admin/Index/base');
    }
    function index($parent_id=0)
    {
        $list=$this->getListData('menu',array('pid'=>$parent_id));
        $this->assign('parent_id',$parent_id);
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
            if($data['pid']>0)
            {
                $orgs=MasterModel::inIt('menu')->field('org')->getOne(array('id'=>$data['pid']));
                $data['org']=$orgs['org']?$orgs['org'].','.$data['pid']:$data['pid'];
            }
            else
            {
                $data['org']='';
            }
            if($action=='edit')
            {
                $id=input('post.id','');
                $line=MasterModel::inIt('menu')->updateData($data,array('id'=>$id));
                if($line)$this->success('修改成功',url('index'));
                else $this->error('修改失败');
                return false;
            }
            $id= MasterModel::inIt('menu')->insertData($data);
            if($id)$this->success('添加成功',url('index'));
            else $this->error('添加失败');
        }
        else
        {
            $_GET['action']=$action;
            if($action=='edit')
            {
                $info=MasterModel::inIt('menu')->getOne(array('id'=>$id));
                $this->assign('info',$info);
            }
            $menu=MasterModel::inIt('menu')->getListData(array('pid'=>0),'sort');
            $this->assign('menu',$menu);
            return view('edit');
        }
    }



}
