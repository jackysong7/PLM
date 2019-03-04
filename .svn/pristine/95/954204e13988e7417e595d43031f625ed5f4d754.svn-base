<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/12
 * Time: 11:10
 */
namespace app\api\model;

use think\Db;
use think\Model;
class Image extends Model
{
    protected $table = 'plm_img';
    /**
     * 获取某条信息
     */
    public static function getImgInfo($param)
    {
        return self::get($param);
    }
    //获取图片列表信息
    public static function getImgList($param)
    {
        //查询当前plm_no的图片文件夹
        $one_where['plm_no'] = $param['plm_no'];
        $one_where['parent_id'] = $param['parent_id'];
        $one_where['status'] = 1;
        $folder_arr = Db::name('folder_img')->field('fi_id,folder_name')->where($one_where)->select();

        //查询当前层级的图片信息

        $img_where['plm_no'] = $param['plm_no'];
        $img_where['fi_id'] = $param['parent_id'];
        $img_where['status'] = 1;
        $img_arr = Db::name('img')->field('img_id,img_name,img_path,download_path,is_main_figure')->where($img_where)->select();


        return array("folder_list"=>$folder_arr,"img_list"=>$img_arr);
    }
    //修改图片名称
    public static function editImg($param)
    {
        $where['img_id'] = $param['img_id'];
        $data['img_name'] = $param['img_name'];
        $data['updatetime'] = time();
        $data['admin_id'] = $param['admin_id'];
        return self::update($data,$where);
    }
    //移动图片
    public static function editImgPath($param)
    {

        $where1['img_id'] = $param['img_id'];
        $where1['is_main_figure'] = 2;
        $is_main_figure = Db::name('img')->where($where1)->find();
        if(!$is_main_figure){
            $where['img_id'] = $param['img_id'];
            $data['fi_id'] = $param['fi_id'];
            self::update($data,$where);
            return 1;
        }else{
            return 2;
        }


    }
    //设定主图
    public static function setOnlyImg($param)
    {
        $where['fi_id'] = $param['fi_id'];
        $where['plm_no'] = $param['plm_no'];
        $data['is_main_figure'] = 1;
        $result = self::update($data,$where);
        if($result)
        {
            $where1['img_id'] = $param['img_id'];
            $where1['fi_id'] = $param['fi_id'];
            $where1['plm_no'] = $param['plm_no'];
            $data1['is_main_figure'] = 2;
            return self::update($data1,$where1);
        }
    }
    //删除图片
    public static function deleteImg($param)
    {
        $where['img_id'] = $param['img_id'];
        $data['status'] = 3;
        $data['admin_id'] = $param['admin_id'];
        $data['updatetime'] = time();
        return self::update($data,$where);
    }
    //上传图片
    public static function uploadImg($params)
    {
        $data = array(
            "plm_no"=>$params['plm_no'],
            "fi_id"=>$params['fi_id'],
            "img_name"=>$params['img_name'],
            "img_path"=>$params['img_path'],
            "download_path"=>$params['download_path'],
            "admin_id"=>$params['admin_id'],
            "status"=>1,
            "createtime"=>time()
        );
        Db::name('img')->insert($data);
        return Db::name('img')->getLastInsID();
    }
}