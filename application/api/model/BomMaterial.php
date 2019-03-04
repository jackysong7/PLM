<?php

namespace app\api\model;

use think\Model;
use think\Db;

class BomMaterial extends Model
{
	 /*
     * 新增BOM表与物料的关系表
     */
    public static function add($params)
    {

        if(isset($params['relative']))
        {
            $params['relative'] = 1;
        }

        $result = Db::name('bom_material')->insert($params);
        return $result;
    }


    /*
     * 通过bom_id查询对应的父项物料数据
     */
    public static function getMaterialInfo($bomId)
    {
        $list = Db::name('bom_material')
            ->field('material_sn,material_name,specification,unit')
            ->where('bom_id',$bomId)
            ->where('relative',1)
            ->find();

        return $list;
    }

    /*
     * 通过bom_id查询对应的子项物料数据
     */
    public static function getSonMaterialInfo($bomId)
    {
        $list = Db::name('bom_material')
            ->field('material_sn,material_name,specification,numerator_amount,denominator_amount,unit,remark,location_num')
            ->where('bom_id',$bomId)
            ->where('relative',0)
            ->select();

        return $list;
    }

    //修改数据
    public static function updateInfo($where,$data)
    {
        return Db::name('bom_material')->where($where)->update($data);
    }

    //删除数据
    public static function del($where)
    {
        return Db::name('bom_material')->where($where)->delete();
    }

    public static function materialSn($where,$field='*')
    {
        return Db::name('bom_material')->where($where)->field($field)->find();
    }
}