<?php

namespace app\admin\controller\system;

use app\admin\controller\CommonController;
use think\Db;

/**
 * 菜单规则管理
 * Class RuleController
 * @package app\admin\controller\auth
 */
class ConfigController extends CommonController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->assign('group', config('config_group_list'));
        $this->assign('type', config('config_type_list'));
    }

    /**
     * 主页面
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 列表
     * @return mixed
     */
    public function lists()
    {
        $group = $this->request->post('group', '', 'trim');
        $map   = [];
        if ($group) {
            $map['group'] = $group;
        }
        $list = Db::name('config')->where($map)->order('sort asc')->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    //配置
    public function group()
    {
        if ($this->request->isPost()) {
            $config = $this->request->post('config/a');
            if ($config) {
                foreach ($config as $name => $value) {
                    Db::name('config')->where(['name' => $name])->setField('value', $value);
                }
            }
            cache('sys:cache:config', null);
            return $this->outInfo(1, '保存成功！');
        } else {
            //所有配置项
            $group = config('config_group_list');
            $list  = [];
            foreach ($group as $key => $val) {
                $temp         = [];
                $temp['id']   = $key;
                $temp['name'] = $val;
                $temp['list'] = Db::name('config')->where(['lock' => 0, 'group' => $key])->select();
                $list[]       = $temp;
            }

            $this->assign('list', $list);
            $this->assign('group', $group);
            $this->assign('type', config('config_type_list'));
            return $this->fetch();
        }

    }

    /**
     * 添加页面和添加操作
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (Db::name('config')->insert($post) !== false) {
                cache('sys:cache:config', null);
                return $this->outInfo();
            }
            return $this->outInfo('添加失败');
        } else {
            return $this->fetch();
        }
    }

    /**
     * 修改页面和修改操作
     * @return mixed
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (Db::name('config')->update($post) !== false) {
                cache('sys:cache:config', null);
                return $this->outInfo();
            }
            return $this->outInfo('修改失败');
        } else {
            $id = $this->request->get('id', 0, 'intval');
            if (!$id) {
                exit('参数错误');
            }
            $info = Db::name('config')->where('id', $id)->find();
            $this->assign('info', $info);
            return $this->fetch();
        }
    }

    /**
     * 快速排序
     * @return json
     */
    public function sort()
    {
        $id   = $this->request->get('id', 0, 'intval');
        $sort = $this->request->post('sort', 0, 'intval');

        if ($id > 0 && Db::name('config')->where(['id' => $id])->setField('sort', $sort) !== false) {
            return $this->outInfo(1, '设置成功');
        }
        return $this->outInfo('更新失败');
    }

    /**
     * 快速锁定
     * @return json
     */
    public function lock()
    {
        $id   = $this->request->get('id', 0, 'intval');
        $lock = $this->request->get('lock', 0, 'intval');

        if ($id > 0 && Db::name('config')->where(['id' => $id])->setField('lock', $lock) !== false) {
            return $this->outInfo(1, '设置成功');
        }
        return $this->outInfo('更新失败');
    }

    /**
     * 删除操作
     * @return json
     */
    public function delete()
    {
        $id = $this->request->get('id', 0, 'intval');
        if ($id <= 0) {
            return $this->outInfo(0, '参数错误');
        }
        if (Db::name('config')->delete($id) !== false) {
            cache('sys:cache:config', null);
            return $this->outInfo(1, '删除成功');
        }
        return $this->outInfo(0, '删除失败');
    }

}