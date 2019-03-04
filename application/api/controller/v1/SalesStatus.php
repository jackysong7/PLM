<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/3
 * Time: 15:10
 * 销售状态
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Validate;

class SalesStatus extends Api
{
    //需要权限验证的接口
    protected $needRight = array('getSalesStatusList','addSalesStatusInfo','editSalesStatusInfo','deleteSalesStatusInfo');

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
            if(isset($params["ss_id"])) {
                $obj = \app\api\model\SalesStatus::checkList(array("status"=>1,"ss_id"=>$params["ss_id"]));
                if ($params["ss_name"] == $obj["ss_name"]){
                    return;
                }
            }
            $obj = \app\api\model\SalesStatus::checkList(array("status"=>1,"ss_name"=>$params["ss_name"]));
            if($obj)
            {
                return $this->returnmsg(402,'销售状态名称违背了唯一性原则！');
            }
        }
    }
    /**
     * 添加销售状态
     */
    public function addSalesStatusInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'ss_name' => 'require'
        ];

        $msg = [
            'ss_id.require' => '销售状态名称不能为空'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\SalesStatus::insertSalesStatusInfo($array);

        if($result){
            $info = $result->toArray();
            $ss_id['ss_id'] = $info['ss_id'];
            return $this->returnmsg(200,'新增数据成功',$ss_id);
        }else{
            return $this->returnmsg(400,'新增数据失败');
        }
    }

    /**
     * 数据列表
     */
    public function getSalesStatusList()
    {
        $salesStatusWhere['status'] = array('NEQ',3);
        $salesStatusField = 'ss_id,ss_name,sort';

        $list = \app\api\model\SalesStatus::getDataList($salesStatusWhere,$salesStatusField,'sort DESC');

        if(!empty($list)){
            return $this->returnmsg(200,'获取数据成功',$list);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }

    /**
     * 获取某条信息
     */
    public function getSalesStatusInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'ss_id' => 'require|integer'
        ];

        $msg = [
            'ss_id.require' => '销售状态ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $data = \app\api\model\SalesStatus::getDataInfo($array);
        if(!empty($data)){
            return $this->returnmsg(200,'获取数据成功',$data);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }

    }

    /**
     * 修改数据
     */
    public function editSalesStatusInfo(){
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'ss_id' => 'require|integer'
        ];

        $msg = [
            'ss_id.require' => '销售状态ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result =  \app\api\model\SalesStatus::editDataInfo($array);
        if($result){
            return $this->returnmsg('200','修改数据成功');
        }else{
            return $this->returnmsg('400','修改数据失败');
        }
    }

    /**
     * 删除数据
     */
    public function deleteSalesStatusInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'ss_id' => 'require|integer'
        ];

        $msg = [
            'ss_id.require' => '销售状态ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $productAttributeData = \app\api\model\Product::getProductAttrInfo(array("sales_status_id"=>$array["ss_id"]));
        if(empty($productAttributeData)){
            //操作者ID
            $array['admin_id'] = $this->userInfo['admin_id'];

            $result = \app\api\model\SalesStatus::deleteDataInfo($array);
            if($result){
                return $this->returnmsg('200','删除数据成功');
            }else{
                return $this->returnmsg('400','删除数据失败');
            }
        }else{
            return $this->returnmsg(403,'其类别下有数据，不可删除');
        }
    }
}