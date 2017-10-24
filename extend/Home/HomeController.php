<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/1
 * Time: 17:19
 */
namespace Home;
use My\MasterController;
use My\MasterModel;
use think\Config;

class HomeController extends MasterController
{
    protected $model_name='';//数据库表名
    function __construct()
    {
        parent::__construct();
        //设置配置
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
            $conf=cache('config_h'.MODULE_NAME)?:array();
            if(!$conf)
            {
                $module=MasterModel::inIt('module')->field('id')->getOne(array('name'=>MODULE_NAME));
                $where="is_show in ('1','4') ";
                if($module['id'])
                    $where.="and (module_id=0 or module_id={$module['id']})";
                else $where.="and module_id=0";
                $conf=MasterModel::inIt('config c')->field('name,content')->getListData($where);
                cache('config_h'.MODULE_NAME,$conf);
            }
            foreach($conf as $a)
            {
                config($a['name'],$a['content']?:'');
            }
            //设置后台操作日志
            $configs=MasterModel::inIt('config')->field('content')->getOne(array('name'=>'admin_action_log'));
            config('admin_action_log',$configs['content']);
        }
    }
    /**
     * 获取分页数据
     * @param $tableName 表名、或数据查询对象
     * @param $where 条件
     * @param $order 排序
     * @param $group 分组
     * @param $join
     */
    function getListData($tableName,$where=null,$field='*',$order=null,$group=null,$join=array())
    {
        $model=MasterModel::inIt($tableName);
        $count=$model->field($field)->getCount($where,$group,$join);
        $page=new \think\Page($count,$this->pageSize);
        $p=$page->show();
        $this->assign('_p',$p);
        $limit= $page->firstRow.','.$page->listRows;
        $list=$model->field($field)->getListData($where,$order,$group,$join,$limit);
        return $list;
    }



}