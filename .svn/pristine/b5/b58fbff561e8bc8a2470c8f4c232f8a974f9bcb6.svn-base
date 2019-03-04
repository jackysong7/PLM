<?php
/**
 * 图片列表
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/12
 * Time: 10:05
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Controller;
use think\Validate;
class Image extends Api
{
    protected $needRight = ["deleteImg","uploadImg","editImg","setOnlyImg"];
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->auth->getUser();
    }
    /**
     * 图片下载
     */
    public function  download()
    {
        $path = $this->request->request('download_path');//得到文件名
//        header( "Content-Disposition:  attachment;  filename=".$fileName); //告诉浏览器通过附件形式来处理文件
//        //header('Content-Length: ' . filesize($fileName)); //下载文件大小
//        readfile($fileName);  //读取文件内容
        if(!empty($path)){
			$name = $this->request->request('name');
            $name = !empty(name) ? $name : basename($path);
            $this->readFile($path, $name);
        }

    }
    public function readFile($path, $name)
    {
        header('Pragma: public'); // required
        header('Expires: 0'); // no cache
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private',false);
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename="'.$name.'"');//告诉浏览器通过附件形式来处理文件
        header('Content-Transfer-Encoding: binary');
        header('Connection: close');
        readfile($path); // 读取文件内容
        exit();
    }
    /**
     * 获取图片列表信息
     */
    public function getImgList()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        $rule = [
            'plm_no'  => 'require'
        ];
        $msg = [
            'plm_no.require' => 'PLM物料编码不能为空！'
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);
        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }

        $row = \app\api\model\Image::getImgList($data);

        return $this->returnmsg(200,'success!',$row);
    }

    /**
     * 修改图片名称
     */

    public function editImg()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'img_name'  => 'require',
            'img_id'  => 'require|number',
        ];
        $msg = [
            'img_name.require' => '图片名称不能为空！',
            'img_id.require' => '图片ID不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);
        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        $data['admin_id'] = $this->userInfo['admin_id'];
        $row = \app\api\model\Image::editImg($data);
        if($row){
            return $this->returnmsg(200,'success！');
        }else{
            return $this->returnmsg(400,'修改图片名称失败！');
        }
    }

    /**
     * 移动图片路径
     */

    public function editImgPath()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'fi_id'  => 'require|number',
            'img_id'  => 'require|number',
        ];
        $msg = [
            'fi_id.require' => '文件夹的ID不能为空！',
            'img_id.require' => '图片ID不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);
        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        $row = \app\api\model\Image::editImgPath($data);
        if($row == 1){
            return $this->returnmsg(200,'success！');
        }else{
            return $this->returnmsg(400,'该主图不能移动');
        }
    }
    /**
     * 设置图片为主图
     */

    public function setOnlyImg()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'fi_id'  => 'require|number',
            'plm_no'  => 'require',
            'img_id'  => 'require|number',
        ];
        $msg = [
            'fi_id.require' => '文件夹的ID不能为空！',
            'plm_no.require' => 'PLM物料编码不能为空！',
            'img_id.require' => '图片ID不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);
        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        $row = \app\api\model\Image::setOnlyImg($data);
        if($row){
            return $this->returnmsg(200,'success！');
        }else{
            return $this->returnmsg(400,'主图设定失败！');
        }
    }

    /**
     * 删除图片
     */

    public function deleteImg()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'img_id'  => 'require|number',
        ];
        $msg = [
            'img_id.require' => '图片ID不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);
        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        $data['admin_id'] = $this->userInfo['admin_id'];
        $row = \app\api\model\Image::deleteImg($data);
        if($row){
            return $this->returnmsg(200,'success！');
        }else{
            return $this->returnmsg(400,'删除图片失败！');
        }
    }

    /**
     * 上传图片
     */
    public function uploadImg()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);
        $rule = [
            'img_name'  => 'require',
            'img_path'  => 'require',
            'download_path'  => 'require',
            'fi_id'  => 'require|number',
            'plm_no'  => 'require'
        ];
        $msg = [
            'img_name.require' => '图片名称不能为空！',
            'img_path.require' => '图片路径不能为空！',
            'download_path.require' => '图片下载路径不能为空！',
            'fi_id.require' => '文件夹id不能为空！',
            'plm_no.require' => 'PLM物料编码不能为空！'
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($data);
        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }

        $data['admin_id'] = $this->userInfo['admin_id'];
        $row = \app\api\model\Image::uploadImg($data);

        return $this->returnmsg(200,'success！',$row);
    }
}