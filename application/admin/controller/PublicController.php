<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\common\controller\BaseCotroller;
use think\Cache;

/**
 * 公共控制器
 * Class PublicController
 * @author yc <cotyxpp@gmail.com>
 * @package app\admin\controller
 */
class PublicController extends BaseCotroller
{
    /**
     * 登陆
     * @return mixed|void
     */
    public function login()
    {
        if ($this->request->isPost()) {
            $username = $this->request->post('username', '', 'trim,htmlspecialchars');
            $password = $this->request->post('password', '');
            if (!$username || !$password) {
                return $this->outInfo('用户名或密码不能为空');
            }
            $admin  = new Admin;
            $result = $admin->login($username, $password);
            if ($result === true) {
                return $this->outInfo(1, '登陆成功', url('index/index'));
            }
            return $this->outInfo($result);
        } else {
            return $this->fetch();
        }
    }

    /**
     * 退出登陆
     */
    public function logout()
    {
        (new Admin())->logout();
        return $this->outInfo(1, '退出成功', url('login'));
    }

    /**
     * 清除所有缓存
     * 如果缓存跟session都用的同一个redis。将退出登陆
     */
    public function clearCache()
    {
        if (session('admin.id') != 1) {
            return $this->outInfo('您无权限操作');
        }
        if ($this->delDir(RUNTIME_PATH . '/temp')) {
            return $this->outInfo(1, '模板缓存清除成功');
        } else {
            return $this->outInfo('缓存清除失败');
        }
    }

    /**
     * 清楚数据缓存
     */
    public function clearDataCache()
    {
        if (session('admin.id') != 1) {
            return $this->outInfo('您无权限操作');
        }
        Cache::clear();
        return $this->outInfo(1, '数据缓存清除成功');
    }

    protected function delDir($dir)
    {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    @unlink($fullpath);
                } else {
                    $this->del_dir($fullpath);
                }
            }
        }
        closedir($dh);
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

}