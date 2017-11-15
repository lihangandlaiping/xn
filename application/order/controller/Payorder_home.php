<?php
/**
 * Payorder
 * User: qls
 */
namespace app\order\controller;

use app\order\model\Memberorder;
use app\order\model\Numberrecord;
use Home\HomeController;
use My\MasterModel;
use think\Request;

class PayorderHome extends HomeController
{
    protected $model_name = 'pay_order';

    /**
     * @var \app\order\model\Memberorder
     */
    protected $member_order_obj='';
    function __construct()
    {
        parent::__construct();
        config('parent_temple', '');
    }

    /**
     * @var \app\order\model\Numberrecord
     */
    protected $num_obj = '';

    /**
     * 添加
     */
    function addGoodsOrder()
    {
        $this->isAdministration();
        if (Request::instance()->isPost()) {
            $data=input('post.');
            if(empty($data['goods_list']))return $this->error('请选择订单商品');
            if(empty($data['order_sn']))return $this->error('缺少必要参数');
            if (!is_object($this->num_obj)) {
                $this->member_order = new Memberorder();
            }
            $add_time=time();
            foreach ($data['goods_list'] as $order_goods){
                //添加订单
                $id=MasterModel::inIt('pay_order')->insertData(['goods_id'=>$order_goods['id'],'goods_name'=>$order_goods['goods_name'],'img'=>$order_goods['goods_logo'],'price'=>$order_goods['price'],'pay_num'=>$order_goods['num'],'add_time'=>$add_time,'pay_status'=>'2','status'=>'1','amount_money'=>$data['total_money'],'order_sn'=>$data['order_sn'],'member_id'=>$this->member_info['id']]);
            }
            return $this->success('订单创建成功',url('getOrderInfo',['order_sn'=>$data['order_sn'],'type'=>'1']));
        } else {
            if (!is_object($this->num_obj)) {
                $this->num_obj = new Numberrecord();
            }
            $pay_order_sn = $this->num_obj->getAddNum('2');
            $this->creationWxModel();
            $js_config=$this->wx_model->get_js_config(1);
            return view('add_goods_order', ['order_sn' => $pay_order_sn,'js_config'=>$js_config]);
        }
    }

    /**
     * 获取商品详情
     */
    function getGoodsInfo()
    {
        $this->isAdministration();
        $isbn = input('isbn');
        if (empty($isbn))return  $this->error('参数错误');
        $goods_info = MasterModel::inIt('goods')->field('id,goods_name,price,goods_logo')->getOne(['isbn' => $isbn]);
        if (empty($goods_info))return $this->error('商品不存在');
        $this->success('获取商品信息成功', '', $goods_info);
    }

    /**
     * 会员确认订单详情
     */
    function memberAffirmOrderInfo(){
        $order_sn=input('order_sn','');
        if(empty($order_sn))return $this->error('订单号不合法');
        $goods_list=MasterModel::inIt('pay_order')->field('id,order_sn,price,amount_money,goods_name,pay_num,goods_id,img')->getListData(['member_id'=>$this->member_info['id'],'order_sn'=>$order_sn]);
        if(empty($goods_list))return $this->error('当前订单不存在');
        if(Request::instance()->isPost()){
            $row=MasterModel::inIt('pay_order')->updateData(['status'=>'2','pay_status'=>'2'],['status'=>'1','member_id'=>$this->member_info['id'],'order_sn'=>$order_sn]);
            if($row){
                if(empty($this->member_order_obj) || !is_object($this->member_order_obj)){
                    $this->member_order_obj=new Memberorder();
                }
                $ids=$this->member_order_obj->setUserGoodsInfo($goods_list,$this->member_info['id']);
            }
            if(empty($ids)){
                return $this->error('当前订单已确认');
            }else{
                return $this->success('订单确认成功');
            }
        }else{
            $order_info=['order_sn'=>$goods_list[0]['order_sn'],'amount_money'=>$goods_list[0]['amount_money']];
            return view('member_affirm_order_info', ['goods_list' => $goods_list,'order_info'=>$order_info]);
        }
    }

    /**
     * 获取订单详情
     */
    function getOrderInfo(){
        $order_sn=input('order_sn','');
        if(empty($order_sn))return $this->error('缺少必要参数');
        $status=MasterModel::inIt('pay_order')->where(['member_id'=>$this->member_info['id'],'order_sn'=>$order_sn])->value('status');
        if(empty($status))return $this->error('当前订单不存在');
        $affirm_url=url('memberAffirmOrderInfo',['order_sn'=>$order_sn],true,true);
        return view('get_order_info', ['affirm_url' => $affirm_url,'order_sn'=>$order_sn,'status'=>$status]);
    }

}