<?php
/**
 * PLM基础属性文档列表信息
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/4
 * Time: 13:45
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Validate;
class Document extends Api
{
    protected $needRight = ["getDirDocList","getDocList","uploadDoc","deleteDoc","viewDoc"];
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }
    //PLM基础属性文档列表信息
    public function getDirDocList()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        if(empty($data['plm_no'])){
            $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        $data['admin_id'] = $this->userInfo['admin_id'];
        $data['username'] = $this->userInfo['username'];

        $result = \app\api\model\Document::getDirDocList($data);

        return $this->returnmsg(200,'success！',$result);
    }

    //PLM基础属性文档列表信息历史文档

    public function getDocList()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        if(empty($data['plm_dir_id'])){
            return $this->returnmsg(401,'文档目录结构ID不能为空！');
        }
        $data['page_no'] = empty($data['page_no']) ? 1 : $data['page_no'];
        $data['page_size'] = empty($data['page_size']) ? 10 : $data['page_size'];

        $result = \app\api\model\Document::getDocList($data);
        return $this->returnmsg(200,'success！',$result);

    }

    //删除单条的历史文档

    public function deleteDoc()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        if(empty($data['pd_id'])){
            return $this->returnmsg(401,'文档ID不能为空！');
        }
        if(empty($data['plm_no'])){
            return $this->returnmsg(401,'PLM物料编码不能为空！');
        }
        if(empty($data['plm_dir_id'])){
            return $this->returnmsg(401,'文档目录结构ID不能为空！');
        }

        $data['admin_id'] = $this->userInfo['admin_id'];
        $obj = \app\api\model\Document::checkList(array("pd_id" => $data['pd_id']));
        if($obj['admin_id'] != $data['admin_id']){
            return $this->returnmsg(402,'不能删除非自己上传的文档！');
        }

        $data['admin_name'] = $this->userInfo['nickname'];
        $result = \app\api\model\Document::deleteDoc($data);

        if($result == -1){
            return $this->returnmsg(402,'数据不存在不合法的数据，重新提交对应的参数');
        }else{
            return $this->returnmsg(200,'success！',$result);
        }
    }

    /**
     * 上传文档信息
     */
    public function uploadDoc()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        $rule = [
            'plm_dir_id'  => 'require|number',
            'plm_no'  => 'require',
            'upload_file'=>'require'
        ];
        $msg = [
            'plm_dir_id.require' => '文档目录结构ID不能为空!',
            'plm_no.require' => '文档目录结构ID不能为空!',
            'upload_file.require' => '上传的文件不能为空!'
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);
        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        $data['admin_id'] = $this->userInfo['admin_id'];
        $result = \app\api\model\Document::uploadDoc($data);
        if (is_array($result)) {
            return $this->returnmsg(200,'success！',$result);
        } else {
            return $this->returnmsg(401,$result);
        }
    }
}