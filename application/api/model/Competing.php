<?php
/**
 * User: liuj
 * Date: 2018/4/2
 * Time: 11:10
 */
namespace app\api\model;

use think\Db;
use think\Model;

class Competing extends Model
{
    protected $table = 'plm_competing_goods';
    
    public static function getCompetingList($params)
    {
        $where = 'status = 1 AND plm_no = "'.$params['plm_no'].'"';
        $list = Db::name('competing_goods')
            ->field('cg_id,plm_no,competing_name,sort,competing_path')
            ->where($where)->order('sort desc')
            ->select();
        
        return $list;
    }
    
    public static function addCompeting($params,$admin_id)
    {
        $data = array(
            "plm_no" => $params['plm_no'],
            "competing_name" => $params['competing_name'],
            "competing_path" => $params['competing_path'],
            "sort" => isset($params['sort']) ? $params['sort'] : 0,
            "admin_id" => $admin_id,
            "status"=>1,
            "createtime"=> time()
        );  
        Db::name('competing_goods')->insert($data);
        $cg_id = Db::name('competing_goods')->getLastInsID();
        return array("cg_id"=>$cg_id);
    }
    
    public static function delCompeting($params)
    {
        $delCompeting = self::get(['cg_id'=>$params['cg_id']]);
 
        if($delCompeting){
            $result = Db::name('competing_goods')->where('cg_id', $params['cg_id'])->update(['status' => 3,'updatetime' => time(),'admin_id'=>$params['admin_id']]);
            return $result;
        }
    }
    
    public static function editCompeting($params)
    {
        $editCompeting = self::get(['cg_id'=>$params['cg_id']]);
        
        if($editCompeting){
            if(!empty($params['competing_name'])){
                $params['competing_name'] = $params['competing_name'];
            }
            if(!empty($params['competing_path'])){
                $params['competing_path'] = $params['competing_path'];
            }
            if(!empty($params['sort'])){
                $params['sort'] = $params['sort'];
            }
            $data = array(
                "competing_name"=>$params['competing_name'],
                "competing_path"=>$params['competing_path'],
                "sort"=>$params['sort'],
                "admin_id"=>$params['admin_id'],
                "updatetime"=>time(),
            );
            $result = Db::name('competing_goods')->where('cg_id', $params['cg_id'])->update($data);
            return $result;
        }else{
            return 0;
        }
    }
}