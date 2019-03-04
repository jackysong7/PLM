<?php
/**
 * Created by PhpStorm.
 * User: chenhailiang
 * Date: 2018/4/2
 * Time: 14:59
 */
namespace app\api\model;

use think\Db;
use think\Model;

class Department extends Model
{
    public static function checkList($data){
        return self::get($data);
    }
    public static function getList($params){
        $where = 'status = 1';

        if(isset($params['page'])&&isset($params['limit'])){
            $count = Db::name('department')
                ->where($where)
                ->count();

            $list = Db::name('department')
                ->where($where)
                ->field('pd_id,department_name,department_code')
                ->page($params['page'],$params['limit'])
                ->select();

            $data = array(
                'list' => $list,
                'page' => ['total_count' => $count,
                    'current_page' => $params['page'],
                    'page_size' => $params['limit'],
                    'total_page' => ceil($count / $params['limit'])],
            );
        }else{
            $result = Db::name('department')
                ->field('pd_id,department_name,department_code')
                ->where($where)
                ->select();
            $data = array(
                'list' => $result
            );
        }
        return $data;
    }

    public static function getInfo($params){
        $department = self::get(['pd_id'=>$params['pd_id']]);
        return $department;
    }

    public static function add($params){
        $data['createtime'] = time();
        $data['updatetime'] = time();
        $data['status'] = 1;
        if(!empty($params['department_name'])){
            $data['department_name'] = $params['department_name'];
        }
        if(!empty($params['department_code'])){
            $data['department_code'] = $params['department_code'];
        }
        if(!empty($params['description'])){
            $data['description'] = $params['description'];
        }
        if(!empty($params['admin_id'])){
            $data['admin_id'] = $params['admin_id'];
        }
        $result = Db::name('department')->insert($data);
        return $result;
    }

    public static function edit($params){
        $department = self::get(['pd_id'=>$params['pd_id']]);
        $data['updatetime'] = time();
        if($department){
            if(!empty($params['department_name'])){
                $data['department_name'] = $params['department_name'];
            }
            if(!empty($params['department_code'])){
                $data['department_code'] = $params['department_code'];
            }
            if(!empty($params['description'])){
                $data['description'] = $params['description'];
            }
            $result = $department->save($data);
            return $result;
        }
    }

    public static function del($params){
        $department = self::get(['pd_id'=>$params['pd_id']]);
        $getJob = Db::name('job')->where(['department_id'=>$department['pd_id'],'status'=>1])->find();
        if(empty($getJob)){
            $result = $department->save(['status'=>3]);
            return $result;
        }else{
            return array();
        }
    }
}