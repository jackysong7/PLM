<?php
/**
 * 文档模板
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/7/6
 * Time: 17:00
 */
namespace app\api\controller\v2;

use app\api\controller\Api;
use think\Controller;
use think\Validate;
class Template extends Api
{
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }
    
    //新增文档模板
    public function add()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        $row = \app\api\model\Template::add($data);

        return $this->returnmsg(200,'success！',$row);
    }
    
    //文档模板列表
    public function getList()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        
        $data['page_no'] = empty($data['page_no']) ? 1 : $data['page_no'];
        $data['page_size'] = empty($data['page_size']) ? 10 : $data['page_size'];

        $result = \app\api\model\Template::getList($data);
        return $this->returnmsg(200,'success！',$result);
    }
    
    //修改文档模板
    public function update()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        
        $result = \app\api\model\Template::editDocTemplate($data);
        if($result)
        {
            return $this->returnmsg(200,'success！');
        }else{
            return $this->returnmsg(400,'修改失败！');
        }
    }
    
    //删除文档模板
    
    public function delete()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'tpl_id'  => 'require|integer',
        ];
        $msg = [
            'tpl_id.require' => '文档模板id必须填写！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);

        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        
        //验证项目节点表有无数据
        $project_node_info = \app\api\model\Template::getProjectNode($data['tpl_id']);
        if(!empty($project_node_info)){
            return $this->returnmsg(403,'其类别下有数据，不可删除！');
        }
        //验证工程变更表有无数据
        $Ecnwhere['change_doc'] = $data['tpl_id'];
        $project_ecn_info = \app\api\model\Template::getProjectEcn($Ecnwhere);
        if(!empty($project_ecn_info)){
            return $this->returnmsg(403,'其类别下有数据，不可删除！');
        }
        //验证自定义表格表有无数据
        $Viuewhere['tpl_id'] = $data['tpl_id'];
        $uitableview_info = \app\api\model\Template::getUitableview($Viuewhere);
        if(!empty($uitableview_info)){
            return $this->returnmsg(403,'其类别下有数据，不可删除！');
        }
        
        $objectDel = \app\api\model\Template::deleteProjectNode($Viuewhere);
        if($objectDel){
            return $this->returnmsg(200,'删除数据成功');
        }else{
            return $this->returnmsg(400,'删除数据失败');
        }
    }
}

