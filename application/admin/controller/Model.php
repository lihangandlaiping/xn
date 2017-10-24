<?php
/**
 * 后台数据表
 */
namespace app\admin\controller;
use My\MasterModel;
use Admin\AdminController;
use think\Controller;
class Model extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name = 'model';
        config('parent_temple', 'Admin/Index/base');
    }

    function index($modul_id = '', $name = '')
    {
        $where = array();
        if ($modul_id != '') $where['m1.modul_id'] = $modul_id;
        if ($name != '') $where['m1.name'] = array('like', '%' . $name . '%');
        $list = $this->getListData($this->model_name . ' m1', $where, 'm1.*,m2.name as modul_name', '', '', array(array('module m2', 'm2.id=m1.modul_id', 'left')));
        $module = MasterModel::inIt('module')->getListData(null, 'sort');
        $this->assign('module', $module);
        $this->assign('modul_id', $modul_id);
        return view('index', array('list' => $list));
    }

    /**
     * 编辑
     */
    function edit($id = '', $action = 'add')
    {
        if (request()->isPost()) {
            $data = input('post.data');
            if ($action == 'edit') {
                $id = input('post.id', '');
               /* $modelvalid=MasterModel::inIt('model')->getCount(array('name'=>$data['name'],'id' => array('neq',$id)));
                if($modelvalid>0)$this->error('该数据库标识以存在');*/
                $line = MasterModel::inIt($this->model_name)->updateData($data, array('id' => $id));
                if ($line) $this->success('修改成功', url('index',array('modul_id'=>isset($data['modul_id'])?$data['modul_id']:'')));
                else $this->error('修改失败');
                return false;
            }
            if (!preg_match('/^\w+$/', $data['name'])) $this->error('模型标识格式错误');
            $modelvalid=MasterModel::inIt('model')->getCount(array('name'=>$data['name']));
            if($modelvalid>0)$this->error('该数据库标识以存在');
            $id = MasterModel::inIt($this->model_name)->insertData($data);
            if ($id) {
                $pix = config('prefix');
                MasterModel::inIt($this->model_name)->execute("CREATE TABLE {$pix}{$data['name']} (
  id int(10) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) ENGINE={$data['engine_type']} AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
                $module = MasterModel::inIt('module m')->field('m.name')->getOne(array('m.id' => $data['modul_id']));
                createControllerView($module['name'], $data['name']);
                $this->success('添加成功', url('index',array('modul_id'=>isset($data['modul_id'])?$data['modul_id']:'')));
            } else $this->error('添加失败');


        } else {
            $_GET['action'] = $action;
            if ($action == 'edit') {
                $info = MasterModel::inIt($this->model_name)->getOne(array('id' => $id));
            } else {
                $info['modul_id'] = input('modul_id', '');
            }
            $this->assign('info', $info);
            $module = MasterModel::inIt('module')->getListData(null, 'sort');
            $this->assign('module', $module);
            return view('edit');
        }
    }

    /**
     * 删除
     */
    function delete($id = '')
    {
        if (!$id) $this->error('缺少数据id');
        if (!$this->model_name) $this->error('缺少数据库模型名称');
        $arr = array();
        if (strpos($id, ',') !== FALSE) {
            $arr = MasterModel::inIt($this->model_name)->getListData(array('id' => array('in', $id)));
            $line = MasterModel::inIt($this->model_name)->where(array('id' => array('in', $id)))->delete();
        } else {
            $arr = MasterModel::inIt($this->model_name)->getListData(array('id' => $id));
            $line = MasterModel::inIt($this->model_name)->where(array('id' => $id))->delete();
        }
        if ($line) {
            $pix = config('prefix');
            foreach ($arr as $row) {
                MasterModel::inIt($this->model_name)->execute("DROP TABLE {$pix}{$row['name']}");
            }
            $this->success('删除成功', url('index'));
        } else $this->error('删除失败');
    }

    /**
     * 需要显示的字段
     * @param string $id
     */
    function show_filed($id = '')
    {
        if (!$id)
            $this->error('缺少模型标识');
        $model = MasterModel::inIt('model')->getOne(array('id' => $id));
        $fields = $model['show_filed'] ?: '';
        if (!$fields) {
            $field = MasterModel::inIt('field f')->field('f.field,f.name,f.type,ma.name as tables')->joinData(array(array('model ma', 'ma.id=f.model_id', 'left')))->getListData(array('f.model_id' => $id, 'f.is_column' => 2));
        } else {
            $field = unserialize($fields);
        }
        $this->assign('fieldlist', $field);
        $field = MasterModel::inIt('field')->field('extra,field')->getListData(array('model_id' => $id, 'type' => 'tablefield'));
        $newtable = array(array('table'=>$model['name'],'field'=>'this'));
        foreach ($field as $row) {
            $vals = array_filter(preg_split('/[\r\n]+/s', $row['extra']));
            foreach ($vals as $v) {
                $v = explode(':', $v);
                if ($v[0] == 'db_table' && $v[1]) {
                    $newtable[]=array('table'=>$v[1],'field'=>$row['field']);
                    //array_push($newtable, $v[1]."({$row['field']})");
                }
            }
        }
        $this->assign('tables', $newtable);
        $this->assign('model_id', $id);
        $this->assign('thismodelname', $model['name']);
        return view('show_filed');
    }

    /**
     * 获取字段
     * @param string $name
     */
    function selectfield($name = '')
    {
        $list = MasterModel::inIt('field f')->field('f.*')->joinData(array(array('model m', 'm.id=f.model_id', 'left')))->getListData(array('m.name' => $name));
        array_unshift($list,array('name'=>'id','field'=>'id','type'=>'num'));
        echo json_encode($list);
    }

    /**
     * 保存列表显示字段
     */
    function saveShowField()
    {
        $data = input('post.data');
        $model_id = input('post.model_id', '');
        $newarr = array();
        foreach ($data as $key => $val) {
            $extra = '';
            if (in_array($val['type'], array('bool', 'select', 'radio', 'checkbox', 'tablefield', 'linkage'))) {
                if (strpos($val['field'], '.')) {
                    $a = explode('.', $val['field']);
                    $a = end($a);
                } else {
                    $a = $val['field'];
                }
                $extras = MasterModel::inIt('model m')->field('f.extra')->joinData(array(array('field f', 'f.model_id=m.id', 'left')))->getOne(array('m.name' => $val['tables'], 'f.field' => $a));
                $extra = $extras['extra'];
            }
            $newarr[] = array('field' => $val['field'], 'name' => $val['name'], 'tables' => $val['tables'], 'type' => $val['type'], 'extra' => $extra, 'searchd' => $val['searchd']);
        }
        $line = MasterModel::inIt($this->model_name)->updateData(array('show_filed' => serialize($newarr)), array('id' => $model_id));
        if ($line)
            $this->success('保存成功');
        else
            $this->error('保存失败');

    }

    /**
     * 导出数据备份
     * @param string $model_id
     */
    function backup($model_id = '',$bz='')
    {
        @mkdir(YANYU_ROOT . '/database',0777);
        $dirpath = YANYU_ROOT . '/database/' . date('YmdHis') . '/';
        @mkdir($dirpath,0777);
        file_put_contents($dirpath.'bz.txt',$bz);
        $dbs = new \app\admin\model\DbManage(config('hostname'), config('username'),config('password'), config('database'));
        $model = MasterModel::inIt('model')->getOne(array('id' => $model_id));
        //$dbs->backup('yanyu_field',APP_PATH.'data/',100);
        //$dbs->restore(APP_PATH.'data/20160928132708_yanyu_field_v1.sql');
        //var_dump( $dbs->msg);
        if ($model_id != '' && $model) {
            $dbs->backup(config('prefix') . $model['name'], $dirpath, 100);
        } else {
            $dbs->backup('', $dirpath, 100);
        }
    }

    /**
     * 导入数据备份
     */
    function restore($name='')
    {
        $dirpath = YANYU_ROOT . '/database/' . $name . '/';
        $dbs = new \app\admin\model\DbManage(config('hostname'), config('username'),config('password'), config('database'));
        if (false != ($handle = opendir ( $dirpath ))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".."&&strpos($file,'.sql')) {
                    $dbs->restore($dirpath.$file);
                }
            }
            //关闭句柄
            closedir ( $handle );
        }

    }

    /**
     * 备份管理
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function selectDatabase()
    {
        $dir=YANYU_ROOT . '/database/';
        $dirArray=array();
        if (false != ($handle = opendir ( $dir ))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".."&&!strpos($file,".")) {
                    $dirArray[$i]=$file;
                    $i++;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }

        for($i=0;$i<count($dirArray)-1;$i++)
        {
            for($j=$i+1;$j<count($dirArray);$j++)
            {
                if($dirArray[$j]>$dirArray[$i])
                {
                    $k=$dirArray[$i];
                    $dirArray[$i]=$dirArray[$j];
                    $dirArray[$j]=$k;
                }
            }
        }
        $bz=array();
        foreach($dirArray as $row)
        {
            $bz[$row]=file_get_contents($dir.$row.'/bz.txt');
        }
        $this->assign('dirArray',$dirArray);
        $this->assign('bz',$bz);
        return view('selectdatabase');
    }

    /**
     * 下载数据备份
     * @param string $name
     */
    function downloaddatabase($name='')
    {
        $files=YANYU_ROOT . '/database/'.$name.'/';
        $dirArray=array();
        if (false != ($handle = opendir ( $files ))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".."&&!strpos($file,".txt")) {
                    $dirArray[$i]=$files.$file;
                    $i++;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        $filename = YANYU_ROOT. "/".session_id().".zip"; // 最终生成的文件名（含路径）
// 生成文件
        @unlink($filename);
        $zip = new \ZipArchive (); // 使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        if ($zip->open ( $filename, \ZIPARCHIVE::CREATE ) !== TRUE) {
            exit ( '无法打开文件，或者文件创建失败' );
        }
//$fileNameArr 就是一个存储文件路径的数组 比如 array('/a/1.jpg,/a/2.jpg....');

        foreach ( $dirArray as $val ) {
            $zip->addFile ( $val, basename ( $val ) ); // 第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
        }
        $zip->close (); // 关闭
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($filename));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        readfile($filename);
        @unlink($filename);
    }

    /**
     * 删除数据备份
     * @param string $name
     */
    function deletedatabase($name='')
    {
        if(!$name)$this->error('数据错误');
        $file=YANYU_ROOT . '/database/'.$name.'/';
        $a=rmdirr($file);
        $this->success('删除成功');

    }

}
