<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/3
 * Time: 13:49
 * 分类管理
 */
namespace app\api\controller\v1;
use app\api\controller\Api;
use think\Validate;

class Category extends Api
{

    //需要权限验证的接口
    protected $needRight = array('getCategoryList','addCategory','updateCategory','deleteCategory');

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
            if(isset($params["gc_id"])) {
                $obj = \app\api\model\Category::checkList(array("status"=>1,"gc_id"=>$params["gc_id"]));
                if ($params["gc_name"] == $obj["gc_name"]){
                    return;
                }
                $params["parent_id"] = $obj["parent_id"];
            }
            $obj = \app\api\model\Category::checkList(array("status"=>1,"gc_name"=>$params["gc_name"],"parent_id"=>$params["parent_id"]));
            if($obj)
            {
                return $this->returnmsg(402,'分类名称违背了唯一性原则！');
            }
        }
    }

    /**
     * 新增分类
     */
    public function addCategory()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'gc_name' => 'require',
            'parent_id' => 'require|integer'
        ];

        $msg = [
            'gc_name.require' => '分类名称不能为空',
            'parent_id.require' => '上级ID不能为空且必须是数字'
        ];

        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);

        if($array['parent_id'] > 0){
            $gc_level = $this->getGoodsCategoryLevel($array['parent_id']);
            $array['gc_level'] = ($gc_level['gc_level'] + 1);
        }else{
            $array['gc_level'] = 1;
        }

        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Category::insertInfo($array);

        if($result){
            $info = $result->toArray();
            $gc_id['gc_id'] = $info['gc_id'];
            return $this->returnmsg(200,'新增操作成功',$gc_id);
        }else{
            return $this->returnmsg(400,'新增操作失败');
        }
    }

    /**
     * 获取数据列表
     */
    public function getCategoryList()
    {
        $this->categoryList();
    }

    public function changeCategoryList()
    {
        $this->categoryList();
    }

    public function categoryList(){
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'parent_id' => 'require|integer'
        ];

        $msg = [
            'parent_id.require' => '上级ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $condition['parent_id'] = $array['parent_id'];
        $condition['status'] = 1;
        $field = 'gc_id,gc_name,sort';

        $list = \app\api\model\Category::getDataList($condition,$field);
        if(!empty($list)){
            return $this->returnmsg(200,'获取数据成功',$list);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }
    /**
     * 获取某条信息
     */
    public function getCategoryInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'gc_id' => 'require|integer'
        ];

        $msg = [
            'gc_id.require' => '分类ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $info = \app\api\model\Category::getDataInfo($array);
        if(!empty($info)){
            return $this->returnmsg(200,'获取数据成功',$info);
        }else{
            return $this->returnmsg(200,'暂无数据');
        }
    }

    /**
     * 修改某条数据
     */
    public function updateCategory()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'gc_id' => 'require|integer'
        ];

        $msg = [
            'gc_id.require' => '分类ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }
        $this->check($array);
        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Category::editDataInfo($array);
        if($result){
            return $this->returnmsg(200,'修改数据成功');
        }else{
            return $this->returnmsg(400,'修改数据失败');
        }
    }

    /**
     * 删除某条信息
     */
    public function deleteCategory()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'gc_id' => 'require|integer'
        ];

        $msg = [
            'gc_id.require' => '分类ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        //验证当前分类下是否有数据
        $info = $this->getGoodsCategoryLevel($array['gc_id']);

        switch ($info['gc_level']){
            case 1:
                $where['category_one_id'] = $array['gc_id'];
                break;
            case 2:
                $where['category_two_id'] = $array['gc_id'];
                break;
            case 3:
                $where['category_three_id'] = $array['gc_id'];
                break;
        }

        $productAttributeData = \app\api\model\Product::getProductAttrInfo($where);

        if($info['gc_level'] == 1 || $info['gc_level'] == 2){
            $data = \app\api\model\Category::getGoodsCategoryInfo(array('parent_id' => $array['gc_id'],"status"=>1));

            if(!empty($data) || !empty($productAttributeData)){
                return $this->returnmsg(403,'其类别下有数据，不可删除');
            }else{
                $result = \app\api\model\Category::deleteDataInfo($array);
                if($result){
                    return $this->returnmsg(200,'删除数据成功');
                }else{
                    return $this->returnmsg(400,'删除数据失败');
                }
            }
        }elseif($info['gc_level'] == 3){
//            $where['category_three_id'] = $array['gc_id'];
//            $productAttributeData = \app\api\model\Product::getProductAttrInfo($where);
            if(empty($productAttributeData)){

                //操作者ID
                $array['admin_id'] = $this->userInfo['admin_id'];

                $result = \app\api\model\Category::deleteDataInfo($array);
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

    /**
     * 获取当前分类的ID及层级
     */
    public function getGoodsCategoryLevel($where)
    {
        $condition['gc_id'] = $where;
        $condition['status'] = 1;
        $field = 'gc_id,gc_level';
        return $info = \app\api\model\Category::getGoodsCategoryInfo($condition,$field);
    }



}