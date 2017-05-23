<?php

namespace app\common\util;

class Images
{
    public $imgsrc;

    public $imgdata;

    public $imgform;

    public function setDir($source)
    {
        $this->imgsrc = $source;
        return $this;
    }

    public function Show()
    {
        $this->setMime($this->imgsrc)->img2data()->data2img();
    }

    protected function img2data()
    {
        $this->imgdata = fread(fopen($this->imgsrc, 'rb'), filesize($this->imgsrc));
        return $this;
    }


    protected function data2img()
    {
        if (extension_loaded('zlib') && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            ob_start('ob_gzhandler');
        }

        $size = filesize($this->imgsrc);

        if (isset($_SERVER['HTTP_RANGE'])) {
            header('HTTP /1.1 206 Partial Content');
            $range = str_replace('=', '-', $_SERVER['HTTP_RANGE']);
            $range = explode('-', $range);

            header('Content-Length:' . $size);
            header('Content-Range: bytes ' . trim($range[0]) . '-' . $range[1]);
            header('Accenpt-Ranges: bytes');
            header('application/octet-stream');
            header("Cache-control: public");
            header("Pragma: public");

            $fp = fopen($this->imgsrc, 'rb+');
            fseek($fp, intval(trim($range[0])));
            while (!feof($fp)) {
                set_time_limit(0);
                print(fread($fp, 1024));         //读取文件（可安全用于二进制文件,第二个参数:规定要读取的最大字节数）
                ob_flush();                     //刷新PHP自身的缓冲区
                flush();                       //刷新缓冲区的内容(严格来讲, 这个只有在PHP做为apache的Module(handler或者filter)安装的时候, 才有实际作用. 它是刷新WebServer(可以认为特指apache)的缓冲区.)
            }

            fclose($fp);
        } else {
            header("content-type:$this->imgform");
            header(sprintf("Content-Length:%d", $size));
            echo $this->imgdata;
        }

        if (extension_loaded('zlib')) {
            ob_end_flush();
        }
    }

    public function setMime($imgsrc)
    {
        $info          = getimagesize($imgsrc);
        $this->imgform = $info['mime'];
        return $this;
    }

    /**
     * 下载原图
     */
    public function download()
    {
        $this->setMime($this->imgsrc)->img2data()->save();

    }

    protected function save()
    {
        header("content-type:$this->imgform");
        header(sprintf("Content-Length:%d", filesize($this->imgsrc)));
        header(sprintf("Content-Disposition:attachment; filename=%s", pathinfo($this->imgsrc, PATHINFO_BASENAME)));
        echo $this->imgdata;
    }
}
