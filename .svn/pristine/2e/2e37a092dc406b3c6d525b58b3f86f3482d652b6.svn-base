<?php

namespace app\api\model;

use think\Db;
use think\Model;
class Annual extends Model
{
    protected $table = 'plm_year';
    
    public static function add ($params)
    {
        $where['year'] = $params['year'];
        $where['type'] = $params['type'];
        $info  = Db::table('plm_year')->where($where)->field('*')->find();

        if(empty($info))
        {
            $data = array(
                "year"=>$params['year'],
                "type"=>$params['type'],
                "add_time"=>time()
            );
            Db::name('year')->insert($data);
            $id = Db::name('year')->getLastInsID();
            return array("id"=>(int)$id);
        }else{
            return 2;
        }
    }
    
    public static function editYear($param)
    {
        $where['id'] = $param['id'];
        $data['year'] = $param['year'];
        $data['type'] = $param['type'];
        return self::update($data,$where);
    }
    
    public static function getList()
    {
        $where['status'] = 1;
        $where['type'] = 1;
        $array = Db::name('year')->field('id,year')->where($where)->select();
        if($array){
            return $array;
        }else{
            return [];
        }
    }
}

