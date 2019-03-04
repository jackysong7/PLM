<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/3
 * Time: 11:04
 * 品牌管理
 */
namespace app\api\controller\v1;
use app\api\controller\Api;
use think\Validate;
class Brand extends Api
{

    //需要权限验证的接口
    protected $needRight = array('getBrandList','addBrand','updateBrand','deleteBrand');

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
            if(isset($params["brand_id"])) {
                $obj = \app\api\model\Brand::checkList(array("status"=>1,"brand_id"=>$params["brand_id"]));
                if ($params["brand_name"] == $obj["brand_name"]){
                    return;
                }
            }

            $obj = \app\api\model\Brand::checkList(array("status"=>1,"brand_name"=>$params["brand_name"]));
            if($obj)
            {
                return $this->returnmsg(402,'品牌名称违背了唯一性原则！');
            }
        }
    }
    /**
     * 添加品牌信息
     */
    public function addBrand()
    {
        $params = $this->request->param();
        $array = json_decode($params['data'],true);

        //验证数据
        $rule = [
            'brand_name' => 'require'
        ];

        $msg = [
            'brand_name.require' => '品牌名称不能为空'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Brand::insertInfo($array);

        if($result){
            $info = $result->toArray();
            $brand_id['brand_id'] = $info['brand_id'];
            return $this->returnmsg(200,'新增操作成功',$brand_id);
        }else{
            return $this->returnmsg(400,'新增操作失败');
        }
    }

    /**
     * 获取品牌信息列表
     */
    public function getBrandList()
    {
        $brandWhere['status'] = array('NEQ',3);
        $brandField = 'brand_id,brand_name,brand_pic,sort';

        $list = \app\api\model\Brand::getDataList($brandWhere,$brandField);

        if(!empty($list)) {
            return $this->returnmsg(200,'获取数据成功',$list);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }

    /**
     * 获取单条数据
     */
    public function getBrandInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'brand_id' => 'require|integer'
        ];

        $msg = [
            'brand_id.require' => '品牌ID不能为空且必须是数字'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $objectInfo = \app\api\model\Brand::getDataInfo($array);
        if(!empty($objectInfo)){
            $info = $objectInfo->toArray();
            return $this->returnmsg(200,'获取数据成功',$info);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }

    /**
     * 修改某条数据
     */
    public function updateBrand(){
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'brand_id' => 'require|integer'
        ];

        $msg = [
            'brand_id.require' => '品牌ID不能为空且必须是数字'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $objectEdit = \app\api\model\Brand::editDataInfo($array);
        if(!empty($objectEdit)){
            return $this->returnmsg(200,'修改数据成功');
        }else{
            return $this->returnmsg(400,'修改数据失败');
        }
    }

    /**
     * 删除某条数据
     */
    public function deleteBrand(){
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'brand_id' => 'require|integer'
        ];

        $msg = [
            'brand_id.require' => '品牌ID不能为空且必须是数字'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        //验证当前品牌下有无数据
        $productWhere['brand_id'] = $array['brand_id'];
        $result = \app\api\model\Product::getProductAttrInfo($productWhere);

        if(!empty($result)){
            return $this->returnmsg(403,'其品牌下有数据，不可删除');
        }else{

            //操作者ID
            $array['admin_id'] = $this->userInfo['admin_id'];

            $objectDel = \app\api\model\Brand::deleteDataInfo($array);
            if(!empty($objectDel)){
                return $this->returnmsg(200,'删除数据成功');
            }else{
                return $this->returnmsg(400,'删除数据失败');
            }

        }
    }
}