<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/3
 * Time: 16:22
 */
namespace app\api\model;

use think\Db;
use think\Model;
class ErpAttribute extends Model
{
    protected $table = 'plm_erp_attribute';

    public static function checkList($data){
        return self::get($data);
    }

    public static function getErpAttributeList($params)
    {
        $result = self::get(['plm_no'=>$params['plm_no']]);
        return json_decode(json_encode($result),true);
    }

    public static function editErpAttribute($params,$admin_id)
    {
        $result = self::get(['plm_no'=>$params['plm_no']]);
        if(empty($result)){
            //新增
            $data = array(
                "plm_no"=>$params['plm_no'],
                "erp_no"=>$params['erp_no'],
                "create_group"=>$params['create_group'],
                "erp_name"=>$params['erp_name'],
                "use_group"=>$params['use_group'],
                "material_attr"=>$params['material_attr'],
                "basic_unit"=>$params['basic_unit'],
                "model_attr"=>$params['model_attr'],
                "material_group"=>$params['material_group'],
                "suite"=>$params['suite'],
                "material_properties"=>$params['material_properties'],
                "bar_code"=>$params['bar_code'],
                "admin_id"=>$admin_id,
                "status"=>1,
                "createtime"=>time()
            );
            Db::name('erp_attribute')->insert($data);
            $ea_id = Db::name('erp_attribute')->getLastInsID();
            return array("ea_id"=>$ea_id);
        }else{
            //更新
            if(!empty($params['erp_no'])){
                $params['erp_no'] = $params['erp_no'];
            }
            if(!empty($params['create_group'])){
                $params['create_group'] = $params['create_group'];
            }
            if(!empty($params['erp_name'])){
                $params['erp_name'] = $params['erp_name'];
            }
            if(!empty($params['use_group'])){
                $params['use_group'] = $params['use_group'];
            }
            if(!empty($params['material_attr'])){
                $params['material_attr'] = $params['material_attr'];
            }
            if(!empty($params['basic_unit'])){
                $params['basic_unit'] = $params['basic_unit'];
            }
            if(!empty($params['model_attr'])){
                $params['model_attr'] = $params['model_attr'];
            }
            if(!empty($params['material_group'])){
                $params['material_group'] = $params['material_group'];
            }
            if(!empty($params['suite'])){
                $params['suite'] = $params['suite'];
            }
            if(!empty($params['material_properties'])){
                $params['material_properties'] = $params['material_properties'];
            }
            if(!empty($params['bar_code'])){
                $params['bar_code'] = $params['bar_code'];
            }
            $data = array(
                "erp_no"=>$params['erp_no'],
                "create_group"=>$params['create_group'],
                "erp_name"=>$params['erp_name'],
                "use_group"=>$params['use_group'],
                "material_attr"=>$params['material_attr'],
                "basic_unit"=>$params['basic_unit'],
                "model_attr"=>$params['model_attr'],
                "material_group"=>$params['material_group'],
                "suite"=>$params['suite'],
                "material_properties"=>$params['material_properties'],
                "bar_code"=>$params['bar_code'],
                "admin_id"=>$admin_id,
                "updatetime"=>time()
            );
            return Db::name('erp_attribute')->where('plm_no', $params['plm_no'])->update($data);
        }
    }

    /*
     * 根据PLM物料编码获取erp物料编码
     */
    public static function materialData($where,$field)
    {
        return Db::name('erp_attribute')->where($where)->field($field)->find();
    }
}