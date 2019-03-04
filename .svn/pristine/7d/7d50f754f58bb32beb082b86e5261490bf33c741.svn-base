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

class Job extends Model
{
    public static function checkList($data){
        return self::get($data);
    }
    public static function getList($params){
        $where = 'j.status = 1';
        if(isset($params['department_id'])){
            $where .=" AND j.department_id = ".$params['department_id'];
        }
        if(isset($params['page'])&&isset($params['limit'])){
            $count = Db::name('job')
                ->alias('j')
                ->join('department d','j.department_id = d.pd_id','LEFT')
                ->where($where)
                ->count();

            $list = Db::name('job')
                ->alias('j')
                ->field('j.pj_id,j.job_name,j.job_code,j.rules,j.description,d.pd_id,d.department_name')
                ->join('department d','j.department_id = d.pd_id','LEFT')
                ->where($where)
                ->page($params['page'],$params['limit'])
                ->select();

            //获取权限列表
            $admin_rule = Db::name('admin_rule')
                ->field('rule_id,parent_id,rule_name')
                ->where('status = 1')
                ->order('rule_id desc')
                ->select();

            foreach($list as $key=>$value){
                $rules = explode(',',$value['rules']);
                foreach($admin_rule as $k=>$v){
                    if(in_array($v['rule_id'],$rules)){
                        $getRules[] = $admin_rule[$k];
                    }
                }
                $list[$key]['admin_rules'] = $getRules;
                unset($getRules);
            }

            $data = array(
                'list' => $list,
                'page' => ['total_count' => $count,
                    'current_page' => $params['page'],
                    'page_size' => $params['limit'],
                    'total_page' => ceil($count / $params['limit'])],
            );
        }else{
            $list = Db::name('job')
                ->alias('j')
                ->field('j.pj_id,j.job_name,j.job_code,j.rules,j.description,d.pd_id,d.department_name')
                ->join('department d','j.department_id = d.pd_id','LEFT')
                ->where($where)
                ->select();
            //获取权限列表
            $admin_rule = Db::name('admin_rule')
                ->field('rule_id,parent_id,rule_name')
                ->where('status = 1')
                ->order('rule_id desc')
                ->select();

            foreach($list as $key=>$value){
                $rules = explode(',',$value['rules']);
                foreach($admin_rule as $k=>$v){
                    if(in_array($v['rule_id'],$rules)){
                        $getRules[] = $admin_rule[$k];
                    }
                }
                $list[$key]['admin_rules'] = $getRules;
                unset($getRules);
            }
            $data = array(
                'list' => $list,
            );
        }
        return $data;
    }

    public static function getInfo($params){
        $job = Db::name('job')
            ->alias('j')
            ->field('j.pj_id,j.job_name,j.rules,d.pd_id as department_id,d.department_name')
            ->join('department d','j.department_id = d.pd_id','LEFT')
            ->where('j.status = 1 AND j.pj_id = '.$params['pj_id'])
            ->find();

        if(!empty($job)){
            //部门
            $getDepartment = Db::name('department')
                ->field('pd_id,department_name')
                ->where('status = 1')
                ->order('pd_id desc')
                ->select();

            if(!empty($getDepartment)){
                foreach($getDepartment as $key=>$value){
                    if($value['pd_id'] == $job['department_id']){
                        $department[] = array_merge($getDepartment[$key],["checked"=>true]);
                    }else{
                        $department[] = array_merge($getDepartment[$key],["checked"=>false]);
                    }
                }
            }

            $list = Db::name('admin_rule')
                ->field('rule_id,parent_id,rule_name')
                ->where('status = 1')
                ->order('rule_id desc')
                ->select();
            foreach($list as $k=>$v){
                $rules = explode(',',$job['rules']);
                if(in_array($v['rule_id'],$rules)){
                    $data[] = array_merge($list[$k],["checked"=>true]);
                }else{
                    $data[] = array_merge($list[$k],["checked"=>false]);
                }
            }
            $result = [
                'department'=>$department,
                'jobInfo'=>$job,
                'jobRules'=>$data
            ];
            return $result;
        }
        return array();
    }

    public static function add($params){
        $data['createtime'] = time();
        $data['updatetime'] = time();
        $data['status'] = 1;
        if(!empty($params['department_id'])){
            $data['department_id'] = $params['department_id'];
        }
        if(!empty($params['job_name'])){
            $data['job_name'] = $params['job_name'];
        }
        if(!empty($params['job_code'])){
            $data['job_code'] = $params['job_code'];
        }
        if(!empty($params['description'])){
            $data['description'] = $params['description'];
        }
        if(!empty($params['rules'])){
            $data['rules'] = $params['rules'];
        }
        if(!empty($params['admin_id'])){
            $data['admin_id'] = $params['admin_id'];
        }
        $result = Db::name('job')->insert($data);
        return $result;
    }

    public static function edit($params){
        $job = self::get(['pj_id'=>$params['pj_id']]);
        $data['updatetime'] = time();
        if($job){
            if(!empty($params['department_id'])) {
                $data['department_id'] = $params['department_id'];
                $isChangeD = $job["department_id"] != $params['department_id'] ? true : false;
            }
            if(!empty($params['job_name'])){
                $data['job_name'] = $params['job_name'];
            }
            if(!empty($params['job_code'])){
                $data['job_code'] = $params['job_code'];
            }
            if(!empty($params['description'])){
                $data['description'] = $params['description'];
            }
            if(!empty($params['rules'])){
                $data['rules'] = $params['rules'];
                $isChangeR = $job["rules"] != $params['rules'] ? true : false;
            }
            $result = $job->save($data);
            if($result){
                //修改成功，判断权限是否发生改变
                if($isChangeR){
                    Admin::updateRules($params['pj_id'],array('rules'=>$params['rules']));
                }
                //修改成功，判断部门是否发生改变
                if($isChangeD){
                    Admin::updateRules($params['pj_id'],array('department_id'=>$params['department_id']));
                }
            }
            return $result;
        }
    }

    public static function del($params){
        $job = self::get(['pj_id'=>$params['pj_id']]);
        if($job){
            $getuser = Db::name('admin_info')->where(['job_id'=>$job['pj_id']])->find();
            if(empty($getuser)){
                $result = $job->save(['status'=>3]);
                return $result;
            }else{
                return array();
            }
        }
    }

    /**
     * 获取数据列表
     */
    public static function getJobDataList($condition = '',$field = '*',$order = 'pj_id')
    {
        return Db::table('plm_job')->where($condition)->field($field)->order($order)->select();
    }
}