<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2018/7/24 11:48
// +----------------------------------------------------------------------
// | TITLE: 金蝶ERP数据处理
// +----------------------------------------------------------------------

namespace app\common\library;


use think\Session;

class KCloudData
{
    /*
    * 获取金蝶云【单据类型】
    * */
    public function getK3BillType($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BOS_BillType"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNUMBER,FNAME"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        $FilterString = "FBILLFORMID='SAL_SaleOrder'";
        return $this->commonQuery($dataArr,$page,$keyword,$FilterString);
    }

    /*
     * 获取金蝶云【基础管理】【组织管理】【组织机构】  =》 销售组织、库存组织、供应组织、货主、结算组织
     * */
    public function getK3OrgOrganizations($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "ORG_Organizations"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNUMBER,FNAME"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【销售员】
     * */
    public function getK3BDSaler($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_Saler"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNUMBER,FNAME"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【销售组】
     * */
    public function getK3BDSaleGroup($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_SaleGroup"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNUMBER,FNAME"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【自营电商分类】 =》 辅助资料
     * */
    public function getK3BOS_ASSISTANTDATA_SELECT($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BOS_ASSISTANTDATA_SELECT"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FDataValue"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        $FilterString = "FID.FNumber='ZYDS'"; //自营电商分类
        return $this->commonQuery($dataArr,$page,$keyword,$FilterString);
    }

    /*
    * 获取金蝶云【客户物料对应表】
    * */
    public function getSAL_CustMatMapping($page = 0, $FBillNo = '', $FCustMatNo = array(), $keyword = '')
    {
        $dataArr["FormId"] = "SAL_CustMatMapping"; //业务对象表单Id（必录）
        //客户物料编码、客户物料名称、物料编码、物料名称、辅助属性、规格型号、启用、默认携带
        $dataArr["FieldKeys"] = "FCustMatNo,FCustMatName,FMaterialId.FNumber,FMaterialName,FAuxpropId,FUOM,FEffective,FDefCarry"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        $FilterString = empty($FBillNo) ? "" : "FBillNo='".$FBillNo."'"; //单据编码
        $custArr = array();
        if (!empty($FCustMatNo)) {
            foreach($FCustMatNo as $val)
            {
                array_push($custArr, "FCustMatNo='".$val."'");
            }
        }
        $custStr = implode(" OR ", $custArr);
        $FilterString .= empty($FCustMatNo) ? "" : " AND ($custStr)"; //JD商品编码
        return $this->commonQuery($dataArr,$page,$keyword,$FilterString,0,false);
    }

    /*
     * 获取金蝶云【基础管理】【基础资料】【客户】（店铺）   =》 收货方、结算方、付款方
     * */
    public function getK3Customer($page = 0, $keyword = '',$FName = '')
    {
        $dataArr["FormId"] = "BD_Customer"; //业务对象表单Id（必录）
        //编码、名称、价控类别编码、价控类别名称、销售部门编码、销售部门名称、销售员编码、销售员名称、结算币别编码、结算币别名称、收款条件编码、收款条件名称、结算方式编码、结算方式名称、税率编码、税率名称、税率%、销售组编码、销售组名称
        $dataArr["FieldKeys"] = "FNumber,FName,FCUSTTYPEID.FNumber,FCUSTTYPEID.FDataValue,FSALDEPTID.FNumber,FSALDEPTID.FName,FSELLER.FNumber,FSELLER.FName,FTRADINGCURRID.FNumber,FTRADINGCURRID.FName,FRECCONDITIONID.FNumber,FRECCONDITIONID.FName,FSETTLETYPEID.FNumber,FSETTLETYPEID.FName,FTaxRate.FNumber,FTaxRate.FName,FTaxRate.FTaxRate,FSALGROUPID.FNumber,FSALGROUPID.FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        if($FName){
            $sale_org_id = $config = \think\Config::get('kcloud_sale_order.sale_org_id'); //使用组织
            $FilterString = "FName='".$FName."' AND FUSEORGID.FNumber = $sale_org_id";

            return $this->commonQuery($dataArr,$page,$keyword,$FilterString);
        }else{
            return $this->commonQuery($dataArr,$page,$keyword);
        }
    }

    /*
     * 获取金蝶云【供应链】【库存管理】【仓库】
     * */
    public function getK3Stock($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_STOCK"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
    * 获取金蝶云【基础管理】【基础资料】【部门】 =》 销售部门
    * */
    public function getK3Department($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_Department"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【基础管理】【基础资料】【业务员】  =>  该接口应该没用，看下方的业务员
     * */
    public function getK3Operator($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_OPERATOR"; //业务对象表单Id（必录）
        //职员、部门、任职岗位
        $dataArr["FieldKeys"] = "FSTAFFID,Fdept,FPosition"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【安全管理】【基础资料】【业务员】
     * */
    public function getK3OperatorView($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BOS_OPERATORVIEW"; //业务对象表单Id（必录）
        //编码、名称、职员内码、部门
        $dataArr["FieldKeys"] = "FNumber,FName,FStaffId,FDeptId"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【财务会计】【总账】【基础资料】【结算方式】
     * */
    public function getK3Settletype($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_SETTLETYPE"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【基础管理】【基础资料】【收款条件】
     * */
    public function getK3BDRecCondition($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_RecCondition"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【基础管理】【基础资料】【物料】
     * */
    public function getK3Material($page = 1, $keyword = '',$pageSize='2000',$FNumber = '')
    {
        //print_r($pageSize);
        $dataArr["FormId"] = "BD_MATERIAL"; //业务对象表单Id（必录）
        //编码、名称、规格型号、型号编码，单位编码、单位名称
        //$dataArr["FieldKeys"] = "FMaterialGroup,FMaterialGroup.FNumber,FNumber,FName,FSPECIFICATION,FDESCRIPTION,F_PAEZ_TEXT,FERPCLSID,FBASEUNITID.FNumber";
        $dataArr["FieldKeys"] = "FMaterialGroup,FMaterialGroup.FNumber,FNumber,FName,FSPECIFICATION,FDESCRIPTION,F_PAEZ_TEXT,FERPCLSID,FBASEUNITID.FNumber,FOldNumber,FOnlineLife,FExpPeriod,FBatchRuleID.FNumber,F_PAEZ_Text1,FExpUnit,FSuite,F_PAEZ_Assistant1.FNumber,FIsPurchase,FIsInventory,FIsSubContract,FIsSale,FIsProduce,FIsAsset,FTaxRateId.FNumber,FCategoryID.FNumber,FTaxType.FNumber,FStoreUnitID.FNumber,FAuxUnitID.FNumber,FUnitConvertDir,FIsBatchManage,FIsExpParToFlot,FIsKFPeriod,FSaleUnitId.FNumber,FSalePriceUnitId.FNumber,FIsReturn,FPurchaseUnitId.FNumber,FPurchasePriceUnitId.FNumber,FIsQuota,FQuotaType,FMinSplitQty,FIsReturnMaterial,FSubconUnitId.FNumber,FSubconPriceUnitId.FNumber,FProduceUnitId.FNumber,FProduceBillType.FNumber,FStandHourUnitId,FPerUnitStandHour,FIssueType,FBKFLTime,FOverControlMode,FMinIssueQty,FMinIssueUnitId.FNumber,FPlanningStrategy,FMfgPolicyId.FNumber,FOrderPolicy,FPlanWorkshop.FNumber,FFixLeadTime,FFixLeadTimeType,FVarLeadTime,FVarLeadTimeType,FCheckLeadTime,FCheckLeadTimeType,FOrderIntervalTimeType,FOrderIntervalTime,FIsMrpComReq,FPlanOffsetTimeType,FPlanOffsetTime,FSupplySourceId"; //FInvPtyId.FName,FIsEnable,FIsAffectPrice,FIsAffectPlan,FIsAffectCost
        //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）

        $sale_org_id = 100; //使用组织
        //$FilterString = empty($FNumber) ? '' : "FNumber='".$FNumber."' AND FUSEORGID.FNumber = $sale_org_id";
        $FilterString = "FUSEORGID.FNumber =".$sale_org_id;
        return $this->commonQuery($dataArr,$page,$keyword,$FilterString,$pageSize);

    }

    /*
     * 获取金蝶云【客户关系管理】【销售过程管理】【销售方法】
     * */
    public function getK3SaleMethod($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "CRM_SaleMethod"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【基础管理】【基础资料】【税种】
     * */
    public function getK3Taxtype($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_TAXTYPE"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【基础管理】【基础资料】【税率】
     * */
    public function getK3BDTaxRate($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_TaxRate"; //业务对象表单Id（必录）
        //编码、名称、税率%
        $dataArr["FieldKeys"] = "FNumber,FName,FTaxRate"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【基础管理】【基础资料】【计量单位】 =》 销售单位
     * */
    public function getK3BDUnit($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_UNIT"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【财务会计】【总账】【币别】 =》 结算币别、本位币
     * */
    public function getK3Currency($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_Currency"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【财务会计】【总账】【汇率类型】
     * */
    public function getK3BDRateType($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "BD_RateType"; //业务对象表单Id（必录）
        //编码、名称
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【财务会计】【总账】【汇率】
     * */
    public function getK3BDRate($page = 0, $keyword = '', $FCyForID, $FCyToID)
    {
        $dataArr["FormId"] = "BD_Rate"; //业务对象表单Id（必录）
        //直接汇率、间接汇率、汇率类型、原币、目标币
        $dataArr["FieldKeys"] = "FEXCHANGERATE,FREVERSEEXRATE,FRATETYPEID.FName,FCYFORID.FName,FCYTOID.FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        $FilterString = "FRATETYPEID.FNumber='HLTX01_SYS' AND FCYFORID.FName='".$FCyForID."' AND FCYTOID.FName='".$FCyToID."'";
        return $this->commonQuery($dataArr,$page,$keyword,$FilterString);
    }

    /*
     * 获取金蝶云【基础管理】【基础资料】【岗位信息】
     * */
    public function getK3OrgHrpost($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "HR_ORG_HRPOST"; //业务对象表单Id（必录）
        //编码、名称、所属部门
        $dataArr["FieldKeys"] = "FNumber,FName,FDept"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 获取金蝶云【业务类型】
     * */
    public function getK3ESSBusinessType($page = 0, $keyword = '')
    {
        $dataArr["FormId"] = "ESS_BusinessType"; //业务对象表单Id（必录）
        //编码、名称、所属部门
        $dataArr["FieldKeys"] = "FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）
        return $this->commonQuery($dataArr,$page,$keyword);
    }

    /*
     * 查询
     * */
    public function commonQuery($dataParm,$page=1,$keyword,$FilterString = '',$pageSize='2000',$status = true)
    {
        $result = array('code'=>200,'server'=>10, 'data'=>array());
        $res = Session::get('k3CloudLoginResult') === true ? 1 : KCloud::check_login();
        //$res = KCloud::check_login();
        if ($res == 1) {
            //登陆成功
            //echo "登陆成功";
            $dataArr = $dataParm;
            $FieldKeys = explode(",",$dataParm["FieldKeys"]);
            if ($keyword){
                $fk_arr = array();
                foreach ($FieldKeys as $field){
                    $fk = "$field LIKE '%".$keyword."%'";
                    array_push($fk_arr,$fk);
                }
                $dataArr["FilterString"] = $FilterString=='' ? implode(" OR ",$fk_arr) : $FilterString." AND (".implode(" OR ",$fk_arr).")"; //过滤（非必录）
            }else{
                $dataArr["FilterString"] = $FilterString=='' ? "" : $FilterString; //过滤（非必录）
            }
            if($status){
                $dataArr['FilterString'] = $dataArr["FilterString"]=='' ? "FDOCUMENTSTATUS='C'" : $dataArr['FilterString']." AND FDOCUMENTSTATUS='C'"; //已审核
            }
            $dataArr["OrderString"] = ""; //排序字段（非必录）
            $dataArr["TopRowCount"] = "0"; //总行数（非必录）
            $dataArr["Limit"] = $page > 0 ? $pageSize : "0";//最大行数，不能超过2000（非必录）
            $dataArr["StartRow"] = $page > 0 ? ($page - 1)*$dataArr["Limit"] : "0"; //开始行（非必录）

            $dataJson = json_encode($dataArr);
            $data = array(
                $dataJson   //具体Json字串
            );

            $post_content = KCloud::create_postdata($data);

            $cloudUrl = Session::get('cloudUrl');
            $cookie_jar = Session::get('cookieJar');
            $info = KCloud::invoke_query($cloudUrl, $post_content, $cookie_jar);
            if($info){
                $infoArr = json_decode($info,true);
                if(!empty($infoArr)){
                    foreach ($infoArr as $k=>$item){
                        foreach ($item as $key => $value){
                            $infoArr[$k][trim($FieldKeys[$key])] = $value;
                            unset($infoArr[$k][$key]);
                        }
                    }
                    $info = json_encode($infoArr);
                }
            }
            $result['data'] = $info;
           // $result['pageInfo'] = $this->commonQueryAll($dataParm,$cloudUrl,$cookie_jar);
//            echo '<pre>';print_r('请求数据：');echo '</pre>';
//            echo '<pre>';print_r(json_decode($post_content));echo '</pre>';
//            echo '<pre>';print_r('查询返回结果：');echo '</pre>';
//            echo '<pre>';print_r(json_decode($info));echo '</pre>';
        }else{
            $result['code'] = 500; //登陆验证失败
        }

        return $result;
    }

    public function commonQueryAll($dataParm,$cloudUrl, $cookie_jar)
    {
        $dataArr = $dataParm;
        $dataArr["FilterString"] = "FDOCUMENTSTATUS='C' AND FUSEORGID.FNumber =100"; // 过滤（非必录）已审核且使用组织为罗马仕
        $dataArr["OrderString"] = ""; //排序字段（非必录）
        $dataArr["TopRowCount"] = "0"; //总行数（非必录）
        $dataArr["Limit"] = "0";//最大行数，不能超过2000（非必录）
        $dataArr["StartRow"] = "0"; //开始行（非必录）

        $dataJson = json_encode($dataArr);
        $data = array(
            $dataJson   //具体Json字串
        );
        $post_content = KCloud::create_postdata($data);
        $info = KCloud::invoke_query($cloudUrl, $post_content, $cookie_jar);
        $infoArr = json_decode($info,true);

        $pageInfo["totalNumber"] = count($infoArr);
        $pageInfo["totalPages"] = ceil($pageInfo["totalNumber"]/2000);
        return $pageInfo;
    }

    /**
     * 获取物料分组
     */
    public function getK3MaterialGroup($page = 1, $keyword = '')
    {
        $dataArr["FormId"] = "SAL_MATERIALGROUP"; //业务对象表单Id（必录）
        //编码、名称、所属部门
        $dataArr["FieldKeys"] = "FParentId,FID,FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）

        /*$sale_org_id = $config = \think\Config::get('kcloud_sale_order.sale_org_id'); //使用组织
        $FilterString = empty($FNumber) ? '' : "FNumber='".$FNumber."' AND FUSEORGID.FNumber = $sale_org_id";*/
        return $this->commonQuery($dataArr,$page,$keyword,$FilterString = '',$pageSize='2000',$status = false);
    }
}