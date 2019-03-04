<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/3
 * Time: 14:14
 * 商品分类
 */
namespace app\api\model;
use think\Db;
use think\Model;

class Category extends Model
{

    protected $table = 'plm_goods_category';
    public static function checkList($data){
        return self::get($data);
    }
    /**
     * 新增信息
     */
    public static function insertInfo($param){
        $param['status'] = 1;
        $param['createtime'] = time();
        return self::create($param);
    }

    /**
     * 获取数据列表
     */
    public static function getDataList($condition,$field = '*',$order = 'sort')
    {
        return self::table('plm_goods_category')->where($condition)->field($field)->order($order)->select();
    }

    /**
     * 获取某条信息
     */
    public static function getDataInfo($param)
    {
        return self::get($param);
    }

    /**
     * 获取某条信息
     */
    public static function getGoodsCategoryInfo($condition,$field = '*')
    {
        return Db::table('plm_goods_category')->where($condition)->field($field)->find();
    }

    /**
     * 修改某条数据
     */
    public static function editDataInfo($param)
    {
        $condition['gc_id'] = $param['gc_id'];
        $data['gc_name'] = $param['gc_name'];
        if(isset($param['sort'])){
            $data['sort'] = $param['sort'];
        }
        $data['updatetime'] = time();
        return self::update($data,$condition);
    }

    /**
     * 删除某条信息
     */
    public static function deleteDataInfo($param)
    {
        $condition['gc_id'] = $param['gc_id'];
        $data['status'] = 3;
        $data['updatetime'] = time();
        return self::update($data,$condition);
    }
}