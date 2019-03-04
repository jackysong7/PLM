<?php
namespace app\api\model;

use think\Db;
use think\Model;
class Strategy extends Model
{
    protected $table = 'plm_project_plan';
    
    public static function add ($params)
    {

        $data = array(
            "brand_id"=>$params['brand_id'],
            "project_name"=>$params['project_name'],
            "description"=>$params['description'],
            "publish_time"=>$params['publish_time'],
            "year"=>$params['year'],
            "add_time"=>time()
        );
        Db::name('project_plan')->insert($data);
        $pp_id = Db::name('project_plan')->getLastInsID();
        return array("pp_id"=>(int)$pp_id);
    }
    
    public static function getList($params)
    {
        $where['year'] = $params['year'];

        $count = Db::name('project_plan')
            ->where($where)
            ->count();//总记录数


        $list = Db::name('project_plan')
            ->field('pp_id,brand_id,project_name,description,publish_time')
            ->where($where)->order('pp_id DESC')
            ->page($params['page_no'],$params['page_size'])
            ->select();
        foreach ($list as $key=>$v)
        {
            $list[$key]['brand_name'] = Db::name('brand')->field('brand_name')->where('brand_id', $v['brand_id'])->value('brand_name');
        }
        
        return array("totalNumber"=>$count,"list"=>$list);
    }
    
    public static function editProjectPlan($params)
    {
        $where['pp_id'] = $params['pp_id'];
        
        if(!empty($params['brand_id'])){
            $data['brand_id'] = $params['brand_id'];
        }
        if(!empty($params['project_name'])){
            $data['project_name'] = $params['project_name'];
        }
        if(!empty($params['description'])){
            $data['description'] = $params['description'];
        }
        if(!empty($params['publish_time'])){
            $data['publish_time'] = $params['publish_time'];
        }
        
        return self::update($data,$where);
    }
    
    public static function getProject($condition,$field="*")
    {
        return Db::table('plm_project')->where($condition)->field($field)->find();
    }
    
    public static function deleteProjectPlan($params)
    {
        $where['pp_id'] = $params['pp_id'];
        return Db::table('plm_project_plan')->where($where)->delete();
    }
    public static function getProjectPlan($params)
    {
        return Db::name('project_plan')->field('is_used')->where('pp_id', $params['pp_id'])->value('is_used');
    }
}