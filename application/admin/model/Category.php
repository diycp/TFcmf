<?php
/**
 * Created by PhpStorm.
 * User: July
 * Date: 2016/10/16
 * Time: 16:25
 */
namespace app\admin\model;

use think\Model;

class Category extends Model
{
    protected $insert = ['opstatus' => 1];

    protected $autoWriteTimestamp = true;


}