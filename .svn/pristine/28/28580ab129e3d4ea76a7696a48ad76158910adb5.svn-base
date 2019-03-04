<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/3
 * Time: 13:59
 */
namespace app\api\model;

use think\Db;
use think\Model;
class Plm extends Model
{
    protected $table = 'plm_basedata';

    public static function checkList($data){
        return self::get($data);
    }

    public static function addPlm($params,$admin_id)
    {
        $addPlm = self::get(['plm_no'=>$params['plm_no']]);
        if(empty($addPlm)){
            $data = array("plm_no"=>$params['plm_no'],"admin_id"=>$admin_id,"createtime"=>time());
            Db::name('basedata')->insert($data);
            $plm_id = Db::name('basedata')->getLastInsID();
            return array("plm_id"=>$plm_id);
        }else{
            return 0;
        }
    }

    public static function updatePlmInfo($params)
    {
        $list = self::get(['plm_no'=>$params['plm_no']]);
        if(empty($list)){
            return false;
        }else{
            $data = array("plm_ttm"=>$params['plm_ttm'],"updatetime"=>time(),"admin_id"=>$params['admin_id']);
            $result = Db::name('basedata')->where('plm_no', $params['plm_no'])->update($data);
            if($result){
                return $result;
            }
        }
    }

    public static function getPlm($plm_no)
    {
        $result = self::get(['plm_no'=>$plm_no]);
        return json_decode(json_encode($result),true);
    }
}