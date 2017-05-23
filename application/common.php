<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function json_format(&$val)
{
    if (!is_object($val) && !is_array($val)) {
        settype($val, 'string');
    }
    return $val;
}

/**
 * 把返回的数据集转换成Tree
 *
 * @param array  $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 *
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = [];
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = [];
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent           = &$refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 *
 * @param  array  $tree 原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list 过渡用的中间数组，
 *
 * @return array        返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = [])
{
    if (is_array($tree)) {
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if (isset($reffer[$child])) {
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby = 'asc');
    }
    return $list;
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string)
{
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if (strpos($string, ':')) {
        $value = [];
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k] = $v;
        }
    } else {
        $value = $array;
    }
    return $value;
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } else if (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}


/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array  $list 查询结果
 * @param string $field 排序的字段名
 * @param array  $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc')
{
    if (is_array($list)) {
        $refer = $resultSet = array();
        foreach ($list as $i => $data) {
            $refer[$i] = &$data[$field];
        }

        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc': // 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val) {
            $resultSet[] = &$list[$key];
        }

        return $resultSet;
    }
    return false;
}

/**
 * 获取多位数组的深度
 * @param $array
 * @return int
 */
function array_depth($array)
{
    $max_depth = 0;
    foreach ($array as $value) {
        if (is_array($value)) {
            $depth = array_depth($value) + 1;
            if ($depth > $max_depth) {
                $max_depth = $depth;
            }
        }
    }
    return $max_depth;
}


/**
 * 是否是手机号码，含虚拟运营商的170号段
 * @author wei sun
 * @param string $phone 手机号码
 * @return boolean
 */
function is_phone($phone)
{
    if (APP_DEV) {
        return true;
    }

    if (strlen($phone) != 11 || !preg_match('/^1[3|4|5|7|8][0-9]\d{4,8}$/', $phone)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 是否是正确的身份证号码
 * @author yc
 * @param string $id 身份证号码
 * @return boolean
 */
function is_idcard($id)
{
    if (APP_DEV) {
        return true;
    }
    $id        = strtoupper($id);
    $regx      = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
    $arr_split = array();
    if (!preg_match($regx, $id)) {
        return false;
    }
    if (15 == strlen($id)) //检查15位
    {
        $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

        @preg_match($regx, $id, $arr_split);
        //检查生日日期是否正确
        $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) {
            return false;
        } else {
            return true;
        }
    } else      //检查18位
    {
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        if (!strtotime($dtm_birth)) //检查生日日期是否正确
        {
            return false;
        } else {
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            $arr_ch  = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $sign    = 0;
            for ($i = 0; $i < 17; $i++) {
                $b    = (int)$id{$i};
                $w    = $arr_int[$i];
                $sign += $b * $w;
            }
            $n       = $sign % 11;
            $val_num = $arr_ch[$n];
            if ($val_num != substr($id, 17, 1)) {
                return false;
            } //phpfensi.com
            else {
                return true;
            }
        }
    }

}


/**
 * 验证是否为中文姓名
 * @param $name
 */
function isChineseName($name)
{
    if (preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $name)) {
        return true;
    }
    return false;
}


/**
 ** @desc 封装 curl 的调用接口，post的请求方式
 **/
function curl_post($url, $post_data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}

/**
 * 数组转对象
 * @param $array
 * @return StdClass
 */
function array2object($array)
{
    if (is_array($array)) {
        $obj = new StdClass();
        foreach ($array as $key => $val) {
            $obj->$key = $val;
        }
    } else {
        $obj = $array;
    }
    return $obj;
}

/**
 * 对象转数组
 * @param $object
 * @return mixed
 */
function object2array($object)
{
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    } else {
        $array = $object;
    }
    return $array;
}

/**
 * 获取图片路径
 * @param int $id
 * @return string
 */
function get_image_path($id = 0)
{
    if (!$id) {
        return '';
    }

    static $list;
    /* 获取缓存数据 */
    if (empty($list)) {
        $list = cache('sys_uploads_list');
    }
    /* 查找用户信息 */
    $key = "u{$id}";
    if (isset($list[$key])) {
        //已缓存，直接使用
        $name = $list[$key];
    } else {
        //调用接口获取用户信息
        $info = model('uploads')->field('type,path')->find($id);
        if ($info !== false && $info['path']) {
            $path = config('upload.upload_path') . DS . $info['type'] . DS . $info['path'];
            $name = $list[$key] = $path;
            /* 缓存用户 */
            $count = count($list);
            while ($count-- > 1000) {
                array_shift($list);
            }
            cache('sys_uploads_list', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

/**
 * 获取id用户姓名
 * @param int $id
 * @return string
 */
function get_user_realname($id)
{
    if (!$id) {
        return '';
    }

    static $list;
    /* 获取缓存数据 */
    if (empty($list)) {
        $list = cache('list_user_realname');
    }
    /* 查找用户信息 */
    $key = "u{$id}";
    if (isset($list[$key])) {
        //已缓存，直接使用
        $name = $list[$key];
    } else {
        //调用接口获取用户信息
        $realname = model('user')->where('id', $id)->value('realname');
        if ($realname) {
            $name = $list[$key] = $realname;
            /* 缓存用户 */
            $count = count($list);
            while ($count-- > 2000) {
                array_shift($list);
            }
            cache('list_user_realname', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

/**
 * 获取id用户姓名
 * @param int $id
 * @return string
 */
function get_user_username($id)
{
    if (!$id) {
        return '';
    }

    static $list;
    /* 获取缓存数据 */
    if (empty($list)) {
        $list = cache('list_user_username');
    }
    /* 查找用户信息 */
    $key = "u{$id}";
    if (isset($list[$key])) {
        //已缓存，直接使用
        $name = $list[$key];
    } else {
        //调用接口获取用户信息
        $username = model('user')->where('id', $id)->value('username');
        if ($username) {
            $name = $list[$key] = $username;
            /* 缓存用户 */
            $count = count($list);
            while ($count-- > 2000) {
                array_shift($list);
            }
            cache('list_user_realname', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

//身份证隐藏
function idCardHidden($idcard)
{
    if (strlen($idcard) < 15) {
        return $idcard;
    }
    return substr($idcard, 0, 6) . str_repeat('*', strlen($idcard) - 7) . substr($idcard, -1);
}

//银行卡隐藏
function bankNumberHidden($number)
{
    if (strlen($number) < 16) {
        return $number;
    }
    return str_repeat('*', strlen($number) - 4) . substr($number, -4);
}

//同步小区收入
function syncIncome()
{
    $dir          = dirname(__FILE__);
    $dir          = dirname($dir);
    $command_path = "php " . $dir . "/public/index.php api/Task/runSmallIncome &";

    pclose(popen($command_path, 'r'));
    // _sock(U('Task/runRezhiIncome', ['_sock' => 'sock'], false, true));
}