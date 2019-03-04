<?php
/**
 * Created by PhpStorm.
 * User: chenhailiang
 * Date: 2018/4/2
 * Time: 14:53
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Controller;

class Job extends Api
{
    //需要权限验证的接口
    protected $needRight = array('getList','add','edit','del');

    /*
     * 获取列表
     */
    public function getList()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $department = \app\api\model\Job::getList($params);
        foreach($department['list'] as $k=>$v){
            $department['list'][$k]['admin_rules'] = $this->build_tree($v['admin_rules'],0);
        }
        $this->returnmsg(200,'操作完成', $department);
    }

    /*
     * 获取
     */
    public function getInfo()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $job = \app\api\model\Job::getInfo($params);
        $job['jobRules'] = $this->build_tree($job['jobRules'],0);
        $this->returnmsg(200,'操作完成', $job);
    }

    //寻找子类
    private function findChild($arr,$id){
        $childs=array();
        foreach ($arr as $k => $v){
            if($v['parent_id']== $id){
                $childs[]=$v;
            }
        }
        return $childs;
    }

    //建立树形结构
    private function build_tree($rows,$root_id){
        $childs=$this->findChild($rows,$root_id);
        if(empty($childs)){
            return null;
        }
        foreach ($childs as $k => $v){
            $rescurTree=$this->build_tree($rows,$v['rule_id']);
            if( null != $rescurTree){
                $childs[$k]['childs']=$rescurTree;
            }
        }
        return $childs;
    }

    /*
     * 检查数据唯一
     */
    public function check($params = ""){
        if (empty($params)) {
            $json = $this->request->param();
            $params = json_decode($json['data'], true);
        }
        if (!empty($params)){
            if(isset($params["pj_id"])) {
                $obj = \app\api\model\Job::checkList(array("status" => 1, "pj_id" => $params["pj_id"]));
                if (isset($params["job_code"]) && $params["job_code"] == $obj["job_code"] && isset($params["job_name"]) && $params["job_name"] == $obj["job_name"]) {
                    return;
                }else if(isset($params["job_code"]) && $params["job_code"] == $obj["job_code"] && !isset($params["job_name"])){
                    return;
                }else if(!isset($params["job_code"]) && isset($params["job_name"]) && $params["job_name"] == $obj["job_name"]){
                    return;
                }
            }
            if (isset($params["job_name"])){
                if(isset($params["pj_id"])) {
                    if ($params["job_name"] == $obj["job_name"]){
                        return;
                    }
                }
                $objcheck = \app\api\model\Job::checkList(array("status"=>1,"job_name"=>$params["job_name"]));
                if($objcheck)
                {
                    return $this->returnmsg(402,'岗位名称违背了唯一性原则！');
                }
            }
            if (isset($params["job_code"])){
                if(isset($params["pj_id"])) {
                    if ($params["job_code"] == $obj["job_code"]){
                        return;
                    }
                }
                $objcheck = \app\api\model\Job::checkList(array("status"=>1,"job_code"=>$params["job_code"]));
                if($objcheck)
                {
                    return $this->returnmsg(402,'岗位编码违背了唯一性原则！');
                }
            }
        }
    }

    /*
     * 新增
     */
    public function add()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $this->check($params);
        $admin = $this->auth->getUser();
        if(!empty($admin)){
            $params['admin_id'] = $admin['admin_id'];
        }
        $job = \app\api\model\Job::add($params);
        $this->returnmsg(200,'操作完成', $job);
    }

    /*
     * 修改
     */
    public function edit()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $this->check($params);
        $job =\app\api\model\Job::edit($params);
        $this->returnmsg(200,'操作完成', $job);
    }

    /*
     * 删除
     */
    public function del()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        //验证当前模型下有无数据
        /*$array['job_id'] = $params["pj_id"];
        $obj = \app\api\model\AdminInfo::checkList($array);
        if(!empty($obj)){
            return $this->returnmsg(403,'该岗位下有其对应账号的数据，不可删除');
        }*/
        $job = \app\api\model\Job::del($params);
        if(empty($job)){
            $this->returnmsg(403,'该岗位下有其对应账号的数据，不可删除！', $job);
        }
        $this->returnmsg(200,'操作完成', $job);
    }
}