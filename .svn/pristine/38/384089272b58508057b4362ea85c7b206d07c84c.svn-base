<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/11
 * Time: 9:56
 * 产品模型
 */
namespace app\api\model;

use think\Model;
use think\Db;
class Sample extends Model
{
    protected $table = 'plm_product_model';
    public static function checkList($data){
        return self::get($data);
    }
    /**
     * 新增数据
     */
    public static function addDataInfo($param){
        $param['status'] = 1;
        $param['createtime'] = time();
        return self::create($param);
    }

    /**
     * 获取数据列表
     */
    public static function getDataList($condition = '',$field = '*',$order = 'sort')
    {
        return self::table('plm_product_model')->where($condition)->field($field)->order($order)->select();
    }

    /**
     * 修改数据
     */
    public static function editDataInfo($param)
    {
        $condition['pm_id'] = $param['pm_id'];
        if(!empty($param['pm_name'])){
            $data['pm_name'] = $param['pm_name'];
        }
        if(isset($param['sort'])){
            $data['sort'] = $param['sort'];
        }
        $data['updatetime'] = time();

        return self::update($data,$condition);
    }

    /**
     * 删除数据
     */
    public static function deleteDataInfo($param)
    {
        $condition['pm_id'] = $param['pm_id'];
        $data['status'] = 3;
        $data['updatetime'] = time();
        return self::update($data,$condition);
    }
}