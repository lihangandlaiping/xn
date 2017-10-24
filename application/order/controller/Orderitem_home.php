<?php
/**
 * Orderitem
 * User: qls
 */
namespace app\order\controller;
use Home\HomeController;
use My\MasterModel;

class OrderitemHome extends HomeController
{
    protected $model_name='order_item';
    function __construct()
    {
        parent::__construct();
        config('parent_temple', '');
    }
     /**
     * 数据列表 $_p 为分页数据
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function index()
    {
        $where=array();$field='*';$order='';$group='';$join=array();
       $list= $this->getListData($this->model_name,$where,$field,$order,$group,$join);
        $this->display('list',$list);
        return view('index');
    }

    /**
     * 单条数据详情
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function show()
    {
        $where=array();$field='*';$order='';$group='';$join=array();
        $info=MasterModel::inIt($this->model_name)->field($field)->getOne($where,$order,$group,$join);
        $this->display('info',$info);
        return view('details');
    }

}