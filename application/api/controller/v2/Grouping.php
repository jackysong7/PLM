<?php
/**
 * Bom/ERP物料
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/7/12
 * Time: 17:00
 */
namespace app\api\controller\v2;

use app\api\controller\Api;
use think\Validate;

class Grouping extends Api
{
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }
    
    //获取分组列表
    public function getList()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        
        $condition['type'] = $data['type'];
        $condition['status'] = 1;
        $field = 'mg_id,mg_name,mg_code,parent_id';
        
        $list = \app\api\model\Grouping::getList($condition,$field);

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
                $childs = self::getTree($data, $v['mg_id']);
                if(!empty($childs)){
                    $v['list'] = $childs;
                }
                unset($v['parent_id']);
                $tree[] = $v;
            }
        }
        return $tree;
    }
    
    //启用和禁用BOM/物料
    public function change()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'id'  => 'require|integer',
            'type'  => 'require|integer',

        ];
        $msg = [
            'id.require' => 'ID不能为空！',
            'type.require' => '类型不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);

        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        $row = \app\api\model\Grouping::change($data);
        if($row){
            return $this->returnmsg(200,'success！');
        }else{
            return $this->returnmsg(400,'error！');
        }
    }
    
    //根据分组获取BOM内容
    
    public function getBom()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        $data['page_no'] = empty($data['page_no']) ? 1 : $data['page_no'];
        $data['page_size'] = empty($data['page_size']) ? 10 : $data['page_size'];

        if($data['source'] == 2)//必填，数据来源：1为所有; 2为标准资源库;年度（选择标准资源库时，为必填项）
        {
            $rule = [
                'year'  => 'require|integer'

            ];
            $msg = [
                'year.require' => '年度不能为空！！'
            ];
            $this->validate = new Validate($rule, $msg);
            $result = $this->validate->check($data);

            if (!$result)
            {
                return $this->returnmsg(401,$this->validate->getError(),"");
            }
        }

        $row = \app\api\model\Grouping::getBom($data);
        return $this->returnmsg(200,'success！',$row);
    }

    //根据分组获取物料内容
    public function getMaterial()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        $data['page_no'] = empty($data['page_no']) ? 1 : $data['page_no'];
        $data['page_size'] = empty($data['page_size']) ? 10 : $data['page_size'];

        if($data['source'] == 2)//必填，数据来源：1为所有; 2为标准资源库;年度（选择标准资源库时，为必填项）
        {
            $rule = [
                'year'  => 'require|integer'

            ];
            $msg = [
                'year.require' => '年度不能为空！！'
            ];
            $this->validate = new Validate($rule, $msg);
            $result = $this->validate->check($data);

            if (!$result)
            {
                return $this->returnmsg(401,$this->validate->getError(),"");
            }
        }

        $row = \app\api\model\Grouping::getMaterial($data);
        return $this->returnmsg(200,'success！',$row);
    }
}
