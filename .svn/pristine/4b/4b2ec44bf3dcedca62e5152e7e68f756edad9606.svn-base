<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/3
 * Time: 15:39
 * PLM项目文档目录属性
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Validate;

class Dir extends Api
{
    //需要权限验证的接口
    protected $needRight = array('addDir','deleteDir');

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
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
            if(isset($params["plm_dir_id"])) {
                $obj = \app\api\model\Dir::checkList(array("status"=>1,"plm_dir_id"=>$params["plm_dir_id"]));
                if ($params["plm_dir_name"] == $obj["plm_dir_name"]){
                    return;
                }
            }
            $obj = \app\api\model\Dir::checkList(array("status"=>1,"plm_dir_name"=>$params["plm_dir_name"]));
            if($obj)
            {
                return $this->returnmsg(402,'项目文档名称违背了唯一性原则！');
            }
        }
    }

    /**
     * 新增PLM项目文档目录属性
     */
    public function addDir()
    {

        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'plm_dir_name' => 'require',
            'job_list' => 'require'
        ];

        $msg = [
            'plm_dir_name.require' => 'PLM项目文档目录属性名称不能为空',
            'job_list.require' => '所属岗位ID组不能为空'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $this->check($array);

        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Dir::insertDirInfo($array);

        if($result){
            $info = $result->toArray();
            $plm_dir_id['plm_dir_id'] = $info['plm_dir_id'];
            return $this->returnmsg(200,'新增操作成功',$plm_dir_id);
        }else{
            return $this->returnmsg(400,'新增操作失败');
        }

    }

    /**
     * 获取PLM项目文档目录属性列表
     */
    public function getDirList()
    {
        $dirWhere['status'] = array('NEQ',3);
        $dirField = 'plm_dir_id,plm_dir_name,job_list,sort';
        $list = \app\api\model\Dir::getDirDataList($dirWhere,$dirField);
        if(!empty($list)){

            foreach($list as $key => $val){
                if(!empty($val['job_list'])){
                    $list[$key]['job_list'] = $this->getJobList($val['job_list']);
                }
            }
            return $this->returnmsg(200,'获取数据成功',$list);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }

    /*
     * 获取岗位列表
     */
    private function getJobList($jobListStr)
    {
        //取出所有的岗位信息
        $jobWhere['pj_id'] = array('IN',$jobListStr);
        $jobWhere['parent_id'] = 0;
        $jobWhere['status'] = 1;
        $jobField = 'pj_id,job_name';
        $jobList = \app\api\model\Job::getJobDataList($jobWhere,$jobField);

        $newJobList = '';
        if(!empty($jobList))
        {
            foreach($jobList as $key => $val){
                $newJobList .= $val['job_name'] . ',';
            }
        }
        return $newJobList = rtrim($newJobList,',');
    }

    /**
     * 获取单条信息
     */
    public function getDirInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'plm_dir_id' => 'require|integer'
        ];

        $msg = [
            'plm_dir_id.require' => '目录ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $info = \app\api\model\Dir::getDirDataInfo($array);

        if(!empty($info)){
            //将所属岗位ID组的字符串，变成数组
            if(!empty($info['job_list'])){
                $row = explode(',',$info['job_list']);
            }
            //取出所有的岗位信息
            $jobWhere['parent_id'] = 0;
            $jobWhere['status'] = 1;
            $jobField = 'pj_id,job_name';
            $jobList = \app\api\model\Job::getJobDataList($jobWhere,$jobField);

            if(!empty($jobList)){
                foreach($jobList as $key => $val){
                    //判断岗位是否被选中
                    if(in_array($val['pj_id'],$row)){
                        $jobList[$key]['checked'] = true;
                    }else{
                        $jobList[$key]['checked'] = false;
                    }
                }
            }

            $info['job_list'] = $jobList;

            return $this->returnmsg(200,'获取数据成功',$info);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }

    /**
     * 修改某条数据
     */
    public function updateDir()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'plm_dir_id' => 'require|integer',
            'job_list' => 'require'
        ];

        $msg = [
            'plm_dir_id.require' => '目录ID不能为空且必须是数字',
            'job_list.require' => '所属岗位ID组不能为空'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Dir::editDirInfo($array);
        if(!empty($result)){
            return $this->returnmsg(200,'修改数据成功');
        }else{
            return $this->returnmsg(400,'修改数据失败');
        }
    }

    /**
     * 删除数据
     */
    public function deleteDir()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'plm_dir_id' => 'require|integer'
        ];

        $msg = [
            'plm_dir_id.require' => '目录ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        //验证其目录下有无数据
        $documentWhere['plm_dir_id'] = $array['plm_dir_id'];
        $documentWhere['status'] = 1;
        $documentInfo = \app\api\model\Document::getDocumentDataInfo($documentWhere);
        if(empty($documentInfo)){

            //操作者ID
            $array['admin_id'] = $this->userInfo['admin_id'];

            $result = \app\api\model\Dir::deleteDirInfo($array);
            if(!empty($result)){
                return $this->returnmsg(200,'删除数据成功');
            }else{
                return $this->returnmsg(400,'删除数据失败');
            }
        }else{
            return $this->returnmsg(403,'其类别下有数据，不可删除');
        }

    }
}