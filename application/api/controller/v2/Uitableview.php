<?php

/**
 * 文档属性
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/7/9
 * Time: 17:00
 */
namespace app\api\controller\v2;

use app\api\controller\Api;
use think\Controller;
use think\Validate;
class Uitableview extends Api
{
     public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }
    
     //新增自定义文档属性
    public function add()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'attr_name'  => 'require',
            'tpl_id'  => 'require|integer',
        ];
        $msg = [
            'attr_name.require' => '属性名称不能为空！',
            'tpl_id.require' => '文档模版表自增ID不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);

        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        $data['admin_id'] = $this->userInfo['admin_id'];
        $row = \app\api\model\Uitableview::add($data);

        return $this->returnmsg(200,'success！',$row);
    }
    
    //修改自定义文档属性
    public function update()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'attr_name'  => 'require',
            'view_id'  => 'require|integer',
        ];
        $msg = [
            'attr_name.require' => '属性名称不能为空！',
            'view_id.require' => '属性ID不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);

        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        $data['admin_id'] = $this->userInfo['admin_id'];
        $row = \app\api\model\Uitableview::editUitableview($data);

        if($row){
            return $this->returnmsg(200,'success！');
        }else{
            return $this->returnmsg(400,'修改失败！');
        }
    }
    
    //删除自定义文档属性信息
    
    public function delete()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'view_id'  => 'require|integer',
        ];
        $msg = [
            'view_id.require' => '属性ID不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);

        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        //验证项目节点表有无数据
        $uitableviewWhere['parent_id'] = $data['view_id'];
        $uitableview_info = \app\api\model\Uitableview::getUitableview($uitableviewWhere);

        if(!empty($uitableview_info)){
            return $this->returnmsg(403,'其类别下有数据，不可删除！');
        }else{
            $valueWhere['view_id'] = $data['view_id'];
            $valueWhere['status'] = 1;
            $uitableview_value_info = \app\api\model\Uitableview::getUitableviewValue($valueWhere);
            if(!empty($uitableview_value_info)){
                return $this->returnmsg(403,'其类别下有值，不可删除！');
            }
        } 
        
        $where['view_id'] = $data['view_id'];
        
        $objectDel = \app\api\model\Uitableview::deleteUitableview($where);
        if($objectDel){
            return $this->returnmsg(200,'删除数据成功');
        }else{
            return $this->returnmsg(400,'删除数据失败');
        }
    }
    
    //获取自定义文档属性信息
    public function getInfo()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'tpl_id' => 'require|integer'
        ];

        $msg = [
            'tpl_id.require' => '文档模版自增ID不能为空且必须是数字！'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $condition['tpl_id'] = $array['tpl_id'];
        $condition['status'] = 1;
        $field = 'view_id,attr_name,type,parent_id,sub_type';
        $list = \app\api\model\Uitableview::getValueList($condition,$field);
        if(!empty($list)){
            $tree = $this->getTree($list, 0);
            return $this->returnmsg(200,'获取成功！',$tree);
        }else{
            return $this->returnmsg(200,'获取成功！',[]);
        }

    }
    /**
     * 递归实现无限级文件夹树
     */
    private function getTree($data, $pId)
    {
        $tree = '';
        foreach($data as $k => $v){
            if($v['parent_id'] == $pId) {
                $childs = self::getTree($data, $v['view_id']);
                if(!empty($childs)){
                    $v['list'] = $childs;
                }else{
                    $v['list'] = [];
                }
                unset($v['parent_id']);
                $tree[] = $v;
            }
        }
        return $tree;
    }
}
