<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2018/7/10 13:51
// +----------------------------------------------------------------------
// | TITLE: 标准资源库名称Model层数据操作
// +----------------------------------------------------------------------

namespace app\api\model;
use think\Db;
use think\Model;

class ResourceNameModel extends Model
{
    /*
     * 通过process_id获取资源库的数据
     */
    public static function getResourceById($processId)
    {
        $where['r.year'] = date('Y');
        $where['r.process_id'] = $processId;
        $where['r.type'] = 1;
        $where['r.status'] = 1;
        $list = Db::name('resource_name')
            ->alias('r')
            ->join('dir', 'r.process_id = dir.plm_dir_id', 'LEFT')
            ->field('r.id,r.year,dir.plm_dir_name')
            ->where($where)
            ->find();
        if(empty($list))//前端要求，没有数据给空值
        {
            $list = [
                'id' =>'',
                'year'=>'',
                'plm_idr_name'=>''
            ];
        }
        return $list;
    }
}