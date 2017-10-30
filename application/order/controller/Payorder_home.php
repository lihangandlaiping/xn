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
    protected $member_info = ['id' => '4'];

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
        if (Request::instance()->isPost()) {
            $data=input('post.');
            if(empty($data['goods_list']))$this->error('请选择订单商品');
            if(empty($data['order_sn']))$this->error('缺少必要参数');
            if (!is_object($this->num_obj)) {
                $this->member_order = new Memberorder();
            }
            $add_time=time();
            foreach ($data['goods_list'] as $order_goods){
                //添加订单
                $id=MasterModel::inIt('pay_order')->insertData(['goods_id'=>$order_goods['id'],'goods_name'=>$order_goods['goods_name'],'img'=>$order_goods['goods_logo'],'price'=>$order_goods['price'],'pay_num'=>$order_goods['num'],'add_time'=>$add_time,'pay_status'=>'2','status'=>'1','amount_money'=>$data['total_money'],'order_sn'=>$data['order_sn'],'member_id'=>$this->member_info['id']]);
            }
            $this->success('订单创建成功',url('getOrderInfo',['order_sn'=>$data['order_sn'],'type'=>'1']));
        } else {
            if (!is_object($this->num_obj)) {
                $this->num_obj = new Numberrecord();
            }
            $pay_order_sn = $this->num_obj->getAddNum('2');
            return view('add_goods_order', ['order_sn' => $pay_order_sn]);
        }
    }

    /**
     * 获取商品详情
     */
    function getGoodsInfo()
    {
        $isbn = input('isbn');
        if (empty($isbn)) $this->error('参数错误');
        $goods_info = MasterModel::inIt('goods')->field('id,goods_name,price,goods_logo')->getOne(['isbn' => $isbn]);
        if (empty($goods_info)) $this->error('商品不存在');
        $this->success('获取商品信息成功', '', $goods_info);
    }

    /**
     * 会员确认订单详情
     */
    function memberAffirmOrderInfo(){

    }

    /**
     * 获取订单详情
     */
    function getOrderInfo(){
        $order_sn=input('order_sn','');
        if(empty($order_sn))$this->error('缺少必要参数');
        $status=MasterModel::inIt('pay_order')->where(['member_id'=>$this->member_info['id'],'order_sn'=>$order_sn])->value('status');
        if(empty($status))$this->error('当前订单不存在');
        $affirm_url=url('memberAffirmOrderInfo',['order_sn'=>$order_sn],true,true);
        return view('get_order_info', ['affirm_url' => $affirm_url,'order_sn'=>$order_sn,'status'=>$status]);
    }

}