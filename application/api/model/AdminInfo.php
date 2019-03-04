<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/10
 * Time: 15:21
 * 产品(商品)模型属性属性值
 */
namespace app\api\model;

use think\Model;
use think\Db;

class AdminInfo extends Model
{
    protected $table = 'plm_admin_info';

    /**
     * 获取某条数据信息
     */
    public static function checkList($param)
    {
        return self::get($param);
    }
}