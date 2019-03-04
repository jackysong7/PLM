<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2018/7/11 13:45
// +----------------------------------------------------------------------
// | TITLE: 
// +----------------------------------------------------------------------

namespace app\api\model;

use think\Model;
use think\Db;

class Material extends Model
{
    protected $table = 'plm_material';

    /**
     * 通过物料编码获取物料名称等数据
     */
    public static function getMaterialInfo($materialCode)
    {
        $list = Db::name('material')
            ->field('material_name,specifications,basic_unit')
            ->where('material_code',$materialCode)
            ->find();

        return $list;
    }

    /**
     * 批量添加
     * @param  array $params 二维数组
     */
    public static function addInfo($params)
    {
        Db::startTrans();
        try{
            $count = count($params);
            $result = Db::name('material')->insertAll($params);
            if($result == $count);
            {
                Db::commit();
                return true;
            }
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

    /**
     * 批量修改或添加
     * @param  array $params 二维数组
     */
    public static function updateInfo0($params)
    {
        $list = [];$addList=[];
        foreach($params as $k=>$v)
        {
            $materialId = Db::name('material')->where('material_code',$v['FNumber'])->value('material_id');
            if($materialId)
            {
                $list[$k]['material_id'] = $materialId;
                $list[$k]['material_code'] = $v['FNumber'];
                $list[$k]['material_name'] = $v['FName'];
                $list[$k]['mg_id'] = $v['FMaterialGroup'];//物料分组ID
                $list[$k]['mg_code'] = $v['FMaterialGroup.FNumber'];//物料分组编码
                $list[$k]['specifications'] = $v['FSPECIFICATION'];//规格型号
                $list[$k]['description'] = $v['FDESCRIPTION'];//描述
                $list[$k]['specifications_code'] = $v['F_PAEZ_TEXT'];//型号编码
                $list[$k]['material_attribute'] = $v['FERPCLSID'];//物料属性
                $list[$k]['basic_unit'] = $v['FBASEUNITID.FNumber'];//基本单位
                $list[$k]['updatetime'] = time();

            }else{
                $addList[$k]['material_code'] = $v['FNumber'];
                $addList[$k]['material_name'] = $v['FName'];
                $addList[$k]['mg_id'] = $v['FMaterialGroup'];//物料分组ID
                $addList[$k]['mg_code'] = $v['FMaterialGroup.FNumber'];//物料分组编码
                $addList[$k]['specifications'] = $v['FSPECIFICATION'];//规格型号
                $addList[$k]['description'] = $v['FDESCRIPTION'];//描述
                $addList[$k]['specifications_code'] = $v['F_PAEZ_TEXT'];//型号编码
                $addList[$k]['material_attribute'] = $v['FERPCLSID'];//物料属性
                $addList[$k]['basic_unit'] = $v['FBASEUNITID.FNumber'];//基本单位
                $addList[$k]['createtime'] = time();
            }
        }
        if($list)
        {
            $material = new Material;
            $material->saveAll($list);
            unset($list);

        }
        if($addList)
        {
            (new Material)->insertAll($addList);
            unset($addList);

        }
    }

    public static function updateInfo($data)
    {
        $list = [];
        foreach ($data as $k => $v) {
            $materialId = Db::name('material')->where('material_code', $v['FNumber'])->value('material_id');
            if ($materialId) {
                $list[$k]['material_id'] = $materialId;
                $list[$k]['updatetime'] = time();
            } else {
                $list[$k]['createtime'] = time();

            }

            $list[$k]['material_code'] = $v['FNumber'];
            $list[$k]['material_name'] = $v['FName'];
            $list[$k]['mg_id'] = $v['FMaterialGroup'];//物料分组ID
            $list[$k]['mg_code'] = $v['FMaterialGroup.FNumber'];//物料分组编码
            $list[$k]['specifications'] = $v['FSPECIFICATION'];//规格型号
            $list[$k]['description'] = $v['FDESCRIPTION'];//描述
            $list[$k]['specifications_code'] = $v['F_PAEZ_TEXT'];//型号编码
            $list[$k]['material_attribute'] = $v['FERPCLSID'];//物料属性
            $list[$k]['basic_unit'] = $v['FBASEUNITID.FNumber'];//基本单位

            $is_purchase = !empty($v['FIsPurchase']) ? '允许采购' : '';
            $is_inventory = !empty($v['FIsInventory']) ? '允许库存' : '';
            $is_sub_contract = !empty($v['FIsSubContract']) ? '允许委外' : '';
            $is_sale = !empty($v['FIsSale']) ? '允许销售' : '';
            $is_produce = !empty($v['FIsProduce']) ? '允许生产' : '';
            $is_asset = !empty($v['FIsAsset']) ? '允许资产' : '';
            $control_str = $is_purchase . '|' . $is_inventory . '|' . $is_sub_contract . '|' . $is_sale . '|' . $is_produce . '|' . $is_asset;
            $control_arr = explode('|', $control_str);

            $list[$k]['is_suite'] = intval($v['FSuite']);
            $list[$k]['material_category'] = $v['F_PAEZ_Assistant1.FNumber']; //物料分类
            /* $list[$k]['is_purchase'] = $v['FIsPurchase'];
             $list[$k]['is_inventory'] = $v['FIsInventory'];
             $list[$k]['is_sub_contract'] = $v['FIsSubContract'];
             $list[$k]['is_sale'] = $v['FIsSale'];
             $list[$k]['is_produce'] = $v['FIsProduce']; //允许生产
             $list[$k]['is_asset'] = $v['FIsAsset']; //允许资产*/
            $list[$k]['control'] = implode('|', array_filter($control_arr));
            $list[$k]['rate'] = $v['FTaxRateId.FNumber']; //默认税率
            $list[$k]['inventory_type'] = $v['FCategoryID.FNumber']; //存货类别
            $list[$k]['tax_type'] = $v['FTaxType.FNumber']; //税分类
            $list[$k]['inventory_unit'] = $v['FStoreUnitID.FNumber']; //库存单位
            $list[$k]['assistant_unit'] = $v['FAuxUnitID.FNumber']; //辅助单位
            $list[$k]['unit_convert'] = $v['FUnitConvertDir']; //换算方向
            $list[$k]['start_batch'] = $v['FIsBatchManage']; //启用批次管理
            $list[$k]['batch_num_extra'] = $v['FIsExpParToFlot']; //批号附属信息
            $list[$k]['is_expiration'] = $v['FIsKFPeriod']; //启用保质期管理
            $list[$k]['sale_unit'] = $v['FSaleUnitId.FNumber']; //销售单位
            $list[$k]['sale_pricing_unit'] = $v['FSalePriceUnitId.FNumber']; //销售计价单位 *
            $list[$k]['allow_return'] = $v['FIsReturn']; //允许退货
            $list[$k]['purchase_unit'] = $v['FPurchaseUnitId.FNumber']; //采购单位
            $list[$k]['purchase_price_unit'] = $v['FPurchasePriceUnitId.FNumber']; //采购计价单位
            $list[$k]['is_quota'] = is_numeric($v['FIsQuota']) ? $v['FIsQuota'] : 0; //启用配额管理
            $list[$k]['quota_way'] = $v['FQuotaType']; //配额方式
            $list[$k]['min_split_amount'] = $v['FMinSplitQty']; //最小拆分数量
            $list[$k]['is_return_material'] = $v['FIsReturnMaterial']; //允许退料
            $list[$k]['outsource_unit'] = $v['FSubconUnitId.FNumber'];
            $list[$k]['outsource_price_unit'] = $v['FSubconPriceUnitId.FNumber'];
            $list[$k]['product_unit'] = $v['FProduceUnitId.FNumber'];
            $list[$k]['product_type'] = $v['FProduceBillType.FNumber'];
            $list[$k]['standard_labor_unit'] = $v['FStandHourUnitId'];
            $list[$k]['standard_labor_hour'] = $v['FPerUnitStandHour'];
            $list[$k]['reverse_time'] = is_numeric($v['FBKFLTime']) ? $v['FBKFLTime'] : 0;
            $list[$k]['over_give_control'] = $v['FOverControlMode'];
            $list[$k]['min_issue_qty'] = $v['FMinIssueQty'];
            $list[$k]['min_give_unit'] = $v['FMinIssueUnitId.FNumber'];
            $list[$k]['plan_strategy'] = $v['FPlanningStrategy']; //计划策略
            $list[$k]['manufacture_strategy'] = $v['FMfgPolicyId.FNumber']; //制造策略*
            $list[$k]['order_strategy'] = $v['FOrderPolicy']; //订货策略
            $list[$k]['plan_area'] = is_numeric($v['FPlanWorkshop.FNumber']) ? $v['FPlanWorkshop.FNumber'] : 0; //计划区
            $list[$k]['fixed_lead_time'] = $v['FFixLeadTime']; //固定提前期
            $list[$k]['fixed_lead_time_unit'] = $v['FFixLeadTimeType']; //固定提前期单位
            $list[$k]['vary_lead_time'] = $v['FVarLeadTime']; //变动提前期
            $list[$k]['vary_lead_time_unit'] = $v['FVarLeadTimeType']; //变动提前期单位
            $list[$k]['check_lead_time'] = $v['FCheckLeadTime']; //检查提前期
            $list[$k]['check_lead_time_unit'] = $v['FCheckLeadTimeType']; //检查提前期单位
            $list[$k]['order_interval_unit'] = $v['FOrderIntervalTimeType']; //订货间隔期单位
            $list[$k]['order_interval_time'] = $v['FOrderIntervalTime']; //订货间隔期
            $list[$k]['mrp_combine'] = $v['FIsMrpComReq'];
            $list[$k]['offset_time_unit'] = $v['FPlanOffsetTimeType']; //时间单位
            $list[$k]['offset_time'] = $v['FPlanOffsetTime']; //偏置时间

            $list[$k]['expiration_date_online'] = $v['FOnlineLife']; //在架寿命期
            $list[$k]['expiration_date'] = $v['FExpPeriod']; //保质期
            $list[$k]['batch_num_rule'] = $v['FBatchRuleID.FNumber'];
            $list[$k]['issue_qty'] = $v['FIssueType'];
            $list[$k]['product_level'] = $v['F_PAEZ_Text1'];
            $list[$k]['expiration_unit'] = $v['FExpUnit']; //保质期单位

            $list[$k]['supply_from'] = $v['FSupplySourceId']; //供应来源
        }

        if($list)
        {
            $material = new Material;
            $material->saveAll($list);
            unset($list);

        }
    }
}
