<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/18
 * Time: 12:00
 */
namespace app\admin\model;
use My\MasterModel;

class Interfaced{
    function __construct()
    {

    }

    /**
     * 生成接口文件
     * @param $list
     * @param $filepath
     */
    function createInterface($list,$filepath)
    {
        foreach($list as $row)
        {
            $paremater=MasterModel::inIt('interface_paremater')->getListData(array('face_id'=>$row['id']));
            $pmsg='';
            $parem='';
            $valid='';
            foreach($paremater as $pare)
            {
                $pmsg.="*{$pare['name']} {$pare['title']}\r\n";
                if($parem!='')$parem.=',';
                $parem.='$'."{$pare['name']}='{$pare['default']}'";
                switch(intval($pare['sy_vilid']))
                {
                    case 2:$valid.="if(".'$'."{$pare['name']}=='')exit(error('{$pare['error_tip']}'));\r\n";break;
                    case 3:$valid.="if(!checkmobile(".'$'."{$pare['name']}))exit(error('{$pare['error_tip']}'));\r\n";break;
                    case 4:$valid.="if(!checkemail(".'$'."{$pare['name']}))exit(error('{$pare['error_tip']}'));\r\n";break;
                    case 5:$valid.="if(!is_numeric(".'$'."{$pare['name']}))exit(error('{$pare['error_tip']}'));\r\n";break;
                    case 6:$valid.="if(!checkjson(".'$'."{$pare['name']}))exit(error('{$pare['error_tip']}'));\r\n";break;
                }
            }
            $action=MasterModel::inIt('interface_action')->getListData(array('face_id'=>$row['id']),'sort asc');
            $actionstr='';
            $output='';
            foreach($action as $ac)
            {
                switch($ac['type'])
                {
                    case 1:
                        $actionstr.=$ac['custom']."\r\n";
                        break;
                    case 2:
                        $actionstr.=$this->typeInterfaceSelect($ac,$ac['type']);
                        break;
                    case 3:
                        $actionstr.=$this->typeInterfaceInsert($ac);
                        break;
                    case 4:
                        $actionstr.= $this->typeInterfaceUpdate($ac);
                        break;
                    case 5:
                        $actionstr.=$this->typeInterfaceSelect($ac,$ac['type']);
                        break;
                    case 6:
                        $actionstr.=$this->typeInterfaceSelect($ac,$ac['type']);
                        break;
                }
                if($ac['bianlian_name']&&$ac['is_output']==1)
                {
                    $output="exit(success('操作成功',".'$'."{$ac['bianlian_name']}));";
                }
            }
            $token=$row['is_token_valid']==1?'parent::validtoken();':'';
            $str="/*start_{$row['face_name']}*/\r\n/**\r\n*{$row['face_title']}\r\n{$pmsg}*/\r\nfunction {$row['face_name']}({$parem})\r\n{\r\n{$token}\r\n{$valid}{$actionstr}{$output}\r\n}\r\n/*end_{$row['face_name']}*/\r\n";
            file_put_contents($filepath,$str,FILE_APPEND);
        }
        file_put_contents($filepath,'}',FILE_APPEND);
    }

    /**
     * 更新一条接口
     * @param $row
     */
    function updateInterface($row,$filepath)
    {
        $paremater=MasterModel::inIt('interface_paremater')->getListData(array('face_id'=>$row['id']));
        $pmsg='';
        $parem='';
        $valid='';
        foreach($paremater as $pare)
        {
            $pmsg.="*{$pare['name']} {$pare['title']}\r\n";
            if($parem!='')$parem.=',';
            $parem.='$'."{$pare['name']}='{$pare['default']}'";
            switch(intval($pare['sy_vilid']))
            {
                case 2:$valid.="if(".'$'."{$pare['name']}=='')exit(error('{$pare['error_tip']}'));\r\n";break;
                case 3:$valid.="if(!checkmobile(".'$'."{$pare['name']}))exit(error('{$pare['error_tip']}'));\r\n";break;
                case 4:$valid.="if(!checkemail(".'$'."{$pare['name']}))exit(error('{$pare['error_tip']}'));\r\n";break;
                case 5:$valid.="if(!is_numeric(".'$'."{$pare['name']}))exit(error('{$pare['error_tip']}'));\r\n";break;
                case 6:$valid.="if(!checkjson(".'$'."{$pare['name']}))exit(error('{$pare['error_tip']}'));\r\n";break;
            }
        }
        $action=MasterModel::inIt('interface_action')->getListData(array('face_id'=>$row['id']),'sort asc');
        $actionstr='';
        $output='';
        foreach($action as $ac)
        {
            switch($ac['type'])
            {
                case 1:
                    $actionstr.=$ac['custom']."\r\n";
                    break;
                case 2:
                    $actionstr.=$this->typeInterfaceSelect($ac,$ac['type']);
                    break;
                case 3:
                    $actionstr.=$this->typeInterfaceInsert($ac);
                    break;
                case 4:
                    $actionstr.= $this->typeInterfaceUpdate($ac);
                    break;
                case 5:
                    $actionstr.=$this->typeInterfaceSelect($ac,$ac['type']);
                    break;
                case 6:
                    $actionstr.=$this->typeInterfaceSelect($ac,$ac['type']);
                    break;
            }
            if($ac['bianlian_name']&&$ac['is_output']==1)
            {
                $output="exit(success('操作成功',".'$'."{$ac['bianlian_name']}));";
            }
        }
        $token=$row['is_token_valid']==1?'parent::validtoken();':'';
        $str="/*start_{$row['face_name']}*/\r\n/**\r\n*{$row['face_title']}\r\n{$pmsg}*/\r\nfunction {$row['face_name']}({$parem})\r\n{\r\n{$token}\r\n{$valid}{$actionstr}{$output}\r\n}\r\n/*end_{$row['face_name']}*/\r\n";
        $newstr=file_get_contents($filepath);
        if(preg_match('/\/\*start_'.$row['face_name'].'\*\/([\s\S.]*)\/\*end_'.$row['face_name'].'\*\//',$newstr))
        {
            $str=preg_replace('/\/\*start_'.$row['face_name'].'\*\/([\s\S.]*)\/\*end_'.$row['face_name'].'\*\//',$str,$newstr);
            file_put_contents($filepath,$str);
        }
        else
        {
            $bool=strripos($newstr,'}');
            if($bool)
            {
                $strs=mb_substr($newstr,0,$bool-1);
                $strs.=$str."\r\n }\r\n";
                file_put_contents($filepath,$strs);
            }
        }
    }
    /**
     * 生成修改代码
     * @param $ac
     * @param string $actionstr
     * @return string
     */
    function typeInterfaceUpdate($ac,$actionstr='')
    {
        if($ac['where'])
        {
            $datas=unserialize($ac['data_bianlian_name']);
            if($ac['bianlian_name'])$actionstr.='$'."{$ac['bianlian_name']}=";
            $actionstr.="MasterModel::inIt('{$datas['tableName']}')->updateData(".'$'."{$datas['data']},".'$'."{$ac['where']});\r\n";
        }
        return $actionstr;
    }
    /**
     * 新增
     * @param $ac
     * @param string $actionstr
     * @return string
     */
    function typeInterfaceInsert($ac,$actionstr='')
    {
        $datas=unserialize($ac['data_bianlian_name']);
        if($ac['bianlian_name'])$actionstr.='$'."{$ac['bianlian_name']}=";
        $actionstr.="MasterModel::inIt('{$datas['tableName']}')->insertData(".'$'."{$datas['data']});\r\n";
        return $actionstr;
    }
    /**
     * 查询
     * @param $ac
     * @param int $type
     * @param string $actionstr
     * @return string
     */
    function typeInterfaceSelect($ac,$type=5,$actionstr='')
    {
        $relations=unserialize($ac['table_relation']);

        if($relations[0])
        {
            if($type!=6&&$ac['bianlian_name'])
            {
                $actionstr.='$'."{$ac['bianlian_name']}=";
            }

            $actionstr.="MasterModel::inIt('{$relations[0]['tableName']} {$relations[0]['alias']}')";
            $fields='';
            $joinarr="array(";
            foreach($relations as $key=>$val)
            {
                if($key!=0)
                {
                    //$actionstr.="->joinData('{$val['tableName']} {$val['alias']}','{$val['relation']}','left')";
                    $rel=isset($val['relation'])?$val['relation']:'';
                    $joinarr.="array('{$val['tableName']} {$val['alias']}','{$rel}','left'),";
                }
                if($val['field'])
                {
                    $val['field']=str_replace(',',",{$val['alias']}.",implode(',',array_filter(preg_split('/[\r\n]+/s',$val['field']))));
                    if($fields!='')$fields.=',';
                    $fields.="{$val['alias']}.{$val['field']}";
                }

            }
            $joinarr=strpos($joinarr,',')!==false?substr($joinarr,0,strlen($joinarr)-1):$joinarr;
            $ac['where']=$ac['where']?'$'.$ac['where']:'null';
            $ac['group']=$ac['group']?'$'.$ac['group']:'null';
            $ac['order']=$ac['order']?'$'.$ac['order']:'null';
            $joinarr.=')';
            if($fields!='')
                $actionstr.="->field('{$fields}')";
            if($type==5)
                $actionstr.="->getOne({$ac['where']},{$ac['order']},{$ac['group']},".$joinarr.");\r\n";
            elseif($type==2)$actionstr.="->getListData({$ac['where']},{$ac['order']},{$ac['group']},".$joinarr.");\r\n";
            elseif($type==6)
            {
                $actionstr='';
                if($ac['bianlian_name'])$actionstr.='$'."{$ac['bianlian_name']}=";
                $wheres=$ac['where']?"{$ac['where']}":'null';
                $fields=$fields?"'".$fields."'":'null';
                $orders=$ac['order']?$ac['order']:'null';
                $groups=$ac['group']?$ac['group']:'null';
                $actionstr.='$'."this->getListData('{$relations[0]['tableName']} {$relations[0]['alias']}',{$wheres},{$fields},{$orders},{$groups},".$joinarr.");\r\n";

            }
            return $actionstr;
        }
        return $actionstr;

    }
}