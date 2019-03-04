<?php
/**
 * Created by PhpStorm.
 * User: licw
 * Date: 2018/4/3
 * Time: 15:55
 * PLM项目文档目录属性
 */
namespace app\api\model;
use think\Model;
use think\Db;

class Dir extends Model
{
    protected $table = 'plm_dir';

    public static function checkList($data){
        return self::get($data);
    }
    /**
     * 新增数据
     */
    public static function insertDirInfo($data)
    {
        $data['status'] = 1;
        $data['createtime'] = time();
        return self::create($data);
    }

    /**
     * 获取数据列表
     */
    public static function getDirDataList($condition,$field = '*',$order = 'sort'){
        return self::table('plm_dir')->field($field)->where($condition)->order($order)->select();
    }

    /**
     * 获取单条信息
     */
    public static function getDirDataInfo($condition)
    {
        $dirWhere['plm_dir_id'] = $condition['plm_dir_id'];
        $dirWhere['status'] = array('NEQ',3);
        return self::get($condition);
    }

    /**
     * 修改某条信息
     */
    public static function editDirInfo($param)
    {
        $dirWhere['plm_dir_id'] = $param['plm_dir_id'];

        if(!empty($param['plm_dir_name'])){
            $dirData['plm_dir_name'] = $param['plm_dir_name'];
        }

        $dirData['job_list'] = $param['job_list'];

        if(isset($param['sort'])){
            $dirData['sort'] = $param['sort'];
        }

        $dirData['updatetime'] = time();
        return self::update($dirData,$dirWhere);
    }

    /**
     * 删除数据
     */
    public static function deleteDirInfo($param)
    {
        $dirWhere['plm_dir_id'] = $param['plm_dir_id'];
        $dirData['status'] = 3;
        $dirData['updatetime'] = time();
        return self::update($dirData,$dirWhere);
    }
}