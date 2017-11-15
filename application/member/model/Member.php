<?php namespace app\member\model;

use My\MasterModel;

class Member extends MasterModel
{
    function __construct()
    {
        parent::__construct('member');
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
     * 获取会员信息
     * @param $wx_user_info
     * @return array|mixed
     */
    function getMemberInfo($wx_user_info){
        $member_info=MasterModel::inIt('member')->getOne(['openid'=>$wx_user_info['openid']]);
        if(empty($member_info)){
            $member_info=['nickname'=>$wx_user_info['nickname'],'head_pic'=>$wx_user_info['headimgurl'],'openid'=>$wx_user_info['openid'],'mobile'=>'','sex'=>$wx_user_info['sex'],'money'=>'0','points'=>'0','is_administration'=>'2','reg_time'=>time()];
            $member_info['id']=MasterModel::inIt('member')->insertData($member_info);
        }
        return $member_info;
    }
}

?>