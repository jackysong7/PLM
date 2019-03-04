<?php
namespace app\api\controller\v1;

use think\Controller;

use app\api\controller\Api;
use app\api\controller\Send;

/* 
 * 竞品信息
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Competing extends Api
{
    protected $needRight = ["getCompetingList","addCompeting","editCompeting","delCompeting"];
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }
    //获取竞品信息列表
    public function getCompetingList()
    {
        $arr = [];
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        if(empty($data['plm_no'])){
            $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        $competinglist = \app\api\model\Competing::getCompetingList($data);

        return $this->returnmsg(200,'success',empty($competinglist) ?  $arr : $competinglist);

    }
    //新增竞品信息
    public function addCompeting()       
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        if(empty($data['competing_name'])){
            return $this->returnmsg(401,'竞品名称不能为空！');
        }
        if(empty($data['competing_path'])){
            return $this->returnmsg(401,'竞品链接不能为空！');
        }
        try {
            $addCompeting = \app\api\model\Competing::addCompeting($data,$this->userInfo['admin_id']);
            return $this->returnmsg(200,'success!',$addCompeting);
        } catch (\Exception $e) {
            return $this->returnmsg(400, '写入数据失败!!');
	    }
    }
    //删除竞品记录
    public function delCompeting()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $data['admin_id'] = $this->userInfo['admin_id'];
        if(empty($data['cg_id'])){
            return $this->returnmsg(401,'竟品ID不能为空！！');
        }
        $delCompeting = \app\api\model\Competing::delCompeting($data);
        if($delCompeting){
            return $this->returnmsg(200,'success!',$delCompeting);
        }else{
            return $this->returnmsg(400, '删除操作失败！');
        }
    }
    //修改竞品信息
    public function editCompeting()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $data['admin_id'] = $this->userInfo['admin_id'];
        $editCompeting = \app\api\model\Competing::editCompeting($data);

        if($editCompeting == 1){
            return $this->returnmsg(200,'success!');
        }else{
            return $this->returnmsg(400, '数据查询不存在！');
        }
    }
}