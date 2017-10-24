<?php
/**
 * 后台管理员
 */
namespace app\admin\controller;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
class Adminuser extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='user';
        config('parent_temple','Admin/Index/base');
    }
    function index($name='',$status='')
    {
        $where=array();
        if($name!='')$where['username']=array('like','%'.$name.'%');
        if($status!='')$where['status']=$status;
        $list=$this->getListData('user u',$where,'u.*,ur.rolename','id desc',null,array(array('user_role ur','ur.roleid=u.roleid','left')));
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
                $line=MasterModel::inIt('user')->updateData($data,array('id'=>$id));
                if($line)$this->success('修改成功',url('index'));
                else $this->error('修改失败');
                return false;
            }
            $id= MasterModel::inIt('user')->insertData($data);
            if($id)$this->success('添加成功',url('index'));
            else $this->error('添加失败');
        }
        else
        {
            $_GET['action']=$action;
            if($action=='edit')
            {
                $info=MasterModel::inIt('user')->getOne(array('id'=>$id));
                $this->assign('info',$info);
            }
            $role=MasterModel::inIt('user_role')->getListData(array('disabled'=>1),'listorder');
            $this->assign('role',$role);
            return view('edit');
        }
    }

}
