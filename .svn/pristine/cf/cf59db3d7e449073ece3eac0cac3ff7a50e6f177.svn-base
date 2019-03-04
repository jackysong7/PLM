<?php
namespace app\api\model;

use think\Db;
use think\Model;
class Uitableview extends Model
{
    protected $table = 'plm_uitableview';
     
     public static function add ($params)
    {

        $data = array(
            "tpl_id"=>$params['tpl_id'],
            "attr_name"=>$params['attr_name'],
            "type"=>$params['type'],
            "parent_id"=>$params['parent_id'],
            "status"=> 1,
            "admin_id"=>$params['admin_id'],
            "createtime"=>time()
        );
        if (isset($params['sub_type']))
        {
            $data['sub_type'] = intval($params['sub_type']);
        }
        Db::name('uitableview')->insert($data);
        $view_id = Db::name('uitableview')->getLastInsID();
        return array("view_id"=>(int)$view_id);
    }
    
    public static function editUitableview($params)
    {
        $where['view_id'] = $params['view_id'];
        
        if(!empty($params['type'])){
            $data['type'] = intval($params['type']);
        }
        if(!empty($params['attr_name'])){
            $data['attr_name'] = $params['attr_name'];
        }
        if (isset($params['sub_type']))
        {
            $data['sub_type'] = intval($params['sub_type']);
        }
        $data['updatetime'] = time();
        return self::update($data,$where);
    }
    
    public static function getUitableview($condition,$field='*')
    {
        return Db::table('plm_uitableview')->where($condition)->field($field)->find();
    }
    
    
    public static function getValueList($condition = '',$field = '*',$order = 'view_id')
    {
         return Db::table('plm_uitableview')->where($condition)->field($field)->order($order)->select();
    }
    
    public static function getUitableviewValue($condition,$field='*')
    {
        return Db::table('plm_uitableview_value')->where($condition)->field($field)->find();
    }
    
    public static function deleteUitableview($params)
    {
        $where['view_id'] = $params['view_id'];
        return Db::table('plm_uitableview')->where($where)->delete();
    }
}
