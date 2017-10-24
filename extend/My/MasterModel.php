<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/1
 * Time: 17:19
 */
namespace My;
use think\Config;
use think\Db;

class MasterModel extends \think\Model
{
    protected $model_field='';
    protected $model_join='';
    protected $table='';
    protected $prefix='';
  private static $database=array();
    function __construct($table='')
    {
        Config::load(APP_PATH.'database.php');
        $pix=Config::get('prefix');
        if($table!='')
        {
            $this->prefix=$pix;
            if(strpos($table,$pix)!==FALSE) {$this->table=$table;}
            else {$this->table=$this->prefix.$table;}
        }
        parent::initialize();
        if($table!='')
        {
            if(strpos($this->table,$pix)!==FALSE) {$this->setTable( $this->table);}
            else {$this->setTable( $this->prefix.$this->table);}
        }

    }
    public static function inIt($table='')
    {
        return $obj=new MasterModel($table);

    }

    function field($field)
    {
        parent::field($field);
        return $this;
    }

    function order($order)
    {
        parent::order($order);
        return $this;
    }
    function group($group)
    {
        parent::group($group);
        return $this;
    }
    function join($join, $condition = null, $type = 'LEFT')
    {
        parent::join($join, $condition, $type);
      return $this;
    }

    /**
     * 获取查询参数
     */
    function getOptions()
    {
       return  $this->options;
    }
    /**
     * 连表
     * @param array $join
     */
    function joinData($join=array())
    {
        $pix=config('prefix');
        if(!$join)return $this;
        foreach($join as $val)
        {
            if(is_array($val)&&$val[0]&&$val[1])
            {
                $val[0]=strpos($val[0],$pix)!==false?:$pix.$val[0];
                $this->join($val[0],$val[1],$val[2]);
            }
        }
        return $this;
    }
    /**
     * 获取数据条数
     * @param $where 条件
     * @param $group 分组
     * @param array $join 二维数组
     */
    function getCount($where=null,$group=null,$join=array())
    {
        if($where)$this->where($where);
        if($group)$this->group($group);
        $this->joinData($join);
       return  $this->count();
    }

    /**
     * 获取多条数据
     * @param null $where
     * @param null $order
     * @param null $group
     * @param array $join
     */
    public function getListData($where=null,$order=null,$group=null,$join=array(),$limit='')
    {

        if($where)$this->where($where);
        if($order)$this->order($order);
        if($group)$this->group($group);
        if($limit)$this->limit($limit);
        $this->joinData($join);
        $options=$this->parseExpress();//查询表达式
        $list=$this->select(null,$options);//执行查询

        $confd=$this->getFieldConf($options);

        $list=$this->_setListConf($list,$confd);
        return  $list;
    }

    /**
     * 获取一条数据
     * @param null $where
     * @param null $order
     * @param null $group
     * @param array $join
     * @return mixed
     */
    function getOne($where=null,$order=null,$group=null,$join=array())
    {
        if($where)$this->where($where);
        if($order)$this->order($order);
        if($group)$this->group($group);
        $this->joinData($join);
        $options=$this->parseExpress();//查询表达式
        $info= $this->find(null,$options);
        $confd=$this->getFieldConf($options);
        $list=$this->_setListConf(array($info),$confd);
        return $list[0];
    }
    /**
     * 插入
     * @param $data
     */
    function insertData($data)
    {
        if(config('admin_action_log'))
        {
            notfy_function($_SERVER['HTTP_HOST'].url('admin/index/adminActionLog',array('admin_id'=>session('admin_user.id')?:0,'action'=>1,'model'=>str_replace(config('prefix'),'',$this->table),'url'=>'')));
        }

        $is_tow_array=false;
        foreach($data as $row)
        {
            if(is_array($row)){$is_tow_array=true;break;}
        }
        if($is_tow_array)
        {
            $this->startTrans();
            try
            {
                $id=$this->saveAll($data, false);
                $this->commit();
                return $id;
            }catch (\Exception $e)
            {
                $this->rollback();
                return false;
            }

        }
        else
        {
            return $this->data($data)->isUpdate(false)->save();
        }
    }

    /**
     * 更新
     * @param null $where
     * @param $data
     */
    function updateData($data,$where=null)
    {
        if(config('admin_action_log'))
        {
            notfy_function($_SERVER['HTTP_HOST'].url('admin/index/adminActionLog',array('admin_id'=>session('admin_user.id')?:0,'action'=>2,'model'=>str_replace(config('prefix'),'',$this->table),'url'=>'')));
        }
        if($where) $this->where($where);
        return $this->update($data);
    }

    /**
     * 删除
     * @param null $where
     */
    function deleteData($where)
    {
        if(config('admin_action_log'))
        {
            notfy_function($_SERVER['HTTP_HOST'].url('admin/index/adminActionLog',array('admin_id'=>session('admin_user.id')?:0,'action'=>3,'model'=>str_replace(config('prefix'),'',$this->table),'url'=>'')));
        }
        return $this->where($where)->delete();
    }

    /**
     * 获取数据库字段配置
     * @param array $table
     */
    function getFieldConf($option=null)
    {

        $pix=config('prefix');
        $newarray=array();
        $table_alis=array();//别名表
        $field_alis=array();//别名字段
        $table=array();//表名
        if($option)
        {
            $tables=$this->getTable();
            $tables=explode(' ',$tables);
            if($tables[0])
            {
                $tbs=str_replace(config('prefix'),'',$tables[0]);
                if(isset($tables[1])&&$tables[1])$table_alis[$tables[1]]=$tbs;
                $tables=$tbs;
                $table[]=$tables;
            }
          //  $tables=$tables[0];$tables=str_replace(config('prefix'),'',$tables);

            if(is_array($option['join']))
            {
                foreach($option['join'] as $row)
                {
                    preg_match("/JOIN {$pix}(.*) ON/",$row,$tname);
                    if($tname[1])
                    {
                        $tname=explode(' ',$tname[1]);
                        $table[]=$tname[0];
                        $table_alis[$tname[1]]=$tname[0];
                    }
                }
            }
            if(is_array($option['field']))
            {
                foreach($option['field'] as $row)
                {
                    if(strpos($row,'as'))
                    {
                        $fid=explode(' as ',$row);
                        if($fid[0])
                        {
                            $ta=explode('.',$fid[0]);
                            if(isset($table_alis[$ta[0]])&&isset($ta[1])&&isset($fid[1]))
                            {
                                $field_alis[$table_alis[$ta[0]]][$ta[1]]=$fid[1];
                            }
                        }

                    }
                }
            }

        }
        else
        {
            return $newarray;
        }

        $filedvalid=Db::table(config('prefix').'field f')->field('f.field,f.type,f.extra,f.name,f.title,m.name as model_name')->where(array('m.name'=>array('in',implode(',',$table))))->join("{$pix}model m",'m.id=f.model_id','left')->select();

        foreach($filedvalid as $vals)
        {
            switch($vals['type'])
            {
                case  'select':
                case  'bool':
                case  'radio':
                case  'checkbox':
                $vals['extra']=array_filter(preg_split('/[\r\n]+/s', $vals['extra']));
                $news=array();
                foreach($vals['extra'] as $r)
                {
                    $n=explode(':',$r);
                    if(isset($n[0])&&isset($n[1]))$news[trim($n[0])]=trim($n[1]);
                }
                if(isset($field_alis[$vals['model_name']][$vals['field']]))
                {
                    $newarray[$field_alis[$vals['model_name']][$vals['field']]]=array('type'=>$vals['type'],'extra'=>$news,'name'=>$vals['name'],'title'=>$vals['title']);
                }
                else
                {
                    $newarray[$vals['field']]=array('type'=>$vals['type'],'extra'=>$news,'name'=>$vals['name'],'title'=>$vals['title']);
                }
                    break;
                default:

                    if(isset($field_alis[$vals['model_name']][$vals['field']]))
                    {
                        $newarray[$field_alis[$vals['model_name']][$vals['field']]]=array('type'=>$vals['type'],'name'=>$vals['name'],'title'=>$vals['title']);
                    }
                    else
                    {
                        $newarray[$vals['field']]=array('type'=>$vals['type'],'name'=>$vals['name'],'title'=>$vals['title']);
                    }
                    break;
            }
        }
        return $newarray;
    }

    /**
     * 加载字段配置
     * @param $list
     * @param $confd
     * @return mixed
     */
    private function _setListConf($list,$confd)
    {
        if(!isset($list[0])||!$list[0])return $list;
        foreach($list as &$row)
        {
            foreach($row as $key=>$val)
            {
                if(!isset($confd[$key]['type']))continue;
                switch($confd[$key]['type'])
                {
                    case 'date':
                        $row[$key.'_format']=date('Y-m-d',$val);
                        break;
                    case 'datetime':
                        $row[$key.'_format']=date('Y-m-d H:i:s',$val);
                        break;
                    case  'select':
                    case  'bool':
                    case  'radio':
                    case  'checkbox':
                        $row[$key.'_msg']=isset($confd[$key]['extra'][$val])?$confd[$key]['extra'][$val]:'';
                        break;

                }
               $this->_testInterface($row,$key,$val,$confd);
            }

        }
        return $list;
    }

    /**
     * 接口测试时调用
     * @param $row
     * @param $key
     * @param $val
     * @param $confd
     */
    private function _testInterface(&$row,$key,$val,$confd)
    {
        $is_interface=input('is_interface_request','');
        $is_interface_request_show=input('is_interface_request_show','');
        if($is_interface=='true')
        {
            $parematerlist=MasterModel::inIt('interface_paremater')->field('name')->getListData(array('face_id'=>input('test_interface_id')));
            foreach($parematerlist as $k=>$v)
            {
                if(input($v['name']))
                    MasterModel::inIt('interface_paremater')->updateData(array('values'=>input($v['name'])),array('face_id'=>input('test_interface_id'),'name'=>$v['name']));
            }

        }
        if($is_interface=='true'&&$is_interface_request_show=='true')
        {
            $row[$key]="{$val}（{$confd[$key]['name']} {$confd[$key]['title']}";
            //var_dump(trim($confd[$key]['extra']));exit;
            if(isset($confd[$key]['extra'])!=''){
                $string=array();
                foreach ($confd[$key]['extra'] as $keys=> $value){
                    $string[] = $keys.':'.$value;
                }
                $row[$key].=implode(',',$string);
            }
            $row[$key].='）';
        }

    }

}