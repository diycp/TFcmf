<?php

namespace app\admin\controller\auth;

use app\admin\controller\CommonController;
use app\admin\model\Admin;
use think\Db;

/**
 * 菜单规则管理
 * Class RuleController
 * @package app\admin\controller\auth
 */
class AdminController extends CommonController
{
    protected $AdminModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->AdminModel = new Admin();
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
        $list = Db::name('admin')->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加页面和添加操作
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (!$post['password']) {
                $post['password'] = $post['username'];
            }
            $post['password'] = $this->AdminModel->encryptPassword($post['password']);
            if ($this->AdminModel->allowField(true)->validate(true)->save($post) !== false) {
                $group = $this->request->post('group/a', []);
                if ($group) {
                    $uid = $this->AdminModel->id;
                    foreach ($group as $val) {
                        Db::name('auth_group_access')->insert(['uid' => $uid, 'group_id' => $val]);
                    }
                }
                return $this->outInfo();
            }
            return $this->outInfo($this->AdminModel->getError());
        } else {
            //获取用户组
            $group = Db::name('auth_group')->field('id,name,status')->select();
            $this->assign('group', $group);
            return $this->fetch('edit');
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
            if ($post['password']) {
                $post['password'] = $this->AdminModel->encryptPassword($post['password']);
            } else {
                unset($post['password']);
            }

            if ($this->AdminModel->allowField(true)->isUpdate(true)->validate(true)->save($post) !== false) {
                $group = $this->request->post('group/a', []);
                $uid   = $post['id'];
                Db::name('auth_group_access')->where(['uid' => $post['id']])->delete();
                if ($group) {
                    foreach ($group as $val) {
                        Db::name('auth_group_access')->insert(['uid' => $uid, 'group_id' => $val]);
                    }
                }
                return $this->outInfo();
            }
            return $this->outInfo($this->AdminModel->getError());
        } else {
            $id = $this->request->get('id', 0, 'intval');
            if (!$id) {
                exit('参数错误');
            }
            $info = $this->AdminModel->where('id', $id)->find();
            //获取该用户的用户组
            $access_group = Db::name('auth_group_access')->where(['uid' => $id])->column('group_id');
            $group        = Db::name('auth_group')->field('id,name,status')->select();
            $this->assign('group', $group);
            $this->assign('info', $info);
            $this->assign('access_group', $access_group);
            return $this->fetch();
        }
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
        if (Admin::destroy($id) !== false) {
            return $this->outInfo(1, '删除成功');
        }
        return $this->outInfo(0, '删除失败');
    }

}