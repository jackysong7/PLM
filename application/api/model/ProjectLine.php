<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2018/7/6 16:34
// +----------------------------------------------------------------------
// | TITLE: 项目节点连线的数据操作
// +----------------------------------------------------------------------

namespace app\api\model;

use think\Db;
use think\Model;
class ProjectLine extends Model
{
    
    /**
     * 记录数据，方法reachTo需要用到
     */
    static $node_key_record = [];
    
    /*
     * 通过项目id、节点from查询到达节点的key
     */
    public static function getNodeTo($project_id,$from)
    {
        $where['from'] = $from;
        $where['project_id'] = $project_id;
        $info = DB::name('project_line')
            ->field('to')
            ->where($where)
            ->select();

        return $info;
    }


    /*
     * 通过项目id、节点to查询出发点key
     *
     */
    public static function getNodeFrom($project_id,$to)
    {
        $where['to'] = $to;
        $where['project_id'] = $project_id;
        $info = DB::name('project_line')
            ->field('from')
            ->where($where)
            ->select();

        return $info;
    }
    
    /**
     * 获取一个项目所有的连线
     * @param type $project_id
     */
    public static function getProjectLines($project_id)
    {
        $project_id = intval($project_id);
        return Db::name('project_line')->where(['project_id' => $project_id])->select();
    }
    
    /**
     * 验证在项目连线中是否能从A节点到达B节点
     * @param type $node_key_a 节点A的node_key，可以是数组，即多个节点
     * @param type $node_key_b 节点B
     * @param type $proj_lines 项目所有连线
     * @return boolean
     */
    public static function reachTo($node_key_a, $node_key_b, $proj_lines, $init = true)
    {
        if ($init)
        {
            self::$node_key_record = [];
        }
        if (!is_array($proj_lines) || empty($proj_lines))
        {
            return false;
        }
        // 转换成数组
        if (!is_array($node_key_a))
        {
            $node_key_a = [$node_key_a];
        }
        $to_keys = [];
        foreach($proj_lines as $line)
        {
            if (in_array($line['from'], $node_key_a) && !in_array($line['from'], self::$node_key_record))
            {
                array_push($to_keys, $line['to']);
            }
        }
        // 记录走过的节点
        self::$node_key_record = array_merge(self::$node_key_record, $node_key_a);
        if (empty($to_keys))
        {
            return false;
        } elseif (in_array($node_key_b, $to_keys)) {
            return true;
        } else {
            return self::reachTo($to_keys, $node_key_b, $proj_lines, false);
        }
    }
}