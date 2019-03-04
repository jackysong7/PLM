<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/9
 * Time: 10:06
 * 商品模型属性
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Validate;

class Attribute extends Api
{
    //需要权限验证的接口
    protected $needRight = array('getAttributeGroup','addAttributeGroup','updateAttributeGroup','deleteAttributeGroup');

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
            if(isset($params["attr_id"])) {
                $obj = \app\api\model\Attribute::checkList(array("status"=>1,"attr_id"=>$params["attr_id"]));
                if ($params["attr_name"] == $obj["attr_name"]){
                    return;
                }
                $params["parent_id"] = $obj["parent_id"];
                $params["pm_id"] = $obj["pm_id"];
            }
            $obj = \app\api\model\Attribute::checkList(array("status"=>1,"parent_id"=>$params["parent_id"],"pm_id"=>$params["pm_id"],"attr_name"=>$params["attr_name"]));
            if($obj)
            {
                return $this->returnmsg(402,'模型属性名称违背了唯一性原则！');
            }
        }
    }
    /**
     * 新增模型属性
     */
    public function addAttributeGroup()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'attr_name' => 'require',
            'pm_id' => 'require|integer',
            'parent_id' => 'require|integer'
        ];

        $msg = [
            'attr_name.require' => '属性名称不能为空',
            'pm_id.require' => '产品模型不能为空且必须是数字',
            'parent_id.require' => '上级ID不能为空且必须是数字'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Attribute::addAttributeGroup($array);
        if($result){
            $info = $result->toArray();
            $attr_id['attr_id'] = $info['attr_id'];
            return $this->returnmsg(200,'新增数据成功',$attr_id);
        }else{
            return $this->returnmsg(400,'新增数据失败');
        }
    }

    /**
     * 获取商品模型属性列表
     */
    public function getAttributeGroup()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'pm_id' => 'require|integer'
        ];

        $msg = [
            'pm_id.require' => '产品模型属性必须是数字'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $condition['pm_id'] = $array['pm_id'];
        $condition['parent_id'] = 0;
        $condition['status'] = 1;
        $field = 'attr_id,attr_name';
        $list = \app\api\model\Attribute::getAttributeGroupList($condition,$field);
        if(!empty($list)){
            foreach($list as $key => $val){
                $row = $val->toArray();
                $condition['parent_id'] = $row['attr_id'];
                $child = \app\api\model\Attribute::getAttributeGroupList($condition,$field);
                $list[$key]['list'] = $child;
            }
            return $this->returnmsg(200,'获取数据成功',$list);
        }else{
            return $this->returnmsg(200,'暂无数据',$list);
        }
    }

    /**
     * 修改商品模型属性信息
     */
    public function updateAttributeGroup()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'attr_id' => 'require|integer',
            'attr_name' => 'require'
        ];

        $msg = [
            'attr_id.require' => '产品模型属性不能为空且必须是数字',
            'attr_name.require' => '产品模型名称不能为空'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Attribute::editAttributeGroupInfo($array);

        if($result){
            return $this->returnmsg(200,'修改数据成功');
        }else{
            return $this->returnmsg(400,'修改数据失败');
        }
    }

    /**
     * 删除商品模型属性信息
     */
    public function deleteAttributeGroup()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'attr_id' => 'require|integer'
        ];

        $msg = [
            'attr_id.require' => '产品模型属性不能为空且必须是数字'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $attributeWhere['parent_id'] = $array['attr_id'];
        $attributeWhere['status'] = 1;
        //验证其模型属性下有数据
        $attributeInfo = \app\api\model\Attribute::getAttributeGroupInfo($attributeWhere);
        if(!empty($attributeInfo)){
            return $this->returnmsg(403,'其模型属性下有数据，不可删除');
        }else{
            $attributeValueWhere['attr_id'] = $array['attr_id'];
            $attributeValueWhere['status'] = 1;
            $attributeValue = \app\api\model\AttributeValue::getAttributeValueInfo($attributeValueWhere);
            if(!empty($attributeValue)){
                return $this->returnmsg(403,'其模型属性下有数据，不可删除');
            }else{

                //操作者ID
                $array['admin_id'] = $this->userInfo['admin_id'];

                $result = \app\api\model\Attribute::deleteAttributeGroupInfo($array);
                if($result){
                    return $this->returnmsg(200,'删除成功');
                }else{
                    return $this->returnmsg(400,'删除失败');
                }
            }
        }
    }
}