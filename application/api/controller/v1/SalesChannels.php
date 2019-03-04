<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/4
 * Time: 13:39
 * 销售渠道
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Validate;

class SalesChannels extends Api
{
    //需要权限验证的接口
    protected $needRight = array('getSalesChannelsList','addSalesChannelsInfo','editSalesChannelsInfo','deleteSalesChannelsInfo');

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
            if(isset($params["sc_id"])) {
                $obj = \app\api\model\SalesChannels::checkList(array("status"=>1,"sc_id"=>$params["sc_id"]));
                if ($params["sc_name"] == $obj["sc_name"]){
                    return;
                }
            }

            $obj = \app\api\model\SalesChannels::checkList(array("status" => 1, "sc_name" => $params["sc_name"]));
            if($obj){
                return $this->returnmsg(402,'销售渠道名称违背了唯一性原则！');
            }
        }
    }
    /**
     * 新增信息
     */
    public function addSalesChannelsInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'sc_name' => 'require'
        ];

        $msg = [
            'sc_name.require' => '销售渠道名称不能为空'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\SalesChannels::insertInfo($array);

        if($result){
            $info = $result->toArray();
            $sc_id['sc_id'] = $info['sc_id'];
            return $this->returnmsg(200,'新增数据成功',$sc_id);
        }else{
            return $this->returnmsg(400,'新增数据失败');
        }
    }

    /**
     * 获取数据列表
     */
    public function getSalesChannelsList()
    {
        $condition['status'] = array('NEQ',3);
        $field = 'sc_id,sc_name,sort';
        $list = \app\api\model\SalesChannels::getDataList($condition,$field);

        if(!empty($list)){
            return $this->returnmsg(200,'获取数据成功',$list);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }

    /**
     * 获取某条数据
     */
    public function getSalesChannelsInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'sc_id' => 'require|integer'
        ];

        $msg = [
            'sc_id.require' => '销售渠道ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $data = \app\api\model\SalesChannels::getDataInfo($array);
        if(!empty($data)){
            return $this->returnmsg(200,'获取数据成功',$data);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }

    /**
     * 修改某条数据
     */
    public function editSalesChannelsInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'sc_id' => 'require|integer'
        ];

        $msg = [
            'sc_id.require' => '销售渠道ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\SalesChannels::editDataInfo($array);

        if($result){
            return $this->returnmsg(200,'修改数据成功');
        }else{
            return $this->returnmsg(400,'修改数据失败');
        }
    }

    /**
     * 删除数据
     */
    public function deleteSalesChannelsInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'sc_id' => 'require|integer'
        ];

        $msg = [
            'sc_id.require' => '销售渠道ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $sc_id = $array['sc_id'];
        $productAttributeData = \app\api\model\Product::getProductAttrInfo("FIND_IN_SET($sc_id,sales_channels_id)");
        if(empty($productAttributeData)){
            //操作者ID
            $array['admin_id'] = $this->userInfo['admin_id'];

            $result = \app\api\model\SalesChannels::deleteDataInfo($array);
            if($result){
                return $this->returnmsg(200,'删除数据成功');
            }else{
                return $this->returnmsg(400,'删除数据失败');
            }
        }else{
            return $this->returnmsg(403,'其类别下有数据，不可删除');
        }
    }
}