<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/2
 * Time: 11:06
 * 品牌管理
 */

namespace app\api\model;
use think\Model;
use think\Db;
class Brand extends Model
{
    protected $table = 'plm_brand';

    public static function checkList($data){
        return self::get($data);
    }

    /**
     * 新增一条品牌信息
     */
    public static function insertInfo($array)
    {
        $array['status'] = 1;
        return self::create($array);
    }

    /**
     * 新增多条品牌信息
     */
    public static function insertAllInfo($data)
    {
        return self::saveAll($data);
    }

    /**
     * 获取品牌信息列表
     */
    public static function getDataList($where,$field = '*',$order = 'sort')
    {
        return self::table('plm_brand')->field($field)->where($where)->order($order)->select();
        //return $this->all($where);
    }

    /**
     * 获取单条品牌信息
     */
    public static function getDataInfo($array)
    {
        return self::get($array);
    }

    /**
     * 修改某条信息
     */
    public static function editDataInfo($array)
    {
        $brandWhere['brand_id'] = $array['brand_id'];

        if(!empty($array['brand_name'])){
            $brandData['brand_name'] = $array['brand_name'];
        }
        if(!empty($array['brand_pic'])){
            $brandData['brand_pic'] = $array['brand_pic'];
        }
        if(!empty($array['sort'])){
            $brandData['sort'] = $array['sort'];
        }

        $brandData['updatetime'] = time();
        return self::update($brandData,$brandWhere);
    }

    /**
     *  删除数据
     */
    public static function deleteDataInfo($array){
        $brandWhere['brand_id'] = $array['brand_id'];
        $brandData['status'] = 3;
        $brandData['updatetime'] = time();
        return self::update($brandData,$brandWhere);
    }
}
