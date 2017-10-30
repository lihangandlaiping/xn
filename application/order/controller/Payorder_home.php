<?php
/**
 * Payorder
 * User: qls
 */
namespace app\order\controller;
use app\order\model\Numberrecord;
use Home\HomeController;
use My\MasterModel;
use think\Request;

class PayorderHome extends HomeController
{
    protected $model_name='pay_order';
    function __construct()
    {
        parent::__construct();
        config('parent_temple', '');
    }

    /**
     * @var \app\order\model\Numberrecord
     */
    protected  $num_obj='';

    /**
     * 添加
     */
    function addGoodsOrder(){
        if(Request::instance()->isPost()){

        }else{
            if(!is_object($this->num_obj)){
                $this->num_obj=new Numberrecord();
            }
            $pay_order_sn=$this->num_obj->getAddNum('2');
            return view('add_goods_order',['order_sn'=>$pay_order_sn]);
        }
    }

    /**
     * 获取商品详情
     */
    function getGoodsInfo(){
        $isbn=input('isbn');
        if(empty($isbn))$this->error('参数错误');
        $goods_info=MasterModel::inIt('goods')->field('id,goods_name,price,goods_logo')->getOne(['isbn'=>$isbn]);
        if(empty($goods_info))$this->error('商品不存在');
        $this->success('获取商品信息成功','',$goods_info);
    }

}