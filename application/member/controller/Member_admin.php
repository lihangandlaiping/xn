<?php namespace app\member\controller;

use My\MasterModel;

use Admin\AdminController;
use app\admin\model\Form;

class MemberAdmin extends AdminController
{
    function __construct()
    {
        parent::__construct();
        $this->model_name = 'member';
        config('parent_temple', 'Admin/Index/base');
    }

    /**
     * 删除
     * @param string $id
     */
    function delete($id = '')
    {
        if (!$id) $this->error('缺少数据id');
        if (!$this->model_name) $this->error('缺少数据库模型名称');
        if (strpos($id, ',') !== FALSE) {
            $line = MasterModel::inIt($this->model_name)->where(array('id' => array('in', $id)))->delete();
        } else {
            $line = MasterModel::inIt($this->model_name)->where(array('id' => $id))->delete();
        }

        if ($line) $this->success('删除成功', url('index'));
        else $this->error('删除失败');
    }

    /**
     * 列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function index()
    {
        $fieldlist = $this->getFieldList();//获取显示字段
        $where = $this->validSearch($fieldlist['showfield']);
//判断如果是级联列表
        $module = MasterModel::inIt('model')->field('is_cascade,cascade_field')->getOne(array('name' => $this->model_name));

        if ($module['is_cascade'] == 1) {
            $where[$this->model_name . '.' . trim($module['cascade_field'])] = input(trim($module['cascade_field']), 0);
            $this->assign('cascade_field', trim($module['cascade_field']));
        }
        $order = $this->model_name . '.id desc';
        $group = '';
        $field = "{$this->model_name}.id," . $fieldlist['fieldlist'];
//搜索封装验证
        $list = $this->getListData($this->model_name . " {$this->model_name}", $where, $field, $order, $group, tableRelation($this->model_name));
        $list = $this->validDataList($list, $fieldlist['showfield']);
        $this->assign('list', $list);
        $this->assign('field', $fieldlist['showfield']);
//生成搜索相关数据
        $this->assign('model_name', $this->model_name);
        $this->assign('form', new Form());
        return view('index');

    }

    /**
     * 编辑
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    function edit()
    {
        $action = input('action', 'add');
        $fieldlist = $this->getModelFromField($action);//获取表单字段
        if (request()->isPost()) {
            $data = $this->validform($fieldlist, $action);//验证表单数据
            if ($action == 'edit') {
                $line = MasterModel::inIt($this->model_name)->updateData($data, array('id' => input('post.id', '')));
                if ($line) $this->success('修改成功', url('index'));
                else $this->error('修改失败');
            } else {
                $id = MasterModel::inIt($this->model_name)->insertData($data);
                if ($id) $this->success('添加成功', url('index'));
                else $this->error('添加失败');
            }
        } else {
            $_GET = input();
            $_GET['action'] = $action;
            $cascade_field = '';
            $form = new Form();
            $values = array();
            if ($action == 'edit') {
                $values = MasterModel::inIt($this->model_name)->getOne(array('id' => $_GET['id']));
            } else {


            }
            $module = MasterModel::inIt('model')->field('is_cascade,cascade_field')->getOne(array('name' => $this->model_name));
            if ($module['is_cascade'] == 1) {
                $values[trim($module['cascade_field'])] = input(trim($module['cascade_field']), 0);
                $cascade_field = trim($module['cascade_field']);
            }
            $this->assign('info', $values);
            $this->assign('formstr', $form->createFrom($fieldlist, $values, $cascade_field));
            return view('edit');
        }
    }
}

?>