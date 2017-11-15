<?php
/**
 * Memberorder
 * User: qls
 */
namespace app\order\controller;

use Home\HomeController;
use My\MasterModel;

class MemberorderHome extends HomeController
{
    protected $model_name = 'member_order';

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
        $where = array('member_id'=>$this->member_info['id']);
        $field = '*';
        $order = 'update_time desc';
        $group = '';
        $join = array();
        $list = $this->getListData($this->model_name, $where, $field, $order, $group, $join);
        return view('index',['goods_list'=> $list,'is_list'=> count($list),'member_info'=> $this->wx_user_info]);
    }

    function ajaxMemberOrderList(){
        $where = array('member_id'=>$this->member_info['id']);
        $field = '*';
        $order = 'update_time desc';
        $group = '';
        $join = array();
        $list = $this->getListData($this->model_name, $where, $field, $order, $group, $join);
        if($list){
            return $this->success('获取信息成功','',$list);
        }else{
            return $this->error('已到最后一页');
        }

    }


}