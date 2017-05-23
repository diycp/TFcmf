<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use think\Db;
use think\Validate;

class InfoController extends CommonController
{
    /**
     * 用户个人中心
     * @return mixed
     */
    public function user()
    {
        $info = Db::name('admin')->find(session('admin.id'));
        $this->assign('info', $info);

        return $this->fetch();
    }

    /**
     * 更新昵称
     */
    public function updateNickname()
    {
        $nickname = $this->request->post('nickname', '', 'trim');
        if (!$nickname) {
            return $this->outInfo('昵称不能为空');
        }
        if (Db::name('admin')->where('id', session('admin.id'))->setField('nickname', $nickname) !== false) {
            session('admin.nickname', $nickname);

            return $this->outInfo(1, '修改成功');
        }

        return $this->outInfo(1, '修改失败');
    }

    //更新头像
    public function updateFace()
    {
        $face = $this->request->post('face', 0, 'intval');
        if (Db::name('admin')->where('id', session('admin.id'))->setField('face', $face) !== false) {
            session('admin.face', url('file/image', ['id' => $face]));

            return $this->outInfo(1, '修改成功');
        }

        return $this->outInfo(1, '修改失败');
    }

    //修改密码
    public function updatePassword()
    {
        $post = $this->request->post();
        $validate = new Validate([
            'password|密码' => 'require|length:6,16',
            'repassword|重复密码' => 'require|length:6,16'
        ]);
        if (!$validate->check($post)) {
            return $this->outInfo($validate->getError());
        }
        if ($password != $repassword) {
            return $this->outInfo('两次密码输入不一致');
        }
        $admin = new Admin();
        $password = $admin->encryptPassword($password);
        if (Db::name('admin')->where('id', session('admin.id'))->setField('password', $password) !== false) {
            return $this->outInfo(1, '修改成功');
        }

        return $this->outInfo(1, '修改失败');
    }
}