<?php
/**
 * 产品属性
 *
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/4
 * Time: 17:34
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use app\api\controller\Send;

class Product extends Api
{
    protected $needRight = ["editProductAttribute","getProductAttribute","editSalesChannels","editSalesStatus"];
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }
    //获取产品属性基础信息(非修改状态下)
    public function getProductAttribute()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        $result = \app\api\model\Product::getProductAttribute($data);

        //if($result == false){
            //return $this->returnmsg(402,'参数错误');
        //}else{
            return $this->returnmsg(200,'success!',$result);
        //}
    }

    /**
     * 修改商品模型属性值信息
     */
    public function updateAttributeValue()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        if(empty($data['attr_id'])){
            return $this->returnmsg(401,'属性ID不能为空！');
        }
        $data['admin_id'] = $this->userInfo['admin_id'];
        $result = \app\api\model\Product::updateAttributeValue($data);
        if($result == -1) {
            return $this->returnmsg(402, '数据不合法');
        }else{
            return $this->returnmsg(200, 'success!', $result);
        }
    }

    /**
     * 修改产品属性信息
     */
    public function updateProductAttribute()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        if(empty($data['type'])){
            return $this->returnmsg(401,'修改类型不能为空！');
        }
        $array = array("brand","model","goods","category_one","category_two","category_three","sales_channels","sales_status");

        $data['admin_id'] = $this->userInfo['admin_id'];

        if(in_array($data['type'],$array)){
            $result = \app\api\model\Product::updateProductAttribute($data);
            return $this->returnmsg(200,'success!',$result);
        }else{
            return $this->returnmsg(401,'修改类型错误！');
        }
    }

    /**
     * 切换模型，根据模型ID获取商品模型属性以及属性值的信息
     */
    public function getAttributeValue()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        if(empty($data['pm_id'])){
            return $this->returnmsg(401,'产品模型表自增ID不能为空！');
        }
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码ID不能为空！');
        }
        $result = \app\api\model\Product::getAttributeValue($data);
        return $this->returnmsg(200,'success!',$result);
    }

    /**
     * 获取销售渠道信息(修改状态)
     */
    public function editSalesChannels()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        $result = \app\api\model\Product::editSalesChannels($data);

        return $this->returnmsg(200,'success!',$result);
    }

    /**
     * 获取销售状态信息(修改状态)
     */
    public function editSalesStatus()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        $result = \app\api\model\Product::editSalesStatus($data);
        return $this->returnmsg(200,'success!',$result);
    }

    /**
     * 获取产品属性信息(修改状态)
     */
    public function editProductAttribute()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        $result = \app\api\model\Product::editProductAttribute($data);
        //print_r($result);
        $this->returnmsg(200,'success!',$result);
    }
}