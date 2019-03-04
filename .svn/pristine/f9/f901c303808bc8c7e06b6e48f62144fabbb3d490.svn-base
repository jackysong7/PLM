<?php
/**
 * PLM物料编码
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/3
 * Time: 13:53
 */
namespace app\api\controller\v1;

use think\Controller;

use app\api\controller\Api;
use app\api\controller\Send;
class Plm extends Api
{
    protected $needRight = ["addPlm","updatePlmInfo"];
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }

    //检查PLM编码是否唯一
    public function check($params){
        if (!empty($params)){
            $obj = \app\api\model\Plm::checkList(array("plm_no"=>$params["plm_no"]));
            if($obj)
            {
                return $this->returnmsg(402,'PLM物料编码违背了唯一性原则！');
            }
        }
    }

    //新增PLM成品基础信息
    public function addPlm()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $admin_id = $this->userInfo['admin_id'];
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        $this->check($data);
        $addPlm = \app\api\model\Plm::addPlm($data,$admin_id);

        if($addPlm){
            return $this->returnmsg(200,'success！',$addPlm);
        }else{
            return $this->returnmsg(402,'PLM物料编码违背了唯一性原则！');
        }
    }
    //更新PLM基础属性信息
    public function updatePlmInfo()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        if(empty($data['plm_ttm'])){
            return $this->returnmsg(401,'上市时间不能为空！');
        }
        $data['admin_id'] = $this->userInfo['admin_id'];
        $updatePlm = \app\api\model\Plm::updatePlmInfo($data);

        if($updatePlm == false){
            return $this->returnmsg(402,'Data illegal！');
        }else{
            return $this->returnmsg(200,'success!');
        }
    }
    //获取PLM信息
    public function getPlm()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $plm_no = $data['plm_no'];
        return $result = \app\api\model\Plm::getPlm($plm_no);
    }
}