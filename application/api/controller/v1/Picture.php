<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/12
 * Time: 9:47
 * 图片文件夹管理
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Validate;

class Picture extends Api
{
    //需要权限验证的接口
    protected $needRight = array('addFolder','getFolderList','editFolder','deleteFolder');

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }

    /**
     * 新建文件夹
     */
    public function addFolder()
    {

        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'folder_name' => 'require',
            'parent_id' => 'require|integer',
            'plm_no' => 'require',
        ];

        $msg = [
            'folder_name.require' => '文件夹名称不能为空',
            'parent_id.require' => '上级ID不能为空且必须是数字',
            'plm_no.require' => 'PLM物料编码不能为空',
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Picture::addFolderImgInfo($array);

        if($result){
            $info = $result->toArray();
            $fi_id['fi_id'] = $info['fi_id'];
            return $this->returnmsg(200,'新增数据成功',$fi_id);
        }else{
            return $this->returnmsg(400,'新增数据失败');
        }
    }

    /**
     * 获取文件夹列表
     */
    public function getFolderList()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'plm_no' => 'require'
        ];

        $msg = [
            'plm_no.require' => 'PLM物料编码不能为空'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        $condition['plm_no'] = $array['plm_no'];
        $condition['status'] = 1;
        $field = 'fi_id,folder_name,parent_id';
        $list = \app\api\model\Picture::getFolderImgList($condition,$field);
        if(!empty($list)){
            $tree = $this->getTree($list, 0);
            return $this->returnmsg(200,'获取成功！',$tree);
        }else{
            return $this->returnmsg(200,'获取成功！',[]);
        }

    }

    /**
     * 文件夹列表，递归实现无限级文件夹树
     */
    private function getTree($data, $pId)
    {
        $tree = '';
        foreach($data as $k => $v){
            if($v['parent_id'] == $pId) {
                $childs = self::getTree($data, $v['fi_id']);
                if(!empty($childs)){
                    $v['childs'] = $childs;
                }
                unset($v['parent_id']);
                $tree[] = $v;
            }
        }
        return $tree;
    }


    /**
     * 修改文件夹
     */
    public function editFolder()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'folder_name' => 'require',
            'fi_id' => 'require|integer'
        ];

        $msg = [
            'folder_name.require' => '文件夹名称不能为空',
            'fi_id.require' => '文件夹ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Picture::editFolderImgInfo($array);
        if($result){
            return $this->returnmsg(200,'修改数据成功');
        }else{
            return $this->returnmsg(400,'修改数据失败');
        }
    }

    /**
     * 删除文件夹
     */
    public function deleteFolder()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'fi_id' => 'require|integer'
        ];

        $msg = [
            'fi_id.require' => '文件夹ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        //验证其文件夹下有无数据
        $condition['parent_id'] = $array['fi_id'];
        $condition['status'] = 1;
        $data = \app\api\model\Picture::getFolderImgInfo($condition);

        if(!empty($data)){
            return $this->returnmsg(403,'其文件夹下有数据，不可删除');
        }else{
            $whereImg['fi_id'] = $array['fi_id'];
            $whereImg['status'] = 1;
            $dataImg = \app\api\model\Image::getImgInfo($whereImg);
            if(!empty($dataImg)){
                return $this->returnmsg(403,'其文件夹下有数据，不可删除');
            }else{

                //操作者ID
                $array['admin_id'] = $this->userInfo['admin_id'];

                $result = \app\api\model\Picture::deleteFolderImgInfo($array);
                if($result){
                    return $this->returnmsg(200,'删除数据成功');
                }else{
                    return $this->returnmsg(400,'删除数据失败');
                }
            }
        }
    }

    /**
     * 移动文件夹路径
     */
    public function editFolderPath()
    {
        $json = $this->request->param();
        $array = json_decode($json['data'],true);

        //验证数据
        $rule = [
            'fi_id' => 'require|integer',
            'parent_id' => 'require|integer'
        ];

        $msg = [
            'fi_id.require' => '文件夹ID不能为空且必须是数字',
            'parent_id.require' => '上级ID不能为空且必须是数字'
        ];
        $this->validate = new Validate($rule, $msg);
        $res = $this->validate->check($array);
        if(!$res){
            return $this->returnmsg(401,$this->validate->getError());
        }

        //操作者ID
        $array['admin_id'] = $this->userInfo['admin_id'];

        $result = \app\api\model\Picture::editFolderPath($array);
        if($result){
            return $this->returnmsg(200,'移动文件夹路径成功');
        }else{
            return $this->returnmsg(400,'移动文件夹路径失败');
        }
    }
}