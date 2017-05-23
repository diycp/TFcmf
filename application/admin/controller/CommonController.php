<?php
/**
 * Created by PhpStorm.
 * User: YC
 * Date: 2017/4/29
 * Time: 13:59
 */

namespace app\admin\controller;

use app\common\api\ConfigApi;
use app\common\controller\BaseCotroller;
use app\common\util\Auth;
use think\Db;

class CommonController extends BaseCotroller
{

    /**
     * 权限控制类
     * @var auth
     */
    protected $auth = null;

    /**
     * 用户信息
     * @var admin
     */
    protected $admin = null;


    public function _initialize()
    {
        parent::_initialize();
        //先校验是否登陆
        $this->admin = session('admin');
        if (!$this->admin || empty($this->admin['id'])) {
            if ($this->request->isAjax()) {
                return $this->outInfo(0, '用户验证失败，请重新登陆！', 'public/login');
            } else {
                return $this->redirect('public/login');
            }
        }

        //ip限制
        if (config('admin_allow_ip')) {
            if (!in_array($this->request->ip(), explode(',', config('admin_allow_ip')))) {
                return $this->outInfo('禁止访问!');
            }
        }

        //auth
        $this->auth = Auth::instance();

        $modulename     = $this->request->module();
        $controllername = strtolower($this->request->controller());
        $actionname     = strtolower($this->request->action());
        $path           = '/' . $modulename . '/' . str_replace('.', '/', $controllername) . '/' . $actionname;

        //FileController和InfoController为所有用户有权限的控制器，无需验证
        if (!($controllername == 'file' || $controllername == 'info')) {
            //验证是否有权限
            if (!(in_array($path, config('allow_visit')) || $this->auth->check($path, $this->admin['id']))) {
                return $this->outInfo(-1, '未授权访问');
            }
        }

        //非异步生成菜单信息
        if (!$this->request->isAjax()) {
            //设置菜单
            $data = $this->auth->getMenu($this->admin['id'], $path);
            $this->assign('__MENU__', $data);
        }
    }

}