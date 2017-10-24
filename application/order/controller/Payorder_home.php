<?php
/**
 * Payorder
 * User: qls
 */
namespace app\order\controller;
use app\order\model\Numberrecord;
use Home\HomeController;
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
            return view('index');
        }
    }

}