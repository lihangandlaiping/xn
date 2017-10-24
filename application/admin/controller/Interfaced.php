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

class Interfaced extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='interface';
        config('parent_temple','Admin/Index/base');
    }
    function interface_test()
    {
        $classlist=MasterModel::inIt('interface_class')->field('class_name as text,id')->getListData();
        $this->assign('class',$classlist);
        $oneinterf=array();
        foreach($classlist as $key=>&$row)
        {
            $row['nodes']=array();
            $interface=MasterModel::inIt('interface')->getListData(array('class_id'=>$row['id']));
            if($key==0)$oneinterf=$interface;
            $count=MasterModel::inIt('interface')->getCount(array('class_id'=>$row['id']));
            $row['tags']=array("{$count}");
            $row['href']='#';
           foreach($interface as $val)
           {
               $val['face_title'].=$val['is_ios']==1&&$val['is_android']==1?'(安卓,ios)':($val['is_ios']==1?'(ios)':($val['is_android']==1?'(安卓)':''));
               $row['nodes'][]=array('text'=>$val['face_title'],'href'=>$row['id'].','.$val['id'],'tags'=>array("0"));
           }
        }
        $this->assign('data',json_encode($classlist));
        $this->assign('oneinterf',$oneinterf);
        return view('test');
    }
    function interfaceparem()
    {
        config('parent_temple','');
        $face_id=input('face_id','');
        $parematers=MasterModel::inIt('interface_paremater')->getListData(array('face_id'=>$face_id));
        $interface=MasterModel::inIt('interface')->getOne(array('id'=>$face_id));
        $this->assign('parematers',$parematers);
        $this->assign('interface',$interface);
        return view('interface_parem');
    }
    function classgetinterface()
    {
        $class_id=input('class_id','');
        $interface=MasterModel::inIt('interface')->getListData(array('class_id'=>$class_id));
        echo json_encode($interface);
    }
    function getinterfacevalus()
    {
        $id=input('id','');
        $interface=MasterModel::inIt('interface')->getOne(array('id'=>$id));
        echo json_encode($interface);
    }
    function index($module_id='',$name='',$title='',$class_id='')
    {
        $where=array();
        if($module_id!='')$where['module_id']=$module_id;
        if($name!='')$where['face_name']=array('like','%'.$name.'%');
        if($title!='')$where['face_title']=array('like','%'.$title.'%');
        if($class_id!='')$where['class_id']=$class_id;
        $list=MasterModel::inIt($this->model_name)->getListData($where,'id desc');
        $module=MasterModel::inIt('module')->getListData(null,'sort asc');
        $this->assign('module',$module);
        return view('index',array('list'=>$list));
    }
    /**
     * 生成接口文件
     */
    function createInterface()
    {
            set_time_limit(0);
            @mkdir(APP_PATH.'app/',0777);
            @mkdir(APP_PATH.'app/controller/',0777);
            $modulelist=MasterModel::inIt('module')->field('name,id')->getListData(array('is_setup'=>1));
            $inter=new \app\admin\model\Interfaced();
                foreach($modulelist as $rows)
                {
                    $rows['name']=strtolower($rows['name']);
                    $filepath=APP_PATH."app/controller/Yy{$rows['name']}.php";
                    $filepath=str_replace('\\','/',$filepath);
                    if(file_exists($filepath))
                        unlink($filepath);
                    file_put_contents($filepath,"<?php \r\n namespace app\app\controller;\r\n use My\MasterModel;\r\n class Yy{$rows['name']} extends \\App\\AppController\r\n {\r\n function __construct(){\r\n parent::__construct();\r\n}\r\n");
                    $list=MasterModel::inIt('interface')->getListData(array('module_id'=>$rows['id']));
                    $inter->createInterface($list,$filepath);
                }
                $this->success('生成成功');


    }

    /**
     * 更新接口
     * @param string $interface
     */
    function updateinterface($interface='')
    {
        if(!$interface)$this->error('缺少接口标识');
        $list=MasterModel::inIt('interface c')->field('c.*,m.name as module_name')->joinData(array(array('module m','m.id=c.module_id','left')))->getOne(array('c.id'=>$interface));
        $inter=new \app\admin\model\Interfaced();
        $list['module_name']=strtolower($list['module_name']);
        $filepath=APP_PATH."app/controller/Yy".$list['module_name'].".php";
        $filepath=str_replace('\\','/',$filepath);
        $inter->updateInterface($list,$filepath);
        $this->success('更新成功');
    }
    /**
     * 编辑
     */
    function edit($id='',$action='add')
    {
        if(request()->isPost())
        {
            $data=input('post.data');
            $data['update_time']=time();
            if($action=='edit')
            {
                $id=input('post.id','');
                $modules=MasterModel::inIt('module m')->field('m.name as module_name')->getOne(array('m.id'=>$data['module_id']));
                $data['href']=$modules['module_name']&&$data['face_name']?url('app/Yy'.$modules['module_name'].'/'.$data['face_name']):'';
                $line=MasterModel::inIt($this->model_name)->updateData($data,array('id'=>$id));
                if($line){
                    $this->success('修改成功',url('index'));
                }
                else $this->error('修改失败');
                return false;
            }
            $modules=MasterModel::inIt('module m')->field('m.name as module_name')->getOne(array('m.id'=>$data['module_id']));
            $data['href']=$modules['module_name']&&$data['face_name']?url('app/Yy'.$modules['module_name'].'/'.$data['face_name']):'';
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
                $info=MasterModel::inIt($this->model_name.' m1')->joinData(array(array('module m2','m1.module_id=m2.id','left')))->field('m1.*,m2.name as module_name')->getOne(array('m1.id'=>$id));
                $this->assign('info',$info);
            }
            $module=MasterModel::inIt('module')->getListData(array('is_setup'=>1));
            $this->assign('module',$module);
            $class=MasterModel::inIt('interface_class')->getListData();
            $this->assign('class',$class);
            return view('edit');
        }
    }



function addInterfaceValues($values='',$id=0)
{
    MasterModel::inIt('interface')->updateData(array('values'=>$values),array('id'=>$id));
    echo '1';
}
}
