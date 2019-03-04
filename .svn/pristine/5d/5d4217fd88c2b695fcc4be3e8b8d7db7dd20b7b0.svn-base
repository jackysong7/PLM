<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/11
 * Time: 9:56
 * 产品模型
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Validate;

class Sample extends Api
{

    //需要权限验证的接口
    protected $needRight = array('getSampleList','addSampleInfo','editSampleInfo','deleteSampleInfo');

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
            if(isset($params["pm_id"])) {
                $obj = \app\api\model\Sample::checkList(array("status"=>1,"pm_id"=>$params["pm_id"]));
                if ($params["pm_name"] == $obj["pm_name"]){
                    return;
                }
            }
            $obj = \app\api\model\Sample::checkList(array("status"=>1,"pm_name"=>$params["pm_name"]));
            if($obj)
            {
                return $this->returnmsg(402,'模型名称违背了唯一性原则！');
            }
        }
    }
    /**
     * 新增模型
     */
    public function addSampleInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'pm_name' => 'require'
        ];

        $msg = [
            'pm_name.require' => '模型名称不能为空'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Sample::addDataInfo($array);
        if($result){
            $info = $result->toArray();
            $pm_id['pm_id'] = $info['pm_id'];
            return $this->returnmsg(200,'新增数据成功',$pm_id);
        }else{
            return $this->returnmsg(400,'新增数据失败');
        }
    }

    /**
     * 模型列表
     */
    public function getSampleList()
    {
        $condition['status'] = 1;
        $field = 'pm_id,pm_name,sort';
        $list = \app\api\model\Sample::getDataList($condition,$field);
        if(!empty($list)){
            return $this->returnmsg(200,'获取数据成功',$list);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }

    /**
     * 修改数据
     */
    public function editSampleInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'pm_id' => 'require|integer'
        ];

        $msg = [
            'pm_id.require' => '产品模型不能为空且必须是数字'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Sample::editDataInfo($array);

        if($result){
            return $this->returnmsg(200,'修改数据成功');
        }else{
            return $this->returnmsg(400,'修改数据失败');
        }
    }

    /**
     * 删除数据
     */
    public function deleteSampleInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'pm_id' => 'require|integer'
        ];

        $msg = [
            'pm_id.require' => '产品模型不能为空且必须是数字'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $productAttributeData = \app\api\model\Product::getProductAttrInfo(array("model_id"=>$array["pm_id"]));
        //验证当前模型下有无数据
        $array["status"] = 1;
        $obj = \app\api\model\Attribute::getAttributeGroupInfo($array);
        if(!empty($obj) || !empty($productAttributeData)){
            return $this->returnmsg(403,'该模型下有数据，不可删除');
        }else{

            //操作者ID
            $array['admin_id'] = $this->userInfo['admin_id'];

            $result = \app\api\model\Sample::deleteDataInfo($array);
            if($result){
                return $this->returnmsg(200,'删除数据成功');
            }else{
                return $this->returnmsg(400,'删除数据失败');
            }
        }
    }
}