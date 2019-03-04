<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/4
 * Time: 14:06
 *
 */

namespace app\api\model;
use think\Model;
use think\Db;

class SalesChannels extends Model
{
    protected $table = 'plm_sales_channels';

    public static function checkList($data){
        return self::get($data);
    }

    /**
     * 新增数据
     */
    public static function insertInfo($param)
    {
        $param['status'] = 1;
        $param['createtime'] = time();

        return self::create($param);
    }

    /**
     * 获取数据列表
     */
    public static function getDataList($condition,$field = '*',$order = 'sort')
    {
        return Db::table('plm_sales_channels')->where($condition)->field($field)->order($order)->select();
    }

    /**
     * 获取单条数据
     */
    public static function getDataInfo($param)
    {
        return self::get($param);
    }

    /**
     * 修改数据
     */
    public static function editDataInfo($param)
    {
        $condition['sc_id'] = $param['sc_id'];
        if(!empty($param['sc_name'])){
            $data['sc_name'] = $param['sc_name'];
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
        $condition['sc_id'] = $param['sc_id'];
        $data['status'] = 3;
        $data['updatetime'] = time();
        return self::update($data,$condition);
    }
}