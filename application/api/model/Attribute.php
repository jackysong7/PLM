<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/9
 * Time: 10:57
 * 商品模型属性
 */
namespace app\api\model;

use think\Model;
use think\Db;

class Attribute extends Model
{
    protected $table = 'plm_product_model_attribute_group';
    public static function checkList($data){
        return self::get($data);
    }
    /**
     * 新增数据
     */
    public static function addAttributeGroup($data)
    {
        $data['status'] = 1;
        $data['createtime'] = time();
        return self::create($data);
    }

    /**
     * 获取商品模型属性列表
     */
    public static function getAttributeGroupList($condition = '',$field = '*',$order = 'attr_id DESC')
    {
        return self::table('plm_product_model_attribute_group')->where($condition)->field($field)->order($order)->select();
    }

    /**
     * 获取商品模型属性信息
     */
    public static function getAttributeGroupInfo($data)
    {
        return self::get($data);
    }

    /**
     * 修改某条信息
     */
    public static function editAttributeGroupInfo($param)
    {
        $where['attr_id'] = $param['attr_id'];
        $data['attr_name'] = $param['attr_name'];
        $data['updatetime'] = time();
        return self::update($data,$where);
    }

    /**
     * 删除某条信息
     */
    public static function deleteAttributeGroupInfo($param)
    {
        $where['attr_id'] = $param['attr_id'];
        $data['status'] = 3;
        $data['updatetime'] = time();
        return self::update($data,$where);
    }
}