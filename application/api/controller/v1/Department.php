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

class Department extends Api
{
    //需要权限验证的接口
    protected $needRight = array('getList','add','edit','del');

    /*
     * 检查数据唯一
     */
    public function check($params = ""){
        if (empty($params)) {
            $json = $this->request->param();
            $params = json_decode($json['data'], true);
        }
        if (!empty($params)){
            if(isset($params["pd_id"])) {
                $obj = \app\api\model\Department::checkList(array("status" => 1, "pd_id" => $params["pd_id"]));
                if (isset($params["department_code"]) && $params["department_code"] == $obj["department_code"] && isset($params["department_name"]) && $params["department_name"] == $obj["department_name"]) {
                    return;
                }else if(isset($params["department_code"]) && $params["department_code"] == $obj["department_code"] && !isset($params["department_name"])){
                    return;
                }else if(!isset($params["department_code"]) && isset($params["department_name"]) && $params["department_name"] == $obj["department_name"]){
                    return;
                }
            }
            if (isset($params["department_name"])){
                if(isset($params["pd_id"])) {
                    if ($params["department_name"] == $obj["department_name"]){
                        return;
                    }
                }
                $objcheck = \app\api\model\Department::checkList(array("status"=>1,"department_name"=>$params["department_name"]));
                if($objcheck)
                {
                    return $this->returnmsg(402,'部门名称违背了唯一性原则！');
                }
            }
            if (isset($params["department_code"])){
                if(isset($params["pd_id"])) {
                    if ($params["department_code"] == $obj["department_code"]){
                        return;
                    }
                }
                $objcheck = \app\api\model\Department::checkList(array("status"=>1,"department_code"=>$params["department_code"]));
                if($objcheck)
                {
                    return $this->returnmsg(402,'部门编码违背了唯一性原则！');
                }
            }
        }
    }
    /*
     * 获取列表
     */
    public function getList()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $department = \app\api\model\Department::getList($params);
        $this->returnmsg(200,'操作完成', $department);
    }

    /*
     * 获取
     */
    public function getInfo()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $department = \app\api\model\Department::getInfo($params);
        $this->returnmsg(200,'操作完成', $department);
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
        $department = \app\api\model\Department::add($params);
        $this->returnmsg(200,'操作完成', $department);
    }

    /*
     * 修改
     */
    public function edit()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $this->check($params);
        $department =\app\api\model\Department::edit($params);
        $this->returnmsg(200,'操作完成', $department);
    }

    /*
     * 删除
     */
    public function del()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        //验证当前模型下有无数据
        /*$array['department_id'] = $params["pd_id"];
        $array['status'] = 1;
        $obj = \app\api\model\Job::checkList($array);
        if(!empty($obj)){
            return $this->returnmsg(403,'该部门下有其对应岗位的数据，不可删除');
        }*/
        $department = \app\api\model\Department::del($params);
        if(empty($department)){
            $this->returnmsg(403,'该部门下有其对应岗位的数据，不可删除！', $department);
        }
        $this->returnmsg(200,'操作完成', $department);
    }
}