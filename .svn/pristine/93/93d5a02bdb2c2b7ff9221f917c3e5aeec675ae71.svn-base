<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/12
 * Time: 10:12
 * 图片文件夹管理
 */

namespace app\api\model;

use think\Model;
use think\Db;

class Picture extends Model
{
    protected $table = 'plm_folder_img';

    /**
     * 新增数据
     */
    public static function addFolderImgInfo($data)
    {
        $data['createtime'] = time();
        $data['status'] = 1;
        return self::create($data);
    }

    /**
     * 获取数据列表
     */
    public static function getFolderImgList($condition = '',$field = '*',$order = 'fi_id')
    {
        return Db::table('plm_folder_img')->where($condition)->field($field)->order($order)->select();
    }

    /**
     * 获取某条信息
     */
    public static function getFolderImgInfo($param)
    {
        return self::get($param);
    }

    /**
     * 修改
     */
    public static function editFolderImgInfo($param)
    {
        $where['fi_id'] = $param['fi_id'];
        $data['folder_name'] = $param['folder_name'];
        $data['updatetime'] = time();
        return self::update($data,$where);
    }

    /**
     * 移动文件夹路径
     */
    public static function editFolderPath($param)
    {
        $where['fi_id'] = $param['fi_id'];
        $data['parent_id'] = $param['parent_id'];
        $data['updatetime'] = time();
        return self::update($data,$where);
    }

    /**
     * 删除
     */
    public static function deleteFolderImgInfo($param)
    {
//        return self::destroy($param);
        $where['fi_id'] = $param['fi_id'];
        $data['status'] = 3;
        $data['updatetime'] = time();
        return self::update($data,$where);
    }
}