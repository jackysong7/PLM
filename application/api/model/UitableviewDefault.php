<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2018/7/17 16:23
// +----------------------------------------------------------------------
// | TITLE: 表格固定模块数据操作模型
// +----------------------------------------------------------------------

namespace app\api\model;

use think\Model;
use think\Db;
class UitableviewDefault extends Model
{
    protected $table = 'plm_uitableview_default';
    /*
     * 新增数据
     */
    public static function add($params)
    {

        if(!empty($params['project_id'])) $data['project_id'] = $params['project_id'];//项目ID
        if(!empty($params['node_id'])) $data['node_id'] = $params['node_id'];//项目节点ID
        if(!empty($params['tpl_id'])) $data['tpl_id'] = $params['tpl_id'];//文档模板ID
        if(!empty($params['user_face'])) $data['user_face'] = $params['user_face'];//用户画像
        if(!empty($params['usage_scenario'])) $data['usage_scenario'] = $params['usage_scenario'];//场景使用
        if(!empty($params['pain_spot_solve'])) $data['pain_spot_solve'] = $params['pain_spot_solve'];//痛点解决
        if(!empty($params['open_mode'])) $data['open_mode'] = $params['open_mode'];//开案模式
        if(!empty($params['product_location'])) $data['product_location'] = $params['product_location'];//产品定位
        if(!empty($params['channel_matching'])) $data['channel_matching'] = $params['channel_matching'];//渠道匹配
        if(!empty($params['product_cost'])) $data['product_cost'] = $params['product_cost'];//产品成本
        if(!empty($params['target_cost'])) $data['target_cost'] = $params['target_cost'];//目标成本
        if(!empty($params['channel_pricing'])) $data['channel_pricing'] = $params['channel_pricing'];//渠道定价
        if(!empty($params['first_number'])) $data['first_number'] = $params['first_number'];//首单数量
        if(!empty($params['gross_margin'])) $data['gross_margin'] = $params['gross_margin'];//毛利率
        if(!empty($params['lifecycle'])) $data['lifecycle'] = $params['lifecycle'];//生命周期
        if(!empty($params['annual_sales'])) $data['annual_sales'] = $params['annual_sales'];//年销售量
        if(!empty($params['annual_money'])) $data['annual_money'] = $params['annual_money'];//年销售额
        if(!empty($params['market_time'])) $data['market_time'] = $params['market_time'];//上市时间
        if(!empty($params['version'])) $data['version'] = $params['version'];//版本号
        if(!empty($params['creator_id'])) $data['creator_id'] = $params['creator_id'];//备注
        if(!empty($params['remarks'])) $data['remarks'] = $params['remarks'];//备注
        $data['create_at'] = time();//创建时间

        //$result = self::add($params);
        $result = self::insertGetId($data);
        return $result;
    }

    /*
     * 修改数据
     */
    public static function updateInfo($where,$params)
    {

        if(!empty($params['project_id'])) $data['project_id'] = $params['project_id'];//项目ID
        if(!empty($params['node_id'])) $data['node_id'] = $params['node_id'];//项目节点ID
        if(!empty($params['tpl_id'])) $data['tpl_id'] = $params['tpl_id'];//文档模板ID
        if(!empty($params['user_face'])) $data['user_face'] = $params['user_face'];//用户画像
        if(!empty($params['usage_scenario'])) $data['usage_scenario'] = $params['usage_scenario'];//场景使用
        if(!empty($params['pain_spot_solve'])) $data['pain_spot_solve'] = $params['pain_spot_solve'];//痛点解决
        if(!empty($params['open_mode'])) $data['open_mode'] = $params['open_mode'];//开案模式
        if(!empty($params['product_location'])) $data['product_location'] = $params['product_location'];//产品定位
        if(!empty($params['channel_matching'])) $data['channel_matching'] = $params['channel_matching'];//渠道匹配
        if(!empty($params['product_cost'])) $data['product_cost'] = $params['product_cost'];//产品成本
        if(!empty($params['target_cost'])) $data['target_cost'] = $params['target_cost'];//目标成本
        if(!empty($params['channel_pricing'])) $data['channel_pricing'] = $params['channel_pricing'];//渠道定价
        if(!empty($params['first_number'])) $data['first_number'] = $params['first_number'];//首单数量
        if(!empty($params['gross_margin'])) $data['gross_margin'] = $params['gross_margin'];//毛利率
        if(!empty($params['lifecycle'])) $data['lifecycle'] = $params['lifecycle'];//生命周期
        if(!empty($params['annual_sales'])) $data['annual_sales'] = $params['annual_sales'];//年销售量
        if(!empty($params['annual_money'])) $data['annual_money'] = $params['annual_money'];//年销售额
        if(!empty($params['market_time'])) $data['market_time'] = $params['market_time'];//上市时间
        if(!empty($params['version'])) $data['version'] = $params['version'];//版本号
        if(!empty($params['creator_id'])) $data['creator_id'] = $params['creator_id'];//备注
        if(!empty($params['remarks'])) $data['remarks'] = $params['remarks'];//备注

        $result = Db::name('uitableview_default')->where($where)->update($data);
        return $result;
    }

    /*
     * 查询最高版本信息
     */
    public static function getUdVersion($where)
    {
        return self::where($where)->max('version');
    }

    /*
     * 按条件查找数据
     */
    public static function getInfo($where,$field = '*')
    {
        if(is_array($where))
        {
            $version = self::getUdVersion($where);
            return self::where($where)->where('version',$version)->field($field)->find();
        }
        return false;
    }

    /*
     * 通过tpl_id修改为删除状态（伪删除）
     */
    public static function setState($docId,$tplId)
    {
        $result = Db::name('uitableview_default')
            ->where('ud_id',$docId)->setField('status',-1);
        if(!$result)
        {
            return false;
        }
        $result = Db::name('uitableview')
            ->where('tpl_id',$tplId)->setField('status',-1);

        return $result;
    }

    /*
     * 提交开发立项表审核,改状态为已提交
     */
    public static function edit($params)
    {
        $editResult = self::get(['ud_id'=>$params['doc_id']]);

        if($editResult)
        {
            $where = [
                'ud_id'=>$params['doc_id'],
            ];
            $data = [
                'submit_status' => 1,
                'submit_time'=>time(),
                'audit_status' => 0
            ];
            $result = Db::name('uitableview_default')->where($where)->update($data);
            //被驳回再重新提交，删除之前的审核记录
            Db::name('relation')->where(['target_id' => $params['doc_id'],'target_type' => 6,'role_type' => 5])->delete();
            return $result;
        }
        return false;
    }

    /*
    * 审核文档
    */
    public static function audit($params, &$error = '')
    {
        $auditResult = self::get(['ud_id'=>$params['doc_id']]);
        if($auditResult)
        {
            if ($auditResult['project_id'] && $error = Project::checkProjectPause($auditResult['project_id'])) {
                return false;
            }

            $where = [
                'ud_id'=>$params['doc_id'],
            ];
            $data = [
                'audit_status' => $params['audit_status'],
                'audit_note' => !empty($params['audit_note'])?($params['audit_note']):'',
                'audit_time' => time()
            ];
            $result = Db::name('uitableview_default')->where($where)->update($data);
            return $result;
        }
        return false;
    }

    /*
     * 通过tpl_id删除数据
     */
    public static function delInfo($where)
    {
       // return Db::name('uitableview_default')->where($where)->delete();
        $result = Db::name('uitableview_default')
            ->where($where)->setField('status',-1);
        if(!$result)
        {
            return false;
        }
        $result = Db::name('uitableview')
            ->where('tpl_id',$where['tpl_id'])->setField('status',-1);

        return $result;
    }

    public static function docIdData($where,$field)
    {
        return Db::name('uitableview_default')->where($where)->field($field)->order('ud_id desc')->find();
    }

    public static function udAuditData($where,$field)
    {
        $info = Db::name('uitableview_default')->where($where)->field($field)->order('ud_id desc')->select();
        $res = true;
        if (!empty($info)) {
            foreach ($info as $audit) {
                if($audit['audit_status'] == 1 || $audit['audit_status'] == -1)
                {
                    $res = true;
                }else{
                    $res = false;
                    break;
                }
            }
        }

        return $res;
    }

    public static function reedit($where,$version)
    {
        $data = Db::name('uitableview_default')->where($where)->where(['version'=>$version])->find();
        if ($data['audit_status'] == -1) {
            return Db::name('uitableview_default')->where($where)->where(['version'=>$version])->update(['reedit'=> 1]);
        }
        return false;
    }
}