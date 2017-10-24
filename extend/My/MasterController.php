<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/1
 * Time: 17:19
 */
namespace My;


class MasterController extends \think\Controller
{
    protected $pageSize=20;
    function __construct()
    {
        parent::__construct();


    }

    /**
     * 获取分页数据
     * @param $tableName 表名、或数据查询对象
     * @param $where 条件
     * @param $order 排序
     * @param $group 分组
     * @param $join
     */
    protected function getListData($tableName,$where=null,$field='*',$order=null,$group=null,$join=array())
    {
            $_GET=input();
        if(is_object($tableName))
        {
            if($where)$tableName->where($where);
            if($where)$tableName->order($order);
            if($where) $tableName->group($group);
            if($where) $tableName->joinData($join);
            $newtablename=clone $tableName;
            $count=$tableName->getCount();
            $page=new \think\Page($count,$this->pageSize);
            $p=$page->show();
            $this->assign('_p',$p);
            $limit= $page->firstRow.','.$page->listRows;
            $list=$newtablename->getListData($where,$order,$group,$join,$limit);
            return $list;
        }
        else
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

    /**
     * js级联效果
     */
    public function qlsJsCascade($model='',$column='',$parentId=0,$filed=array())
    {
        if(!$model)$this->error('请提交数据库表名');
        if(!$column)$this->error('请提交关联字段名');
        $fils='';
        $filedd=(array)$filed;
        foreach($filedd as $key=>$val)
        {
            if($fils!='')$fils.=',';
            $fils.="{$val} as {$key}";
        }
        $models=MasterModel::inIt($model)->field($fils)->getListData(array($column=>$parentId));
        $this->success('获取成功','',$models);
    }


}