<?php
namespace app\api\model;

use think\Db;
use think\Model;
class Template extends Model
{
     protected $table = 'plm_doc_template';
     
     public static function add ($params)
    {

        $data = array(
            "tpl_name"=>$params['tpl_name'],
            "tpl_type"=>$params['tpl_type'],
            "status"=> 1,
            "add_time"=>time()
        );
        Db::name('doc_template')->insert($data);
        $tpl_id = Db::name('doc_template')->getLastInsID();
        return array("tpl_id"=>(int)$tpl_id);
    }
    
    public static function getList($params)
    {
        $where['status'] = 1;

        $count = Db::name('doc_template')
            ->where($where)
            ->count();//总记录数


        $list = Db::name('doc_template')
            ->field('tpl_id,tpl_name,tpl_type')
            ->where($where)->order('add_time DESC')
            ->page($params['page_no'],$params['page_size'])
            ->select();
        
        return array("totalNumber"=>$count,"list"=>$list);
    }
    
    public static function editDocTemplate($params)
    {
        $where['tpl_id'] = $params['tpl_id'];
        
        if(!empty($params['tpl_name'])){
            $data['tpl_name'] = $params['tpl_name'];
        }
        if(!empty($params['tpl_type'])){
            $data['tpl_type'] = $params['tpl_type'];
        }
        
        return self::update($data,$where);
    }
    
    public static function getProjectNode($tpl_id)
    {
        $where = "1 = 1";
        $str = "FIND_IN_SET($tpl_id,doc_tpl_ids)";
        $where .= " AND ($str)";
        return $list = Db::name('project_node')
            ->field('*')
            ->where($where)
            ->select();
    }
    
    
    public static function getProjectEcn($condition,$field="*")
    {
        return Db::table('plm_project_ecn')->where($condition)->field($field)->find();
    }
    
    
    public static function getUitableview($condition,$field="*")
    {
        return Db::table('plm_uitableview')->where($condition)->field($field)->find();
    }
    
    
    public static function deleteProjectNode($params)
    {
        $where['tpl_id'] = $params['tpl_id'];
        return Db::table('plm_doc_template')->where($where)->delete();
    }
}