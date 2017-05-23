<?php

namespace app\admin\controller;

use think\Request;

/**
 * 空控制器
 * Class ErrorController
 * @package app\admin\controller
 */
class ErrorController
{

    public function _empty()
    {
        if (Request::instance()->isAjax()) {
            header('Content-type: application/json;charset=utf-8');
            exit(json_encode(['code' => 0, 'msg' => '您请求的链接不存在！']));
        } else {
            throw new \think\exception\HttpException(404, '页面不存在');
        }

    }
}
