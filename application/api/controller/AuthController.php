<?php

namespace app\api\controller;

use app\common\controller\ApiController;
use think\Cache;
use think\Request;

class AuthController extends ApiController
{

    /**
     * 当前用户
     * @var
     */
    protected $identity = null;

    /**
     * 初始化
     * 验证token
     * $identity为当前用户的简易信息
     */
    public function _initialize()
    {
        parent::_initialize();
        if (!$this->request->isPost()) {
            return $this->outInfo(1, '请求类型错误');
        }

        //验证token
        $client_token = $this->request->header('client-token');
        if (!$client_token) {
            return $this->outInfo(10, '缺少token');
        }

        $info = Cache::get('user:info:' . $client_token);
        $token_expiration_date = config('token_expiration_date');
        if (!$info || ($token_expiration_date > 0 && (time() - $info['token_time'] > $token_expiration_date))) {
            return $this->outInfo(11, 'token已失效，请重新登陆');
        }
        $info['token']  = $client_token;
        $this->identity = array2object($info);
    }
}
