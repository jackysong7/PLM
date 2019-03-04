<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/4
 * Time: 10:11
 * 销售状态
 */
namespace app\api\model;
use think\Model;
use think\Db;

class SalesStatus extends Model
{
    protected $table = 'plm_sales_status';

    public static function checkList($data){
        return self::get($data);
    }
    /**
     * 新增销售状态
     */
    public static function insertSalesStatusInfo($param)
    {
        $param['status'] = 1;
        $param['createtime'] = time();
        return self::create($param);
    }

    /**
     * 获取数据列表
     */
    public static function getDataList($condition = '',$field = '*',$order = 'sort')
    {
        return Db::table('plm_sales_status')->where($condition)->field($field)->order($order)->select();
    }

    /**
     * 获取某条数据
     */
    public static function getDataInfo($param)
    {
        $param['status'] = 1;
        return self::get($param);
    }

    /**
     * 修改数据
     */
    public static function editDataInfo($param)
    {
        $condition['ss_id'] = $param['ss_id'];
        if(!empty($param['ss_name'])){
            $data['ss_name'] = $param['ss_name'];
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
        $condition['ss_id'] = $param['ss_id'];
        $data['status'] = 3;
        $data['updatetime'] = time();
        return self::update($data,$condition);
    }
}