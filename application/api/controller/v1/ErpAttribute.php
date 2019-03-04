<?php
/**
 * ERP的基础属性
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/3
 * Time: 15:33
 */
namespace app\api\controller\v1;

use think\Controller;

use app\api\controller\Api;
use app\api\controller\Send;
class ErpAttribute extends Api
{
    protected $needRight = ["getErpAttributeList","editErpAttribute"];
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }
    //查看ERP的基础属性
    public function getErpAttributeList()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        $result = \app\api\model\ErpAttribute::getErpAttributeList($data);

        return $this->returnmsg(200,'success！',$result);

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
            if(isset($params["plm_no"])) {
                $obj = \app\api\model\ErpAttribute::checkList(array("status"=>1,"plm_no"=>$params["plm_no"]));
                if ($params["erp_no"] == $obj["erp_no"]){
                    return;
                }
            }

            $obj = \app\api\model\ErpAttribute::checkList(array("status" => 1, "erp_no" => $params["erp_no"]));
            if($obj){
                return $this->returnmsg(402,'ERP物料编码违背了唯一性原则！');
            }
        }
    }
    //新增、更新ERP的基础属性
    public function editErpAttribute()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        $this->check($data);
        $result = \app\api\model\ErpAttribute::editErpAttribute($data,$this->userInfo['admin_id']);
        if($result){
            return $this->returnmsg(200,'success！',$result);
        }else{
            return $this->returnmsg(402,'Data illegal！');
        }
    }
}
