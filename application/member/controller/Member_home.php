<?php namespace app\member\controller;

use Home\HomeController;
use My\MasterModel;


class Member_home extends HomeController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name = 'member';
    }



}

?>