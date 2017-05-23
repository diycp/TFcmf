<?php

namespace app\admin\controller;

use app\common\library\hashids\Hashids;
use app\common\util\Images;
use app\common\util\XDeode;
use think\Db;
use think\Response;

class FileController extends CommonController
{

    public function upload()
    {
        $type = request()->request('type', 'image');
        $file = request()->file('file');
        if (empty($file)) {
            return json(['status' => 5, 'msg' => '文件不存在']);
        }

        //获取上传配置
        $config = config('upload');
        $path   = $config['upload_path'] . DS . $type;
        if (!isset($config['upload_size_limit'][$type])) {
            return json(['code' => 2, 'msg' => '上传文件格式不允许']);
        }
        $info = $file->validate(['size' => $config['upload_size_limit'][$type], 'ext' => $config['upload_type_limit'][$type]])->move($path);
        if ($info) {
            $data          = [
                'type'     => $type,
                'ext'      => strtolower($info->getExtension()),
                'path'     => $info->getSaveName(),
                'filename' => $info->getFilename(),
                'size'     => $info->getSize(),
                'sha1'     => $info->hash('sha1'),
                'md5'      => $info->hash('md5'),
                'at_time'  => time()
            ];
            $id            = Db::name('uploads')->insertGetId($data);
            $result['src'] = url('image', ['id' => (new XDeode())->encode($id)]);
            $result['id']  = $id;
            return json(['code' => 0, 'msg' => '上传成功', 'data' => $result]);
        } else {
            return json(['code' => -10, 'msg' => $file->getError()]);
        }
    }

    public function image()
    {
        $id = $this->request->get('id', '', 'trim');
        if (!is_numeric($id)) {
            $id = (new XDeode())->decode($id);
        }

        $path = get_image_path($id);
        if (!$path) {
            $path = ROOT_PATH . 'public/static/admin/dist/img/error.png';
        }
        $content = file_get_contents($path);
        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/jpeg');
    }
}