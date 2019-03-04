<?php
/**
 * 产品属性模型
 * Created by PhpStorm.
 * User: liu
 * Date: 2018/4/8
 * Time: 13:57
 */
namespace app\api\model;

use think\Db;
use think\Model;
class Product extends Model
{
    /**
     * //获取产品属性基础信息(非修改状态下)
     * @param $params
     * @return array|bool
     */
    public static function getProductAttribute($params)
    {
        //查询产品的基础信息
        $product_attribute = Db::name('product_attribute')->where('plm_no',$params['plm_no'])->find();
        //查询产品的品牌信息
        if(!empty($product_attribute['brand_id'])) {
            $brand_name = Db::name('brand')->field('brand_name')->where('brand_id', $product_attribute['brand_id'])->value('brand_name');
        }else{
            $brand_name = '';
        }

        //查询产品的模型名称
        if(!empty($product_attribute['model_id'])) {


            $pm_name = Db::name('product_model')->field('pm_name')->where('pm_id', $product_attribute['model_id'])->value('pm_name');
        }else{
            $pm_name = '';
        }

        //查询产品的销售渠道
        if(!empty($product_attribute['sales_channels_id'])) {

            $where['sc_id'] = array("in",$product_attribute['sales_channels_id']);

            $sc_name_array = Db::name('sales_channels')->field('sc_name')->where($where)->select();

            $sc_name = array_column($sc_name_array, 'sc_name');
        }else{
            $sc_name = '';
        }
        //查训产品的销售状态
        if(!empty($product_attribute['sales_status_id'])) {
            $ss_name = Db::name('sales_status')->field('ss_name')->where('ss_id', $product_attribute['sales_status_id'])->value('ss_name');
        }else{
            $ss_name = '';
        }

        //查询产品的分类名称1级 2级 3级
        if(!empty($product_attribute['category_one_id'])) {
            $gc_name1 = Db::name('goods_category')->field('gc_name')->where('gc_id', $product_attribute['category_one_id'])->value('gc_name');
        }else{
            $gc_name1 = '';
        }

        if(!empty($product_attribute['category_two_id'])) {
            $gc_name2 = Db::name('goods_category')->field('gc_name')->where('gc_id', $product_attribute['category_two_id'])->value('gc_name');
        }else{
            $gc_name2 = '';
        }

        if(!empty($product_attribute['category_three_id'])) {
            $gc_name3 = Db::name('goods_category')->field('gc_name')->where('gc_id', $product_attribute['category_three_id'])->value('gc_name');
        }else{
            $gc_name3 = '';
        }

        $productAttributesData = array(
            "brand_name"=>$brand_name,
            "goods_name"=>empty($product_attribute['goods_name'])?"":$product_attribute['goods_name'],
            "model_name"=>$pm_name,
            "category_one"=>$gc_name1,
            "category_two"=>$gc_name2,
            "category_three"=>$gc_name3,
            "sales_channels_name"=>$sc_name,
            "sales_status_name"=>$ss_name
        );

        //查询属于当前模型的规格

        $attr_where['status'] = 1;
        $attr_where['parent_id'] = 0;
        $newData = [];
        if(!empty($product_attribute['model_id'])){

            $attr_where['pm_id'] = $product_attribute['model_id'];
            $attrData = Db::name('product_model_attribute_group')->field('attr_id,pm_id,attr_name')->where($attr_where)->select();

            foreach ($attrData as $k => $v) {
                $newData[$k]['attr_name'] = $v['attr_name'];
                $newData[$k]['list'] = Db::name('product_model_attribute_group')->field('attr_id,attr_name')->where('status = 1 AND parent_id="' . $v['attr_id'] . '"')->select();

                foreach ($newData[$k]['list'] as $key => $value) {
                    $newData[$k]['list'][$key]['attr_value_name'] = Db::name('product_model_attribute_value')->field('attr_value_name')->where('status = 1 AND attr_id="' . $value['attr_id'] . '"')->value('attr_value_name');
                }
            }
        }
        return array("productAttributes"=>$productAttributesData,"attributeValue"=>$newData);
    }

    /**
     * 修改商品模型属性值信息
     * @param $params
     * @return int|string
     */
    public static function updateAttributeValue($params)
    {
        $row = Db::name('basedata')->where('plm_no', $params['plm_no'])->find();

        if($row) {
            $where['attr_id'] = $params['attr_id'];//属性值的ID
            $where['plm_no'] = $params['plm_no'];

            $result = Db::name('product_model_attribute_value')->where($where)->find();
            if ($result) {
                if (empty($params['attr_value_name'])) {
                    $data = array("status" => 3,"updatetime"=>time());
                    return Db::name('product_model_attribute_value')->where($where)->update($data);
                } else {
                    $data = array("status" => 1,"attr_value_name" => $params['attr_value_name']);
                    return Db::name('product_model_attribute_value')->where($where)->update($data);
                }
            } else {
                $array_value = array(
                    "plm_no"=>$params['plm_no'],
                    "attr_id"=>$params['attr_id'],
                    "attr_value_name"=>$params['attr_value_name'],
                    "status"=>1,
                    "createtime"=>time(),
                    "admin_id"=>$params['admin_id']
                );
                return Db::name('product_model_attribute_value')->insert($array_value);
            }
        }else{
            return -1;
        }
    }

    /**
     * 获取某条信息
     */
    public static function getProductAttrInfo($condition,$field = '*')
    {
        return Db::table('plm_product_attribute')->where($condition)->field($field)->find();
    }

    /**
     * 修改产品属性信息
     * @param $params
     */
    public static function updateProductAttribute($params)
    {
        if($params['type'] == 'brand'){
            $data = array("brand_id"=>$params['update_value']);
        }
        if($params['type'] == 'model'){
            $data = array("model_id"=>$params['update_value']);
        }
        if($params['type'] == 'goods'){
            $data = array("goods_name"=>$params['update_value']);
        }
        if($params['type'] == 'category_one'){
            $data = array("category_one_id"=>$params['update_value']);
        }
        if($params['type'] == 'category_two'){
            $data = array("category_two_id"=>$params['update_value']);
        }
        if($params['type'] == 'category_three'){
            $data = array("category_three_id"=>$params['update_value']);
        }
        if($params['type'] == 'sales_channels'){
            $data = array("sales_channels_id"=>$params['update_value']);
        }
        if($params['type'] == 'sales_status'){
            $data = array("sales_status_id"=>$params['update_value']);
        }
        $where['plm_no'] = $params['plm_no'];
        $result = Db::name('product_attribute')->where($where)->select();

        if(!empty($result)){
            return Db::name('product_attribute')->where($where)->update($data);
        }else{
            $array = array(
                "plm_no" => $params['plm_no'],
                "admin_id" => $params['admin_id'],
                "createtime"=> time()
            );
            $data = array_merge($array,$data);
            return Db::name('product_attribute')->insert($data);
        }
    }

    /**
     * 切换模型，根据模型ID获取商品模型属性以及属性值的信息
     * @param $params
     * @return mixed
     */
    public static function getAttributeValue($params)
    {
        $attr_where['status'] = 1;
        $attr_where['parent_id'] = 0;
        $attr_where['pm_id'] = $params['pm_id'];
        $attrData = Db::name('product_model_attribute_group')->field('attr_id,pm_id,attr_name')->where($attr_where)->select();

        if($attrData) {
            foreach ($attrData as $k => $v) {
                $newData[$k]['attr_id'] = $v['attr_id'];
                $newData[$k]['attr_name'] = $v['attr_name'];
                $newData[$k]['list'] = Db::name('product_model_attribute_group')->field('attr_id,attr_name')->where('status = 1 AND parent_id="' . $v['attr_id'] . '"')->select();

                foreach ($newData[$k]['list'] as $key => $value) {
                    $newData[$k]['list'][$key]['attr_value_name'] = Db::name('product_model_attribute_value')->field('attr_value_name')->where('status = 1 AND attr_id="' . $value['attr_id'] . '" AND plm_no = "'.$params['plm_no'].'"')->value('attr_value_name');
                }
            }
            return $newData;
        }else{
            return [];
        }

    }

    /**
     * @return string
     */
    public static function editSalesChannels($params)
    {
         $resultData = Db::name('sales_channels')->field('sc_id,sc_name')->where("status",1)->select();

         foreach ($resultData as $key=>$v)
         {
             if(in_array($v['sc_id'],explode(',',Db::name('product_attribute')->field('sales_channels_id')->where("plm_no",$params['plm_no'])->value('sales_channels_id'))))
             {
                 $resultData[$key]["checked"] = true;
             }else{
                 $resultData[$key]["checked"] = false;
             }
         }
         return $resultData;
    }

    public static function editSalesStatus($params)
    {
        $resultData = Db::name('sales_status')->field('ss_id,ss_name')->where("status",1)->select();

        foreach ($resultData as $key=>$v)
        {
            if($v['ss_id'] == Db::name('product_attribute')->field('sales_status_id')->where("plm_no",$params['plm_no'])->value('sales_status_id'))
            {
                $resultData[$key]["checked"] = true;
            }else{
                $resultData[$key]["checked"] = false;
            }
        }
        return $resultData;
    }

    public static function editProductAttribute($params)
    {
        $product_attribute = Db::name('product_attribute')->where('plm_no',$params['plm_no'])->find();//PLM产品属性信息
        //品牌的对比
        $brandData = Db::name('brand')->field('brand_id,brand_name')->where("status",1)->select();
        foreach ($brandData as $key=>$v)
        {
            if($v['brand_id'] == $product_attribute['brand_id'])
            {
                $brandData[$key]['checked'] = true;
            }else{
                $brandData[$key]['checked'] = false;
            }
        }
        //查询产品名称
        $goods_name = Db::name('product_attribute')->field('goods_name')->where("plm_no",$params['plm_no'])->value('goods_name');

        //模型的对比

        $modelData = Db::name('product_model')->field('pm_id,pm_name')->where("status",1)->select();
        foreach ($modelData as $key=>$v)
        {
            if($v['pm_id'] == $product_attribute['model_id'])
            {
                $modelData[$key]['checked'] = true;
            }else{
                $modelData[$key]['checked'] = false;
            }
        }
        $twoCategory = $threeCategory = $attributeValue = []; //初始化
        //一级分类对比
        $onewhere['status'] = 1;
        $onewhere['parent_id'] = 0;
        $oneCategory = Db::name('goods_category')->field('gc_id,gc_name')->where($onewhere)->select();
        foreach ($oneCategory as $key=>$v)
        {
            if($v['gc_id'] == $product_attribute['category_one_id'])
            {
                $oneCategory[$key]['checked'] = true;
            }else{
                $oneCategory[$key]['checked'] = false;
            }
        }


        //二级分类对比
        if($product_attribute['category_one_id']) {
            $twowhere['status'] = 1;
            $twowhere['parent_id'] = $product_attribute['category_one_id'];
            $twoCategory = Db::name('goods_category')->field('gc_id,gc_name')->where($twowhere)->select();
            foreach ($twoCategory as $key => $v) {
                if ($v['gc_id'] == $product_attribute['category_two_id']) {
                    $twoCategory[$key]['checked'] = true;
                } else {
                    $twoCategory[$key]['checked'] = false;
                }
            }
        }
        //三级分类对比
        if($product_attribute['category_two_id']) {
            $threewhere['status'] = 1;
            $threewhere['parent_id'] = $product_attribute['category_two_id'];
            $threeCategory = Db::name('goods_category')->field('gc_id,gc_name')->where($threewhere)->select();
            foreach ($threeCategory as $key => $v) {
                if ($v['gc_id'] == $product_attribute['category_three_id']) {
                    $threeCategory[$key]['checked'] = true;
                } else {
                    $threeCategory[$key]['checked'] = false;
                }
            }
        }

        //查询当前plm_no 对应的模型信息

        if($product_attribute['model_id']){
            $data = array("pm_id"=>$product_attribute['model_id'],"plm_no"=>$params['plm_no']);
            $attributeValue = self::getAttributeValue($data);
        }
        $resultBrand = array("brand_list"=>$brandData,"goods_name"=>$goods_name,"model_list"=>$modelData,"category_one_list"=>$oneCategory,"category_two_list"=>$twoCategory,"category_three_list"=>$threeCategory);

        return array("productAttributes"=>$resultBrand,"attributeValue"=>$attributeValue);
    }
}