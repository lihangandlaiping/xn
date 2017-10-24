<?php
/**
 * 后台模块管理
 */
namespace app\admin\controller;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
class Module extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name='module';
        config('parent_temple','Admin/Index/base');
    }
    function index($name='',$status='')
    {

        $list=$this->getListData( $this->model_name,null,'*','sort');
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
                    /*$modulevalid=MasterModel::inIt('module')->getCount(array('name'=>$data['name'],'id'=>array('neq',$id)));
                    if($modulevalid>0)$this->error('模块标识以存在');*/
                    try{
                        $line=MasterModel::inIt($this->model_name)->updateData($data,array('id'=>$id));
                    }catch (\Exception $e)
                    {
                        $this->error('添加失败');
                    }
                    if($line)$this->success('修改成功',url('index'));
                    else $this->error('修改失败');
                    return false;
                }
            if(!preg_match('/^\w+$/',$data['name']))$this->error('模块标识格式错误');
            $data['name']=strtolower($data['name']);
            $modulevalid=MasterModel::inIt('module')->getCount(array('name'=>$data['name']));
            if($modulevalid>0)$this->error('模块标识以存在');
                try{
                    $id= MasterModel::inIt($this->model_name)->insertData($data);
                }catch (\Exception $e)
                {
                    $this->error('添加失败');
                }
                if($id&&create_module(APP_PATH.$data['name'])){
                    $this->success('添加成功',url('index'));
                }
                else $this->error('添加失败');
        }
        else
        {
            $_GET['action']=$action;
            if($action=='edit')
            {
                $info=MasterModel::inIt($this->model_name)->getOne(array('id'=>$id));
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
        $models=array();
        if(strpos($id,',')!==FALSE)
        {
            $models=MasterModel::inIt($this->model_name)->getListData(array('id'=>array('in',$id)));
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>array('in',$id)))->delete();
        }
        else
        {
            $models=MasterModel::inIt($this->model_name)->getListData(array('id'=>$id));
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>$id))->delete();
        }
        foreach($models as $row)
        {

            $path=APP_PATH.$row['name'];
            if($row['name']&&file_exists($path))
            rmdirr($path);
        }
        if($line)$this->success('删除成功',url('index'));
        else $this->error('删除失败');
    }

    /**
     * 打包模块
     * @param string $id
     */
    function packaging($id='')
    {
        if(!$id)$this->error('缺少模块唯一标识');
        $basepath=YANYU_ROOT.'/module';
        $models=MasterModel::inIt($this->model_name)->getOne(array('id'=>$id));
        @mkdir($basepath."/{$models['name']}",0777);
        @mkdir($basepath."/{$models['name']}/code",0777);
        $filename=$basepath."/{$models['name']}/code/pack.zip";
        $zip = new \ZipArchive();//使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        if ($zip->open($filename, \ZIPARCHIVE::CREATE)!==TRUE) {
            $this->error('无法打开文件，或者文件创建失败');
        }
        $a='./application/'.$models['name'];
        $this->addFileToZip($a,$zip);
        $zip->close();//关闭
        $pix=config('prefix');//数据库表前缀
        $sqlcode=array();
        //生成模块数据插入sql
        unset($models['id']);
        $sqlcode['module_data']=$models;
        // 模型数据插入sql
        $modellist=MasterModel::inIt('model')->field('id,name,title,engine_type,show_filed,is_cascade,cascade_field')->getListData(array('modul_id'=>$id));
        $modelssql=array();
        foreach($modellist as $row)
        {
            $filedlist=MasterModel::inIt('field')->field('name,title,field,type,value,remark,is_show,is_column,show_srot,column_srot,extra,is_must,add_time,validate_rule,error_info,validate_type')->getListData(array('model_id'=>$row['id']));
            unset($row['id']);
            $modelssql[]=array('model'=>$row,'field'=>$filedlist);
        }
        $sqlcode['models_data']=$modelssql;
        file_put_contents($basepath."/{$models['name']}/data.data",serialize($sqlcode));
        $this->success('打包成功');
    }

    /**
     * 压缩
     * @param $path
     * @param $zip
     */
    private function addFileToZip($path,$zip){
        $handler=opendir($path); //打开当前文件夹由$path指定。

        while(($filename=readdir($handler))!==false){
            if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                    $this->addFileToZip($path."/".$filename, $zip);
                }else{ //将文件加入zip对象
                    $a=$path."/".$filename;
                    $zip->addFile($path."/".$filename,str_replace('./application/','',$a));
                }
            }
        }
        @closedir($path);
    }

    /**
     * 安装模块 列表
     */
    function installPackage()
    {
        $data=array();
        $path=YANYU_ROOT.'/module';
        $handler=opendir($path); //打开当前文件夹由$path指定。
        while(($filename=readdir($handler))!==false){
            if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                    $data[]=$filename;
                }else{ //将文件加入zip对象

                }
            }
        }
        $module=MasterModel::inIt($this->model_name)->field('name')->getListData();
        $newmodule=array();
        foreach($module as $row)
        {
            $newmodule[]=$row['name'];
        }
       $this->assign('list',$data);
        $this->assign('newmodule',$newmodule);
        return view('selectpackage');
    }

    /**
     * 安装模块
     * @param string $name
     */
    function installModule($name='')
    {
        ob_end_clean();
        if(!$name){
            echo '模块信息错误';
            flush();
        }
        $basepath=YANYU_ROOT.'/module/'.$name;
        $zip = new \ZipArchive;
        $res = $zip->open($basepath.'/code/pack.zip');
        $pix=config('prefix');//数据库表前缀
        if ($res === TRUE) {
            //解压缩到test文件夹
            $zip->extractTo(APP_PATH);
            $zip->close();
            echo '项目安装成功,正在导入数据库<br/>';
            flush();
            $data=file_get_contents($basepath.'/data.data');
            $data=unserialize($data);
            $moduleid=MasterModel::inIt($this->model_name)->insertData($data['module_data']);
            if($moduleid)
            {
                $models=MasterModel::inIt('model');
                $filed=MasterModel::inIt('field');
                foreach($data['models_data'] as $row)
                {
                    $models->execute("CREATE TABLE {$pix}{$row['model']['name']} (
  id int(10) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ENGINE={$row['model']['engine_type']} AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
                    $row['model']['modul_id']=$moduleid;
                    $modelid=MasterModel::inIt('model')->insertData($row['model']);
                    foreach($row['field'] as $vals)
                    {
                        $type=getFieldType($vals['type']);
                        if($vals['value']!=='')$defaultval="DEFAULT '{$vals['value']}'";
                        else $defaultval="";
                        $sql="ALTER TABLE {$pix}{$row['model']['name']} ADD COLUMN {$vals['field']} {$type} {$defaultval} COMMENT '{$vals['title']} {$vals['remark']}'";
                        @$models->execute($sql);
                        $vals['model_id']=$modelid;
                        MasterModel::inIt('field')->insertData($vals);
                    }
                    echo '数据库:'.$row['model']['name'].'  导入成功！<br/>';
                    flush();
                }
            }
            echo '安装完成';
            flush();
        } else {
            echo 'failed, code:' . $res;
            flush();
        }
    }
}
