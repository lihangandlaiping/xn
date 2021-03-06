<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/1
 * Time: 17:19
 */
namespace Home;
use app\member\model\Member;
use My\MasterController;
use My\MasterModel;
use think\Config;
use think\Request;
use wx_model\Weixinmodel;

class HomeController extends MasterController
{
    protected $model_name='';//数据库表名
    /**
     * 微信对象
     * @var \wx_model\Weixinmodel
     */
    protected $wx_model='';
    protected $member_info = '';
    protected $wx_user_info='';
    protected $member_model='';
    function __construct()
    {
        parent::__construct();
        //设置配置
        $this->_redyConfig();
        //分页设置
        $this->pageSize=config('admin_page_size')?:$this->pageSize;
        $this->getUserWxInfo();
    }

    /**
     * 获取微信信息
     */
    protected function getUserWxInfo(){
        $this->wx_user_info=session('wx_user_info');
        $this->member_info=session('member_info');
        if(empty($this->wx_user_info)){
            $this->creationWxModel();
            $this->wx_user_info=$this->wx_model->authorize();
            if(empty($this->wx_user_info))$this->error('获取信息失败');
            session('wx_user_info',$this->wx_user_info);
        }
        if(empty($this->member_info)){
            if(empty($this->member_model) || !is_object($this->member_model)){
                $this->member_model=new Member();
            }
            $this->member_info=$this->member_model->getMemberInfo($this->wx_user_info);
            session('member_info',$this->member_info);
        }
        if(empty($this->member_info))$this->error('会员信息不存在');
    }

    /**
     * 创建微信操作对象
     */
    protected function creationWxModel(){
        if(empty($this->wx_model) || !is_object($this->wx_model)){
            vendor('weixin/Weixinmodel');
            $wx_config=config('wx_config');
            $this->wx_model=new Weixinmodel($wx_config['appid'],$wx_config['appsecret']);
        }
    }

    /**
     * 配置加载
     */
    private function _redyConfig()
    {
        //配置设置
        if(!Config::has('admin_action_log'))
        {
            $conf=cache('config_h'.MODULE_NAME)?:array();
            if(!$conf)
            {
                $module=MasterModel::inIt('module')->field('id')->getOne(array('name'=>MODULE_NAME));
                $where="is_show in ('1','4') ";
                if($module['id'])
                    $where.="and (module_id=0 or module_id={$module['id']})";
                else $where.="and module_id=0";
                $conf=MasterModel::inIt('config c')->field('name,content')->getListData($where);
                cache('config_h'.MODULE_NAME,$conf);
            }
            foreach($conf as $a)
            {
                config($a['name'],$a['content']?:'');
            }
            //设置后台操作日志
            $configs=MasterModel::inIt('config')->field('content')->getOne(array('name'=>'admin_action_log'));
            config('admin_action_log',$configs['content']);
        }
    }
    /**
     * 获取分页数据
     * @param $tableName 表名、或数据查询对象
     * @param $where 条件
     * @param $order 排序
     * @param $group 分组
     * @param $join
     */
    function getListData($tableName,$where=null,$field='*',$order=null,$group=null,$join=array())
    {
        $model=MasterModel::inIt($tableName);
        $count=$model->field($field)->getCount($where,$group,$join);
        $page=new \think\Page($count,$this->pageSize);
        $p=$page->show();
        $this->assign('_p',$p);
        $limit= $page->firstRow.','.$page->listRows;
        $list=$model->field($field)->getListData($where,$order,$group,$join,$limit);
        return $list;
    }

    /**
     * 验证权限
     */
    protected function isAdministration(){
        if($this->member_info['is_administration']!=1)$this->error('当前用户无权限');
    }

    /**
     * 操作成功跳转的快捷方法
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3)
    {
        $result = [
            'code' => '1',
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        if(Request::instance()->isAjax()){
            exit(json_encode($result));
        }else{
            return view('public/index/success',$result);
        }
    }

    /**
     * 操作错误跳转的快捷方法
     */
    function error($msg = '', $url = null, $data = '', $wait = 3,$parentwindow=0)
    {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait
        ];
        if(Request::instance()->isAjax()){
            exit(json_encode($result));
        }else{
            return view('public/index/error',$result);
        }
    }



}