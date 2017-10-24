<?php
/**
 * Order
 * User: qls
 */
namespace app\order\controller;
use Home\HomeController;
use My\MasterModel;

class OrdersHome extends HomeController
{
    protected $model_name='orders';
    function __construct()
    {
        parent::__construct();
        config('parent_temple', '');
    }

    function addOrder(){

    }

}