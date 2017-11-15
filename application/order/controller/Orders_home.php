<?php
/**
 * Order
 * User: qls
 */
namespace app\order\controller;

use app\order\model\Numberrecord;
use app\order\model\Orderitem;
use Home\HomeController;
use My\MasterModel;
use think\Request;

class OrdersHome extends HomeController
{
    protected $model_name = 'orders';
    /**
     * @var \app\order\model\Numberrecord
     */
    protected $num_obj = '';

    /**
     * @var \app\order\model\Orderitem
     */
    protected $order_item_model='';
    function __construct()
    {
        parent::__construct();
        config('parent_temple', '');
    }

    function index()
    {
        $where = array('member_id'=>$this->member_info['id']);
        $field = '*';
        $order = 'id desc';
        $group = '';
        $join = array();
        $list = $this->getListData($this->model_name, $where, $field, $order, $group, $join);
        $list=$this->getOrderItemInfo($list);
        return view('index',['order_list'=> $list,'is_list'=> count($list),'member_info'=> $this->wx_user_info]);
    }

    function ajaxMemberOrderList(){
        $where = array('member_id'=>$this->member_info['id']);
        $field = '*';
        $order = 'id desc';
        $group = '';
        $join = array();
        $list = $this->getListData($this->model_name, $where, $field, $order, $group, $join);
        $list=$this->getOrderItemInfo($list);
        if($list){
            return $this->success('获取信息成功','',$list);
        }else{
            return $this->error('已到最后一页');
        }
    }

    /**
     * 获取订单明细
     * @param $list
     * @return mixed
     */
    private function getOrderItemInfo($list){
        foreach ($list as &$value){
            $value['order_item']=MasterModel::inIt('order_item o')->field('o.id,o.member_order_id,o.to_num,m.surplus_num,m.goods_name,m.img')->getListData(['o.order_id'=>$value['id'],'member_id'=>$this->member_info['id']],'id asc','',[['member_order m','m.id=o.member_order_id','left']]);
        }
        return $list;
    }

    /**
     * 手工选择
     * @return \think\response\View
     */
    function setOrderTitle(){
        $this->isAdministration();
        $list=MasterModel::inIt('nursing')->field('id,name,add_time')->getListData(['status'=>'1']);
        return view('set_order_title',['list'=>$list]);
    }

    /**
     * 创建服务订单
     * @return \think\response\View
     */
    function addOrder()
    {
        $this->isAdministration();
        if ($data = input('post.')) {
            if(empty($data['nursing_names']))$this->error('请选择服务');
            if(empty($data['nursing_ids']))$this->error('缺少必要参数');
            if(empty($data['order_sn']))$this->error('缺少订单号');
            if(empty($data['goods_list']))$this->error('缺少商品信息');
            $order_id=MasterModel::inIt('orders')->insertData(['status'=>'1','member_id'=>$this->member_info['id'],'add_time'=>time(),'order_sn'=>$data['order_sn'],'order_title'=>$data['nursing_names'],'nursing_id'=>$data['nursing_ids']]);
            foreach ($data['goods_list'] as &$value){
                $value['order_id']=$order_id;
            }
            $order_item=MasterModel::inIt('order_item')->insertData($data['goods_list']);
            if($order_item){
                return $this->success('订单创建成功',url('getOrderInfo',['order_id'=>$order_id]));
            }else{
                return $this->error('订单创建失败');
            }
        } else {
            $nursing_ids=input('nursing_ids','');
            if(empty($nursing_ids))return $this->error('参数错误');
            $nursing_names=MasterModel::inIt('nursing')->where(['id'=>['in',$nursing_ids]])->column('abbreviation');
            if(empty($nursing_names))return  $this->error('手工信息不存在');
            $nursing_names=implode(',',$nursing_names);
            if (!is_object($this->num_obj)) {
                $this->num_obj = new Numberrecord();
            }
            $order_sn = $this->num_obj->getAddNum('1');
            $this->creationWxModel();
            $js_config=$this->wx_model->get_js_config(1);
            return view('add_order',['order_sn'=>$order_sn,'nursing_ids'=>$nursing_ids,'js_config'=>$js_config,'nursing_names'=>$nursing_names]);
        }
    }

    function memberAffirmOrderInfo(){
        $order_id=input('order_id');
        if(empty($order_id))return $this->error('缺少必要参数');
        $order_info=MasterModel::inIt('orders')->getOne(['id'=>$order_id,'member_id'=>$this->member_info['id']]);
        if(empty($order_info))return $this->error('订单信息不存在');
        if($order_info['status']!='1')return $this->error('当前订单已确认',url('index'));
        if(Request::instance()->isPost()){
            if(empty($this->order_item_model) || !is_object($this->order_item_model)){
                $this->order_item_model= new Orderitem();
            }
            $this->order_item_model->setMemberOrderGoodsNum($order_id);
            $row=MasterModel::inIt('orders')->updateData(['status'=>'2'],['id'=>$order_id]);
            if($row===false){
                return $this->error('确实失败');
            }else{
                return $this->success('确认成功',url('index'));
            }
        }else{
            $order_item=MasterModel::inIt('order_item o')->field('o.id,o.member_order_id,o.to_num,m.surplus_num,m.goods_name,m.img')->getListData(['o.order_id'=>$order_id,'member_id'=>$this->member_info['id']],'id asc','',[['member_order m','m.id=o.member_order_id','left']]);
            return view('member_affirm_order_info',['order_info'=>$order_info,'order_item'=>$order_item]);
        }
    }

    function getOrderInfo(){
        $this->isAdministration();
        $order_id=input('order_id');
        $order_info=MasterModel::inIt('orders')->getOne(['id'=>$order_id,'member_id'=>$this->member_info['id']]);
        if(empty($order_info))return $this->error('订单信息不存在');
        return view('get_order_info',['order_info'=>$order_info,'affirm_url'=>url('memberAffirmOrderInfo',['order_id'=>$order_id],true,true)]);
    }

    /**
     * 查询当前存储数据
     */
    function getMyGoodsInfo(){
        $isbn = input('isbn');
        if (empty($isbn)) return $this->error('参数错误');
        $goods_info = MasterModel::inIt('member_order m')->field('m.id,m.goods_id,m.goods_name,g.price,g.goods_logo,m.surplus_num')->getOne(['g.isbn' => $isbn,'m.member_id'=>$this->member_info['id']],'m.surplus_num desc','',[['goods g','g.id=m.goods_id','left']]);
        if (empty($goods_info))return $this->error('商品不存在');
        $this->success('获取商品信息成功', '', $goods_info);
    }



}