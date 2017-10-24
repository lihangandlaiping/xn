<?php
namespace app\admin\controller;
use My\MasterModel;
use My\MasterController;
use My\Plugfactory;
use think\Controller;
class Index extends MasterController
{
    function __construct()
    {
        parent::__construct();
    }
  function test()
  {
      Plugfactory::getWxPay()->html5Pay(time(),1,'www.baidu.com','sfdas');
  }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function index()
    {
        $admin_user=session('admin_user');
        if(!$admin_user)
        {
            return redirect(url('login'));
        }
        $menus= MasterModel::inIt('user_role')->field('rules')->getOne(array('roleid'=>$admin_user['roleid']));
        $m=MasterModel::inIt('menu')->field('org');

        $orgs=$m->getListData(array('id'=>$menus['rules'],'org'=>array('neq','')));

        $str=$menus['rules'];
        foreach($orgs as $row)
        {
            if($row['org'])
            $str.=",{$row['org']}";
        }
        $auth=array_unique(explode(',',$str));
        cache('admin_auth',$auth);
        $this->assign('menus',$auth);
        $menulist=MasterModel::inIt('menu')->getListData(array('pid'=>0),'sort asc');
        $this->assign('menulist',$menulist);
        $this->assign('admin_user',$admin_user);
        return view('index');
    }
    /**
     * 左侧菜单
     * @param int $pid
     * @param int $level
     * @return mixed
     */
    function left_menu($pid=0,$level=0)
    {
        config('parent_temple','');
        $this->assign('level',$level);
        $list=MasterModel::inIt('menu m1')->field('m1.*,m2.id as m2id')->getListData(array('m1.pid'=>$pid,'m1.hide'=>1),'m1.sort asc','m1.id',array(array('menu m2','m1.id=m2.pid','left')));
        $this->assign('list',$list);
        return $this->fetch('left_menu');
    }
    /**
     * 登录
     * @param $uName
     * @param $pass
     * @param $isCheck
     */
    function login($uName='',$pass='',$isCheck=null)
    {
        if(request()->isPost())
        {
            if($uName=='')$this->error('请填写用户名',url('login'));
            if($pass=='')$this->error('请填写用户密码',url('login'));
            $modle=MasterModel::inIt('user');
            $info=$modle->getOne(array('username'=>$uName));
            if(!$info)$this->error('用户名错误');
            else
            {
                if($info['password']!=$pass)$this->error('用户密码错误',url('login'));
                if($info['status']!=1)$this->error('改账号已被禁用',url('login'));
                session('admin_user',$info);
                if(config('admin_action_log'))
                {
                    notfy_function($_SERVER['HTTP_HOST'].url('admin/index/adminActionLog',array('admin_id'=>session('admin_user.id')?:0,'action'=>4,'model'=>'','url'=>MODULE_NAME.'_'.CONTROLLER_NAME.'_'.ACTION_NAME)));
                }
                $this->success('登录成功',url('index'));
            }
            return false;
        }
        if(session('admin_user'))
        {
            exit("<script>parent.location.href='".url('index')."';</script>");
        }
        return view();
    }


    /**
     * 退出登录
     */
    function loginOut()
    {
        session('admin_user',null);
        header("Location: http://".$_SERVER['HTTP_HOST'].url('index'));
    }
    /**
     *清除缓存
     */
    function deleteCache()
    {
        $dir=YANYU_ROOT.'/runtime';
        $a=rmdirr($dir);
        $this->success('清除成功');
    }

    /**
     * 修改当前用户密码
     */
    function updatePass($id='',$pass='')
    {
        $id=MasterModel::inIt('user')->updateData(array('password'=>$pass),array('id'=>$id));
        if($id)$this->success('修改成功');
        else $this->error('修改失败');
    }

    /**
     * 后台用户操作日志
     */
    function adminActionLog()
    {
        $admin_id=input('admin_id',0);
        $action=input('action',0);
        $model=input('model','');
        $url=input('url','');
        $url=str_replace('_','/',$url);
        $menu=MasterModel::inIt('menu')->field('id')->getOne(array('url'=>$url));
        $urlarr=explode('/',$url);
        $modules=MasterModel::inIt('module')->field('id')->getOne(array('name'=>$urlarr[0]));
        $model=MasterModel::inIt('model')->field('id')->getOne(array('name'=>$model));
        $data['action']=$action;
        $data['admin_id']=$admin_id;
        $data['model']=$model['id']?:0;
        $data['model_name']=input('model','');
        $data['url']=$url;
        if($action==0) $data['menu_id']=$menu['id']?:0;
        $data['module']=$modules['id']?:0;
        $data['add_time']=time();
        MasterModel::inIt('admin_action_log')->insertData($data);
        exit('success');
    }
    function base()
    {
        return view('base');
    }

}
