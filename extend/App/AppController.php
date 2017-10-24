<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/18
 * Time: 15:33
 */
namespace App;
use My\MasterController;
use My\MasterModel;

class AppController extends MasterController {
    protected $page_no = 1;
    protected $page_size = 20;
    protected $start = 0;
    protected $uid;
    protected $token;
    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Chongqing');
        $this->uid = input('uid', '');
        $this->token = input('token', '');
        if (input('page') != '' && is_numeric(input('page'))) {
            $this->page_no = intval(input('page'));
        }
        if (input('pageSize') != '' && is_numeric(input('pageSize'))) {
            $this->page_size = intval(input('pageSize'));
        }
        if ($this->page_no > 1) {
            $this->start = $this->page_size * ($this->page_no - 1);
        }
    }

    /**
     * 分页查询
     * @param \My\表名 $tableName
     * @param null $where
     * @param string $field
     * @param null $order
     * @param null $group
     * @param array $join
     * @return mixed
     */
    function getListData($tableName,$where=null,$field='*',$order=null,$group=null,$join=array())
    {
        if(is_object($tableName))
        {
            $confd=$tableName->getFieldConf();
            $list=$tableName->select();
            //$list= $this->_setListConf($list,$confd);
            return $list;
        }
        else
        {
            $model=\My\MasterModel::inIt($tableName);
            $limit= $this->start.','.$this->page_size;
            $count=$model->getCount($where,$group,$join);
            $list=$model->field($field)->getListData($where,$order,$group,$join,$limit);
            $pages=floor($count/$this->page_size);
            if($count%$this->page_size!=0)$pages+=1;
            return array('totalPage'=>$pages,'page'=>$this->page_no,'pageSize'=>$this->page_size,'data'=>$list);
        }
    }


    /**
     * 验证token
     */
    function validtoken()
    {
        if($this->uid==100)return true;
        //if(empty($this->uid)||empty($this->token))exit(error('缺少用户标识或token',5));
        //  if(YYF('token_'.$this->uid)!= $this->token)exit(error('token验证失败',5));
    }

    /**
     * 文件上传
     */
    public function upload_file()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('files');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(YANYU_ROOT . '/upload');
        if($info){
            exit(success('上传成功',$info->getSaveName()?:''));
        }else{
            // 上传失败获取错误信息
          exit(error('上传失败',$file->getError()));
        }
    }
    private function _setListConf($list,$confd)
    {
        foreach($list as &$row)
        {
            foreach($row as $key=>$val)
            {
                switch($confd[$key]['type'])
                {
                    case  'select':
                    case  'bool':
                    case  'radio':
                    case  'checkbox':
                        $row[$key.'_msg']=isset($confd[$key]['extra'][$val])?$confd[$key]['extra'][$val]:'';
                        break;

                }
            }

        }
        return $list;
    }

    /**
     * 输出字段信息
     * @param $modelName
     * @param $field
     * @param $id
     */
    function getModelFiled($modelName,$field,$id)
    {
        if(!is_string($field)||strpos($field,','))exit(error('字段格式错误'));
        $info=MasterModel::inIt($modelName)->field($field)->getOne(array('id'=>$id));
        exit("<html> <meta charset='utf-8'><body>{$info[$field]}</body></html>");
    }

}