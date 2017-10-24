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

class Adminlog extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='admin_action_log';
        config('parent_temple','Admin/Index/base');
    }
    function index($starttime='',$endtime='',$action='',$username='')
    {
        $where=array();
        if($action!=='')$where['m1.action']=$action;
        if($username!='')$where['u.id']=$username;
        if($starttime!=''&&$endtime!='')
        {
            $starttime=strtotime($starttime);
            $endtime=strtotime($endtime);
            $where['m1.add_time']=array('between',"{$starttime},{$endtime}");
        }
        elseif($starttime!='')
        {
            $starttime=strtotime($starttime);
            $where['m1.add_time']=array('egt',$starttime);
        }
        elseif($endtime!='')
        {
            $endtime=strtotime($endtime);
            $where['m1.add_time']=array('elt',$endtime);
        }
        if($action!=='')$where['action']=$action;
        $list=$this->getListData( $this->model_name.' m1',$where,'m1.*,m.title,u.username,m2.name as model_names,m2.title as model_title','m1.id desc','',array(array('menu m','m.id=m1.menu_id','left'),array('user u','u.id=m1.admin_id','left'),array('model m2','m2.id=m1.model','left')));
        $user=MasterModel::inIt('user')->field('id,username')->getListData();
        $this->assign('user',$user);
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
