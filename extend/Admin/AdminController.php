<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/1
 * Time: 17:19
 */
namespace Admin;
use app\admin\model\Form;
use My\MasterController;
use My\MasterModel;
use think\Config;

class AdminController extends MasterController
{
    protected $model_name='';//数据库表名
    protected $admin_user=null;//登录用户信息
    function __construct()
    {
        parent::__construct();
        $this->admin_user=session('admin_user');
        $admin_auth=cache('admin_auth');
        if(empty($this->admin_user)||($this->admin_user['type']==2&&empty($admin_auth)))
        {
            $this->error('你的登录已过期！',url('admin/index/index'),'',3,1);exit;
        }
        $this->assign('admin_user',$this->admin_user);
        $url=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        $menu=MasterModel::inIt('menu')->field('id,title')->getOne(array('url'=>$url));//菜单标题
        if($menu)
        {
            if($this->admin_user['type']==2)
            {
                if(!adminAouth($this->admin_user['roleid'],$menu['id']?:''))$this->error('你没有该操作的权限请联系管理员！');//权限验证
            }
            $this->assign('menuTitle',$menu['title']);
        }
        //加载配置
        $this->_redyConfig();
        //分页设置
        $this->pageSize=config('admin_page_size')?:$this->pageSize;
    }

    /**
     * 配置加载
     */
    private function _redyConfig()
    {
        //配置设置
        if(!Config::has('admin_action_log'))
        {
            $conf=cache('config_'.MODULE_NAME)?:array();
            if(!$conf)
            {
                $module=MasterModel::inIt('module')->field('id')->getOne(array('name'=>MODULE_NAME));
                $where="is_show in ('1','3') ";
                if($module['id'])
                    $where.="and (module_id=0 or module_id={$module['id']})";
                else $where.="and module_id=0";
                $conf=MasterModel::inIt('config c')->field('name,content')->getListData($where);
                cache('config_'.MODULE_NAME,$conf);
            }
            foreach($conf as $a)
            {
                config($a['name'],$a['content']?:'');
            }
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
        if(strpos($id,',')!==FALSE)
        {
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>array('in',$id)))->delete();
        }
        else
        {
            $line=MasterModel::inIt($this->model_name)->where(array('id'=>$id))->delete();
        }

        if($line)$this->success('删除成功',url('index'));
        else $this->error('删除失败');
    }

    /**
     * 获取数据分页
     * @param \My\表名 $tableName
     * @param null $where
     * @param string $field
     * @param null $order
     * @param null $group
     * @param array $join
     * @return mixed
     */
    function getListData($tableName,$where=null,$field='*',$order=null,$group=null,$join=array())
    {
        if(config('admin_action_log'))
        {
            notfy_function($_SERVER['HTTP_HOST'].url('admin/index/adminActionLog',array('admin_id'=>$this->admin_user['id'],'action'=>0,'model'=>'','url'=>MODULE_NAME.'_'.CONTROLLER_NAME.'_'.ACTION_NAME)));
        }
        return parent::getListData($tableName,$where,$field,$order,$group,$join);
    }
    /**
     * 列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function index()
    {
        $fieldlist=$this->getFieldList();//获取显示字段
        $where=$this->validSearch($fieldlist['showfield']);
        //判断如果是级联列表
        $module=MasterModel::inIt('model')->field('is_cascade,cascade_field')->getOne(array('name'=>$this->model_name));

        if($module['is_cascade']==1){
            $where[$this->model_name.'.'.trim($module['cascade_field'])]=input(trim($module['cascade_field']),0);
            $this->assign('cascade_field',trim($module['cascade_field']));
        }
        $order='';
        $group='';
        $field="{$this->model_name}.id,".$fieldlist['fieldlist'];
        //搜索封装验证
        $list=$this->getListData($this->model_name." {$this->model_name}",$where,$field,$order,$group,tableRelation($this->model_name));
        $list=$this->validDataList($list,$fieldlist['showfield']);
        $this->assign('list',$list);
        $this->assign('field',$fieldlist['showfield']);
        //生成搜索相关数据
        $this->assign('model_name',$this->model_name);
        $this->assign('form',new Form());
        return view('index');

    }

    /**
     * 编辑
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function edit()
    {
        $action=input('action','add');
        $fieldlist=$this->getModelFromField($action);//获取表单字段
        if(request()->isPost())
        {
            $data=$this->validform($fieldlist,$action);//验证表单数据
            if($action=='edit')
            {
                $line=MasterModel::inIt($this->model_name)->updateData($data,array('id'=>input('post.id','')));
                if($line)$this->success('修改成功',url('index'));
                else $this->error('修改失败');
            }
            else
            {
                $id=MasterModel::inIt($this->model_name)->insertData($data);
                if($id)$this->success('添加成功',url('index'));
                else $this->error('添加失败');
            }
        }
        else
        {
            $_GET=input();
            $_GET['action']=$action;
            $cascade_field='';
            $form=new Form();
            $values=array();
            if($action=='edit')
            {
                $values=MasterModel::inIt($this->model_name)->getOne(array('id'=>$_GET['id']));
            }
            else
            {

                $module=MasterModel::inIt('model')->field('is_cascade,cascade_field')->getOne(array('name'=>$this->model_name));
                if($module['is_cascade']==1){
                    $values[trim($module['cascade_field'])]=input(trim($module['cascade_field']),0);
                    $cascade_field=trim($module['cascade_field']);
                }
            }
            $this->assign('info',$values);
            $this->assign('formstr',$form->createFrom($fieldlist,$values,$cascade_field));
            return view('edit');
        }
    }

    /**
     * 获取模型中的form表单字段
     * @param $action
     * @return mixed
     */
    protected function getModelFromField($action)
    {
        if(!$action)$action=input('action','add');
        if(!$this->model_name)$this->error('缺少数据库名');
        $where=array('m.name'=>$this->model_name);
        if($action=='add')$where['f.is_show']=array('in','1,2');
        elseif($action=='edit')$where['f.is_show']=array('in','1,3');
        $list= MasterModel::inIt('field f')->field('f.*')->joinData(array(array('model m','m.id=f.model_id','left')))->getListData($where,'f.show_srot asc');
        return $list;
    }

    /**
     * 获取列表显示字段
     * @return mixed
     */
    protected function getFieldList()
    {
        if(!$this->model_name)$this->error('缺少数据库名');
        $field=MasterModel::inIt('model')->field('show_filed,id')->getOne(array('name'=>$this->model_name));

        if(!$field['show_filed']){
            $field = MasterModel::inIt('field f')->field('f.field,f.name,f.type,ma.name as tables,f.extra,f.value')->joinData(array(array('model ma', 'ma.id=f.model_id', 'left')))->getListData(array('f.model_id' => $field['id'], 'f.is_column' => 2));
        }
        else
        {
            $field=unserialize($field['show_filed']);
        }
        $fieldstr='';
        $newfield=array();
        if(!$field)$this->error('请设置列表显示');
        foreach($field as $row)
        {
            if(strpos($row['field'],'.'))
            {
                $a=str_replace('.','_',$row['field']);
                $row['field']=$row['field'].' as '.$a;
                $newfield[]=array('field'=>$a,'name'=>$row['name'],'tables'=>$row['tables'],'type'=>$row['type'],'extra'=>$row['extra'],'searchd'=>$row['searchd']);
            }
            else
            {
                $newfield[]=$row;
            }
            if($fieldstr!='')$fieldstr.=',';
            $fieldstr.=$this->model_name==$row['tables']?$this->model_name.'.'.$row['field']:$row['field'];
        }
        return array('fieldlist'=>$fieldstr,'showfield'=>$newfield);

    }
    /**
     * 验证表单数据
     */
    protected function validform($fieldlist,$action='add')
    {
        $data=array();
        foreach($fieldlist as $row)
        {
            $valsd=input('post.'.$row['field'],'');
            if($row['is_must']&&$valsd==''){$this->error("{$row['name']},为必填字段！");exit;}//非空验证
            $data[$row['field']]=$valsd;
            switch ($row['type'])
            {
                case 'linkage':
                case 'images':
                case 'checkbox':
                case 'attachs':
                    $data[$row['field']]=$data[$row['field']]?implode(',',$data[$row['field']]):'';
                    break;
                case 'date':
                case 'datetime':
                    $data[$row['field']]=strtotime($data[$row['field']]);
                    break;
            }
            if((($row['validate_type']==1&&$action=='add')||($row['validate_type']==2&&$action=='edit'))&&$row['validate_rule'])
            {
                if($row['is_must'])
                {
                    if(!$data[$row['field']])$this->error($row['error_info']);
                }
                if( !call_user_func($row['validate_rule'],$data[$row['field']]))$this->error($row['error_info']);
            }
        }
        return $data;

    }

    /**
     * 列表数据封装
     * @param $data
     * @param $field
     * @return mixed
     */
    protected function validDataList($data,$field)
    {
        foreach($data as $key=>&$row)
        {
            foreach($field as $val)
            {
                switch($val['type'])
                {
                    case 'date':
                        $row[$val['field']]=date('Y-m-d',$row[$val['field']]);
                        break;
                    case 'datetime':
                        $row[$val['field']]=date('Y-m-d H:i:s',$row[$val['field']]);
                        break;
                    case 'bool':
                    case 'select':
                    case 'radio':
                    case 'checkbox':
                        $extra=array_filter(preg_split('/[\r\n]+/s',$val['extra']));
                        $news=array();
                        foreach($extra as $rs)
                        {
                            $s=explode(':',$rs);
                            if(isset($s[0])&&isset($s[1]))$news[$s[0]]=$s[1];
                        }
                        $row[$val['field'].'_num']=$row[$val['field']];
                        $valsd=explode(',',$row[$val['field']]);
                        $str='';
                        foreach($valsd as $rowsd)
                        {
                            if($str!='')$str.=',';
                            $str.=isset($news[$rowsd])?$news[$rowsd]:'';
                        }
                        $row[$val['field']]=$str;
                    break;
                    case 'thumb':
                        $row[$val['field']]=$row[$val['field']]?"<img style='width: 150px;' src='".__ROOT__."{$row[$val['field']]}'>":'';
                        break;
                    case 'linkage':
                        $extra=array_filter(preg_split('/[\r\n]+/s',$val['extra']));
                        $news=array();
                        foreach($extra as $rs)
                        {
                            $s=explode(':',$rs);
                            if(isset($s[0])&&isset($s[1]))$news[trim($s[0])]=$s[1];
                        }
                        if($row[$val['field']])
                        {
                            $listd=MasterModel::inIt($news['table'])->field("{$news['name']}")->getListData(array("{$news['id']}"=>array('in',$row[$val['field']])));
                            $a='';
                            foreach($listd as $rowss)
                            {
                                if($a!='')$a.=',';
                                $a.= $rowss[trim($news['name'])];
                            }
                            $row[$val['field']]=$a;
                        }

                        break;
                    case 'images':
                        $images=explode(',',$row[$val['field']]);
                        $html='<div class="carousel slide" id="carousel'.$key.'" style="width: 150px;">
                                    <div class="carousel-inner">
                                    %s
                                    </div>
                                    <a data-slide="prev" href="carousel.html#carousel'.$key.'" class="left carousel-control">
                                        <span class="icon-prev"></span>
                                    </a>
                                    <a data-slide="next" href="carousel.html#carousel'.$key.'" class="right carousel-control">
                                        <span class="icon-next"></span>
                                    </a>
                                </div>';
                        $str='';
                        foreach($images as $k=>$r)
                        {
                            $acti=$k==0?'active':'';
                            $str.=' <div class="item '.$acti.'">
                                            <img style="width:100%" alt="image"  class="img-responsive" src="'.__ROOT__.$r.'">
                                        </div>';
                        }
                        $row[$val['field']]=sprintf($html,$str);
                        break;
                    case 'tablefield':
                        $newarray = array();
                        $vals = array_filter(preg_split('/[\r\n]+/s', $val['extra']));
                        foreach ($vals as &$v) {
                            $v = explode(':', $v);
                            $newarray[trim($v[0])] = $v[1];
                        }
                        $qlslist = \My\MasterModel::inIt($newarray['db_table'])->field($newarray['primary_key'].','.$newarray['search_field'])->getOne(array($newarray['primary_key']=>$row[$val['field']]));
                        $row[$val['field']]=$qlslist[$newarray['search_field']]?:'';
                        break;

                }
            }
        }
        return $data;
    }

    /**
     * @param $fieldlist
     * @return array
     */
    protected function validSearch($fieldlist)
    {
        $where=array();
        foreach($fieldlist as $row)
        {
            if(!isset($row['searchd']))continue;
            if($row['searchd']==1)
            {
                $field=$row['field'];
                if($row['tables']!=$this->model_name)
                {
                    $row['field']=preg_replace('/_+/s','.',$row['field'],1);
                }
                else
                {
                    $row['field']=$this->model_name.'.'.$row['field'];
                }
                if($row['type']=='string')
                {
                    $vals=input($field,'');
                    if($vals!='') $where[$row['field']]=array('like','%'.$vals.'%');
                }
                elseif($row['type']=='checkbox')
                {
                    $vals=input($field,'');
                    if(is_array($vals))$vals=implode(',',$vals);
                    if($vals!='') $where[$row['field']]=array('exp',"find_in_set({$row['field']},'{$vals}')");
                }
                elseif($row['type']=='datetime'||$row['type']=='date')
                {
                    $start=input($field.'start','');
                    $end=input($field.'end','');
                    if($start!=''&&$end!=''){$start=strtotime($start);$end=strtotime($end);$where[$row['field']]=array('between',"{$start},{$end}");}
                    elseif($start!=''){$start=strtotime($start);$where[$row['field']]=array('egt',$start);}
                    elseif($end!=''){$end=strtotime($end);$where[$row['field']]=array('elt',$end);}
                }
                elseif($row['type']=='linkage')
                {
                    $linkpage=input($field,'');
                    if($linkpage)
                    {
                        if(is_array($linkpage))$linkpage=implode(',',$linkpage);
                        $where[$row['field']]=array('like',$linkpage."%");
                    }

                }
                else
                {
                    $vals=input($field,'');
                    if($vals!='') $where[$row['field']]=$vals;
                }
            }
        }
        return $where;
    }
}