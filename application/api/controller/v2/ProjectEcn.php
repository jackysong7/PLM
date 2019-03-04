<?php
/**
 * Created by PhpStorm.
 * User: chenhailiang
 * Date: 2018/7/6
 * Time: 16:00
 */
namespace app\api\controller\v2;

use app\api\controller\Api;
use think\Controller;
use think\Validate;
class ProjectEcn extends Api
{
    //需要权限验证的接口
    protected $needRight = array();

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }

    /*
     * 获取列表
     */
    public function getList()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $admin = $this->auth->getUser();
        if(!empty($admin)){
            $params['admin_id'] = $admin['admin_id'];
        }
        $projectEcn = \app\api\model\ProjectEcn::getList($params);

        $this->returnmsg(200,'操作完成', $projectEcn);
    }

    /*
     * 获取详情
     */
    public function getInfo()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $projectEcn = \app\api\model\ProjectEcn::getInfo($params);

        $this->returnmsg(200,'操作完成', $projectEcn);
    }

    /*
     *获取所有上市项目
     */
    public function getAllProject()
    {
        $allProject = \app\api\model\ProjectEcn::getAllProject();
        $this->returnmsg(200,'操作完成', $allProject);
    }

    /*
     *获取项目组文件
     */
    public function getProjectDoc()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $projectDoc = \app\api\model\ProjectEcn::getProjectDoc($params);
        $this->returnmsg(200,'操作完成', $projectDoc);
    }

    /*
     * 新增工程
     */
    public function createProjectEcn()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $rule = [
            'final_auditor' => 'require',
        ];
        $msg = [
            'final_auditor.require' => '最终审核人不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($params);

        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }

        $admin = $this->auth->getUser();
        if(!empty($admin)){
            $params['admin_id'] = $admin['admin_id'];
        }

        $createProjectEcn = \app\api\model\ProjectEcn::createProjectEcn($params);
        if($createProjectEcn){
            $this->returnmsg(200,'操作完成', $createProjectEcn);
        }else{
            return $this->returnmsg(402,'新增工程变更失败！');
        }
    }

    /**
     * 检查创建项目节点是否存在
     */
    public function checkCreate()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $result = \app\api\model\ProjectEcn::checkCreate($params);
        $this->returnmsg(200,'操作完成', $result);
    }

    /*
     * 获取需变更物料编码
     */
    public function getChangeMaterial()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        //数据验证
        $result = $this->validate($params, [
            'change_material' => 'require',
            'project_id' => 'require|integer|>:0',
        ]);

        if ($result !== true)
        {
            return $this->returnmsg(401,$result);
        }
        $getChangeMaterial = \app\api\model\ProjectEcn::getChangeMaterial($params);
        $this->returnmsg(200,'操作完成', $getChangeMaterial);
    }

    /*
    * 获取物料产品分类表
    */
    public function getMaterial()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $getChangeMaterial = \app\api\model\ProjectEcn::getMaterial($params);
        $this->returnmsg(200,'操作完成', $getChangeMaterial);
    }

    /*
    * 工程变更审核
    */
    public function accraditation()
    {
        $data = $this->request->param();
        $params = json_decode($data['data'],true);
        $admin = $this->auth->getUser();
        if(!empty($admin)){
            $params['admin_id'] = $admin['admin_id'];
        }
        $is_check = \app\api\model\ProjectEcn::isRelation($params);
        if($is_check == 0){
            return $this->returnmsg(402,'当前用户不存在变更审核人ID组里！');
        }
        $getChangeMaterial = \app\api\model\ProjectEcn::accraditation($params);
        if($getChangeMaterial){
            $this->returnmsg(200,'操作完成', $getChangeMaterial);
        }else{
            return $this->returnmsg(402,'工程变更审核失败！');
        }
    }
}