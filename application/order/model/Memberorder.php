<?php namespace app\order\model;

use My\MasterModel;

class Memberorder extends MasterModel
{
    function __construct()
    {
        parent::__construct('member_order');
    }
        /**
     * 获取数据条数
     * @param $where 条件
     * @param $group 分组
     * @param array $join 二维数组
     */
    function getCount($where=null,$group=null,$join=array())
    {
        return parent::getCount($where,$group,$join);
    }

    /**
     * 获取多条数据
     * @param null $where
     * @param null $order
     * @param null $group
     * @param array $join
     */
    public function getListData($where=null,$order=null,$group=null,$join=array(),$limit='')
    {
        return parent::getListData($where,$order,$group,$join,$limit);
    }

    /**
     * 获取一条数据
     * @param null $where
     * @param null $order
     * @param null $group
     * @param array $join
     * @return mixed
     */
    function getOne($where=null,$order=null,$group=null,$join=array())
    {
       return parent::getOne($where,$order,$group,$join);
    }
    /**
     * 插入
     * @param $data
     */
    function insertData($data)
    {
        return parent::insertData($data);
    }

    /**
     * 更新
     * @param null $where
     * @param $data
     */
    function updateData($data,$where=null)
    {
       return parent::updateData($data,$where);
    }

    /**
     * 删除
     * @param null $where
     */
    function deleteData($where)
    {
        return parent::deleteData($where);
    }

    /**
     * 添加库存数量
     * @param $goods_list
     * @param $member_id
     * @return array
     */
    function setUserGoodsInfo($goods_list,$member_id){
        $ids=[];
        foreach ($goods_list as $goods){
            $member_order_id=MasterModel::inIt('member_order')->where(['goods_id'=>$goods['goods_id'],'member_id'=>$member_id])->value('id');
            if(empty($member_order_id)){
                $row=['member_id'=>$member_id,'goods_id'=>$goods['goods_id'],'goods_name'=>$goods['goods_name'],'img'=>$goods['img'],'surplus_num'=>$goods['pay_num'],'add_time'=>time(),'update_time'=>time()];
                $ids[]=MasterModel::inIt('member_order')->insertData($row);
            }else{
                $ids[]=$member_order_id;
                MasterModel::inIt('member_order')->where(['goods_id'=>$goods['goods_id'],'member_id'=>$member_id])->setInc('surplus_num',$goods['pay_num']);
                MasterModel::inIt('member_order')->updateData(['update_time'=>time()],['goods_id'=>$goods['goods_id'],'member_id'=>$member_id]);
            }
        }
        return $ids;
    }
}

?>