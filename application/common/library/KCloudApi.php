<?php

namespace app\common\library;


class KCloudApi
{
    /**
     * 保存并提交物料
     * @param $param
     * @throws KCloudException
     */
    public static function saveMaterial($param)
    {
        $control = explode('|', $param['control']);
        $is_purchase = in_array('允许采购', $control) ? true : false;
        $is_inventory = in_array('允许库存', $control) ? true : false;
        $is_subcontract = in_array('允许委外', $control) ? true : false;
        $is_sale = in_array('允许销售', $control) ? true : false;
        $is_produce = in_array('允许生产', $control) ? true : false;
        $is_asset = in_array('允许资产', $control) ? true : false;

        $inventory_attr = json_decode($param['inventory_attr'], true);

        $data = [
            "IsDeleteEntry" => "True",
            "IsVerifyBaseDataField" => "false",
            "IsEntryBatchFill" => "True",
            "Model" => [
                "FMATERIALID" => "0",
                "FCreateOrgId" => ["FNumber" => $param['create_org']], //创建组织，（必填项）*
                "FUseOrgId" => ["FNumber" => $param['use_org']], //使用组织，（必填项）*
                "FNumber" => $param['material_code'], //编码
                "FName" => $param['material_name'], //名称，（必填项）
                "FSpecification" => $param['specifications'], //规格型号
                "FMnemonicCode" => "", //助记码
                "FOldNumber" => $param['material_code_old'], //旧物料编码
                "FDescription" => $param['description'], //描述
                "FMaterialGroup" => ["FNumber" => $param['mg_code']], //物料分组，（必填项）*
                "FImgStorageType" => "B", //图片存储类型
                "FIsSalseByNet" => false, //是否网销
                "F_PAEZ_Text" => $param['specifications_code'], //型号编码
                "F_PAEZ_Text1" => $param['product_level'], //产品级别
                "F_PAEZ_Text2" => $param['product_package_name'], //产品包装名称
                "F_PAEZ_Text3" => $param['product_chinese_name'], //产品中文名称
                "F_PAEZ_Text4" => $param['model_no'], //Model NO
                "F_PAEZ_Assistant1" => ["FNumber" => $param['material_category']], //物料分类
                "F_PAEZ_Assistant" => ["FNumber" => ""], //产品系列
                "SubHeadEntity" => [
                    "FErpClsID" => $param['material_attribute'], //物料属性，（必填项）*
                    "FCategoryID" => ["FNumber" => $param['inventory_type']], //存货类别，（必填项）*
                    "FTaxType" => ["FNumber" => $param['tax_type']], //税分类
                    "FTaxRateId" => ["FNumber" => $param['rate']], //默认税率
                    "FBaseUnitId" => ["FNumber" => $param['basic_unit']], //基本单位，（必填项）*
                    "FIsPurchase" => $is_purchase, //允许采购
                    "FIsInventory" => $is_inventory, //允许库存
                    "FIsSubContract" => $is_subcontract, //允许委外
                    "FIsSale" => $is_sale, //允许销售
                    "FIsProduce" => $is_produce, //允许生产
                    "FIsAsset" => $is_asset, //允许资产
                    "FGROSSWEIGHT" => 0, //毛重
                    "FNETWEIGHT" => 0, //净重
                    "FWEIGHTUNITID" => ["FNumber" => "kg"], //重量单位
                    "FLENGTH" => 0, //长
                    "FWIDTH" => 0, //宽
                    "FHEIGHT" => 0, //高
                    "FVOLUME" => 0, //体积
                    "FVOLUMEUNITID" => ["FNumber" => "m"], //尺寸单位
                    "FSuite" => $param['is_suite'], //套件，（必填项）*
                    "FCostPriceRate" => 0, //结算成本价加减价比例(%)
                ],
                //库存
                "SubHeadEntity1" => [
                    "FStoreUnitID" => ["FNumber" => $param['inventory_unit']], //库存单位，（必填项）*
                    "FAuxUnitID" => ["FNumber" => ""], //辅助单位
                    "FUnitConvertDir" => $param['unit_convert'], //换算方向,（必填项）*
                    "FIsLockStock" => true, //可锁库
                    "FIsCycleCounting" => true, //启用盘点周期
                    "FCountCycle" => 1, //盘点周期单位
                    "FCountDay" => 1, //盘点周期
                    "FIsMustCounting" => false, //必盘
                    "FIsBatchManage" => $param['start_batch'] ? true : false, //启用批号管理
                    "FBatchRuleID" => ["FNumber" => $param['batch_num_rule']], //启用批号管理 PHBM001
                    "FIsKFPeriod" => $param['is_expiration'] ? true : false, //启用保质期管理
                    "FIsExpParToFlot" => $param['batch_num_extra'] ? true : false, //批号附属信息
                    "FExpUnit" => $param['expiration_unit'], //保证期单位 D/M/Y
                    "FExpPeriod" => $param['expiration_date'], //保质期
                    "FOnlineLife" => $param['expiration_date_online'], //在架寿命期
                    "FRefCost" => 0, //参考成本
                    "FCurrencyId" => ["FNumber" => "PRE001"], //币别
                    "FIsEnableMinStock" => false, //启用最小库存
                    "FIsEnableMaxStock" => false, //启用最大库存
                    "FIsEnableSafeStock" => false, //启用安全库存
                    "FIsEnableReOrder" => false, //启用再订货点
                    "FMinStock" => 0, //最小库存
                    "FSafeStock" => 0, //安全库存
                    "FReOrderGood" => 0, //再订货点
                    "FEconReOrderQty" => 0, //经济订货批量
                    "FMaxStock" => 0, //最大库存
                    "FIsSNManage" => false, //库存管理
                    "FIsSNPRDTracy" => false, //生产追溯
                    "FSNManageType" => 1, //业务范围，（必填项）*
                    "FSNGenerateTime" => 1, //序列号生成时机，（必填项）*
                    "FBoxStandardQty" => 0 //单箱标准数量
                ],
                //销售
                "SubHeadEntity2" => [
                    "FSaleUnitId" => ["FNumber" => $param['sale_unit']], //销售单位，（必填项）*
                    "FSalePriceUnitId" => ["FNumber" => $param['sale_pricing_unit']], //销售计价单位，（必填项）*
                    "FOrderQty" => 0, //起订量
                    "FMinQty" => 0, //最小批量
                    "FMaxQty" => 100000, //最大批量
                    "FOutStockLmtH" => 0, //超发上限(%)
                    "FOutStockLmtL" => 0, //超发下限(%)
                    "FAgentSalReduceRate" => 0, //代理销售减价比例(%)
                    "FIsATPCheck" => false, //ATP检查
                    "FIsReturnPart" => false, //部件可退
                    "FIsInvoice" => false, //可开票
                    "FIsReturn" => $param['allow_return'] ? true : false, //允许退货
                    "FAllowPublish" => false, //允许发布到订货平台
                    "FISAFTERSALE" => true, //启用售后服务
                    "FISPRODUCTFILES" => true, //生成产品档案
                    "FISWARRANTED" => false,
                    "FWARRANTY" => 0,
                    "FWARRANTYUNITID" => "D",
                    "FOutLmtUnit" => "SAL", //超发控制单位
                    "FIsTaxEnjoy" => false, //享受税收优惠政策
                    "FTaxDiscountsType" => 0, //税收优惠政策类型
                ],
                //采购
                "SubHeadEntity3" => [
                    "FBaseMinSplitQty" => 0, //基本单位最小拆分数量
                    "FPurchaseUnitId" => ["FNumber" => $param['purchase_unit']], //采购单位，（必填项）*
                    "FPurchasePriceUnitId" => ["FNumber" => $param['purchase_price_unit']], //采购计价单位，（必填项）*
                    "FIsQuota" => $param['is_quota'] ? true : false, //启用配额管理
                    "FQuotaType" => $param['quota_way'], //配额方式
                    "FMinSplitQty" => $param['min_split_amount'], //最小拆分数量
                    "FIsVmiBusiness" => false, //VMI业务
                    "FEnableSL" => false, //启用商联在线(6.1弃用)
                    "FIsPR" => false, //需要请购
                    "FIsReturnMaterial" => $param['is_return_material'] ? true : false, //允许退料
                    "FIsSourceControl" => false, //货源控制
                    "FReceiveMaxScale" => 0, //收货上限比例(%)
                    "FReceiveMinScale" => 0, //收货下限比例(%)
                    "FReceiveAdvanceDays" => 0, //收货提前天数
                    "FReceiveDelayDays" => 0, //收货延迟天数
                    "FAgentPurPlusRate" => 0, //代理采购加成比例
                    "FPrintCount" => 1, //重复打印数
                    "FMinPackCount" => 1, //最小包装数
                ],
                //计划
                "SubHeadEntity4" => [
                    "FPlanMode" => 0,
                    "FBaseVarLeadTimeLotSize" => 0, //基本变动提前期批量
                    "FPlanningStrategy" => $param['plan_strategy'], //计划策略，（必填项）*
                    "FMfgPolicyId" => [
                        "FNumber" => $param['manufacture_strategy']
                    ], //制造策略
                    "FOrderPolicy" => $param['order_strategy'], //订货策略，（必填项）*
                    "FPlanWorkshop" => ["FNumber" => $param['plan_area']], //计划区 JHQ001_SYS
                    "FFixLeadTime" => $param['fixed_lead_time'], //固定提前期
                    "FFixLeadTimeType" => $param['fixed_lead_time_unit'], //固定提前期单位，（必填项）*
                    "FVarLeadTime" => $param['vary_lead_time'], //变动提前期
                    "FVarLeadTimeType" => $param['vary_lead_time_unit'], //变动提前期单位，（必填项）*
                    "FCheckLeadTime" => $param['check_lead_time'], //检验提前期
                    "FCheckLeadTimeType" => $param['check_lead_time_unit'], //检验提前期单位，（必填项）*
                    "FOrderIntervalTimeType" => $param['order_interval_unit'], //订货间隔期单位，（必填项）*
                    "FOrderIntervalTime" => $param['order_interval_time'], //订货间隔期
                    "FMaxPOQty" => 100000, //最大订货量
                    "FMinPOQty" => 0, //最小订货量
                    "FIncreaseQty" => 0, //最小包装量
                    "FEOQ" => 1, //固定/经济批量
                    "FVarLeadTimeLotSize" => 1, //变动提前期批量
                    "FPlanIntervalsDays" => 0, //批量拆分间隔天数
                    "FPlanBatchSplitQty" => 0, //拆分批量
                    "FRequestTimeZone" => 0, //需求时界
                    "FPlanTimeZone" => 0, //计划时界
                    "FCanLeadDays" => 0, //允许提前天数
                    "FIsMrpComReq" => $param['mrp_combine'] ? true : false, //MRP计算是否合并需求
                    "FLeadExtendDay" => 0, //提前宽限期
                    "FReserveType" => 1, //预留类型，（必填项）*
                    "FPlanSafeStockQty" => 1, //安全库存
                    "FAllowPartAhead" => false, //预计入库允许部分提前
                    "FCanDelayDays" => 999, //允许延后天数
                    "FDelayExtendDay" => 0, //延后宽限期
                    "FAllowPartDelay" => true, //预计入库允许部分延后
                    "FPlanOffsetTimeType" => $param['offset_time_unit'], //时间单位，（必填项）*
                    "FPlanOffsetTime" => $param['offset_time'], //偏置时间
                    "FSupplySourceId" => ["FNumber" => $param['supply_from']], //供应来源
                ],
                //生产
                "SubHeadEntity5" => [
                    "FProduceUnitId" => [
                        "FNumber" => $param['product_unit'],
                    ], //生产单位
                    "FFinishReceiptOverRate" => 0, //入库超收比例(%)
                    "FFinishReceiptShortRate" => 0, //入库欠收比例(%)
                    "FProduceBillType" => [
                        "FNumber" => $param['product_type'],
                    ], //生产类型
                    "FOrgTrustBillType" => [
                        "FNumber" => "SCDD06_SYS",
                    ], //组织间受托类型
                    "FIsSNCarryToParent" => false, //序列号携带到父项
                    "FIsProductLine" => false, //生产线生产
                    "FBOMUnitId" => [
                        "FNumber" => $param['basic_unit']
                    ], //子项单位
                    "FLOSSPERCENT" => 0, //变动损耗率(%)
                    "FConsumVolatility" => 0, //消耗波动(%)
                    "FIsMainPrd" => true, //可为主产品
                    "FIsCoby" => false, //可为联副产品
                    "FIsECN" => false, //启用ECN
                    "FIssueType" => $param['issue_qty'], //发料方式，（必填项）*
                    "FBKFLTime" => $param['reverse_time'], //倒冲时机（调拨倒冲时才有值）；3，入库倒冲；2，汇报倒冲
                    "FOverControlMode" => $param['over_give_control'], //超发控制方式，（必填项）*
                    "FMinIssueQty" => $param['min_issue_qty'], //最小发料批量
                    "FISMinIssueQty" => false, //领料考虑最小发料批量
                    "FIsKitting" => false, //是否关键件
                    "FIsCompleteSet" => false, //是否齐套件
                    "FStdLaborPrePareTime" => 0, //标准人员准备工时
                    "FStdLaborProcessTime" => 0, //标准人员实作工时
                    "FStdMachinePrepareTime" => 0, //标准机器准备工时
                    "FStdMachineProcessTime" => 0, //标准机器实作工时
                    "FMinIssueUnitId" => ["FNumber" => $param['min_give_unit']], //最小发料批量单位，（必填项）*
                    "FStandHourUnitId" => $param['standard_labor_unit'], //工时单位，（必填项）*
                    "FPerUnitStandHour" => (int)$param['standard_labor_hour'], //标准工时
                ],
                //委外
                "SubHeadEntity7" => [
                    "FSubconUnitId" => ["FNumber" => $param['outsource_unit']], //委外单位
                    "FSubconPriceUnitId" => ["FNumber" => $param['outsource_price_unit']], //委外计价单位
                    "FSubBillType" => [
                        "FNumber" => "WWDD01_SYS"
                    ] //委外类型
                ],
                //质量
                "SubHeadEntity6" => [
                    "FCheckIncoming" => false, //来料检验
                    "FCheckProduct" => false, //产品检验
                    "FCheckStock" => false, //库存检验
                    "FCheckReturn" => false, //退货检验
                    "FCheckDelivery" => false, //发货检验
                    "FEnableCyclistQCSTK" => false, //启用库存周期复检
                    "FStockCycle" => 0, //复检周期
                    "FEnableCyclistQCSTKEW" => false, //启用库存周期复检提醒
                    "FEWLeadDay" => false, //提醒提前期
                    "FCheckEntrusted" => false, //受托材料检验
                    "FCheckOther" => false, //其他检验
                ],
                //库存属性
                "FEntityInvPty" => [
                    [
                        "FEntryID" => null,
                        "FInvPtyId" => [
                            "FNumber" => "01"
                        ], //库存属性，（必填项）*
                        "FIsEnable" => $inventory_attr['warehouse']['enable'] ? true : false, //启用
                        "FIsAffectPrice" => $inventory_attr['warehouse']['affect_price'] ? true : false, //影响价格
                        "FIsAffectPlan" => $inventory_attr['warehouse']['affect_plan'] ? true : false, //影响计划
                        "FIsAffectCost" => $inventory_attr['warehouse']['affect_cost'] ? true : false, //影响成本
                    ],
                    [
                        "FEntryID" => null,
                        "FInvPtyId" => [
                            "FNumber" => "02"
                        ],
                        "FIsEnable" => $inventory_attr['warehouse_space']['enable'] ? true : false,
                        "FIsAffectPrice" => $inventory_attr['warehouse_space']['affect_price'] ? true : false,
                        "FIsAffectPlan" => $inventory_attr['warehouse_space']['affect_plan'] ? true : false,
                        "FIsAffectCost" => $inventory_attr['warehouse_space']['affect_cost'] ? true : false,
                    ],
                    [
                        "FEntryID" => null,
                        "FInvPtyId" => [
                            "FNumber" => "03"
                        ],
                        "FIsEnable" => $inventory_attr['BOM']['enable'] ? true : false,
                        "FIsAffectPrice" => $inventory_attr['BOM']['affect_price'] ? true : false,
                        "FIsAffectPlan" => $inventory_attr['BOM']['affect_plan'] ? true : false,
                        "FIsAffectCost" => $inventory_attr['BOM']['affect_cost'] ? true : false,
                    ],
                    [
                        "FEntryID" => null,
                        "FInvPtyId" => [
                            "FNumber" => "04"
                        ],
                        "FIsEnable" => $inventory_attr['batch_num']['enable'] ? true : false,
                        "FIsAffectPrice" => $inventory_attr['batch_num']['affect_price'] ? true : false,
                        "FIsAffectPlan" => $inventory_attr['batch_num']['affect_plan'] ? true : false,
                        "FIsAffectCost" => $inventory_attr['batch_num']['affect_cost'] ? true : false,
                    ],
                    [
                        "FEntryID" => null,
                        "FInvPtyId" => [
                            "FNumber" => "06"
                        ],
                        "FIsEnable" => $inventory_attr['trace_num']['enable'] ? true : false,
                        "FIsAffectPrice" => $inventory_attr['trace_num']['affect_price'] ? true : false,
                        "FIsAffectPlan" => $inventory_attr['trace_num']['affect_plan'] ? true : false,
                        "FIsAffectCost" => $inventory_attr['trace_num']['affect_cost'] ? true : false,
                    ]
                ]
            ]
        ];

        $result = KCloud::auto_save('BD_MATERIAL', $data);
        try {
            if (!empty($result['Result']['ResponseStatus']['SuccessEntitys'][0]['Number'])) {
                $number = $result['Result']['ResponseStatus']['SuccessEntitys'][0]['Number'];
                $data = ['Numbers' => $number];
                KCloud::auto_submit('BD_MATERIAL', $data);
            }
        } catch (KCloudException $e) {
            KCloud::auto_delete('BD_MATERIAL', $data);
            throw $e;
        }
    }

    /**
     * 审核物料
     * @param $material_code
     * @throws KCloudException
     */
    public static function auditMaterial($material_code)
    {
        $data = ['Numbers' => $material_code];
        KCloud::auto_audit('BD_MATERIAL', $data);
    }
}