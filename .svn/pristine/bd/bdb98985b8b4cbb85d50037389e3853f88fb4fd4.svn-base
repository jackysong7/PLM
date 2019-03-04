<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2018/7/24 17:12
// +----------------------------------------------------------------------
// | TITLE: 金蝶云ERP订单的增、改、查
// +----------------------------------------------------------------------

namespace app\common\model;

use think\Model;
use think\Db;
use think\Session;
use app\common\library\KCloud;

class KCloudSaleOrder Extends Model
{

    /**
     *
     * 保存金蝶云订单
     *
     * @author         weibx
     * @param          $table  string 表名（不含前缀）
     * @param          $isSale  boolean 是否是手工销售订单
     * @param          $isFixed  boolean 是否是修复订单
     * @param          $isBillNo  string 单据编号（处理单条订单）
     * @return         true/false
     */
    public function saveKCloudSaleOrder($table, $isSale = false, $isFixed = false, $isBillNo = '')
    {
        $model = 0;
        if($table == "fa_edb_k3_order"){
            $model = 1;  //E店宝
        }else if($table == "fa_cainiao_k3_order"){
            $model = 2;  //菜鸟
        }else if($table == "fa_sale_k3_order"){
            $model = 3;  //手工单
        }else if($table == "fa_kcloud_order"){
            $model = 4;  //七千猫国内商城
            $table_name = "fa_kcloud_order";
            $condition_table_name = "fa_kcloud_order_goods";
        }else if($table == "fa_overseas_kcloud_order"){
            $model = 5;  //七千猫海外商城
            $table_name = "fa_overseas_kcloud_order";
            $condition_table_name = "fa_overseas_kcloud_order_goods";
        }
        $shop_table = array("fa_kcloud_order","fa_overseas_kcloud_order");
        //查询数据
        if($isBillNo){
            KCloudLog::saveHandleLog($model,6,1); //保存执行日志

            if(in_array($table,$shop_table)){
                $k3_order_list = Db::table($table_name)->alias('k')->join($condition_table_name.' g','k.kcloud_order_sn = g.kcloud_order_sn','LEFT')->where('k.kcloud_order_sn', $isBillNo)->select();
            }else{
                $k3_order_list = Db::table($table)->where('bill_no', $isBillNo)->select();//物料编码不为空，为空则代表数据有问题，所以直接过滤不同步到金蝶云
            }
        }else{

            KCloudLog::saveHandleLog($model,3,1); //保存执行日志
            if(in_array($table,$shop_table)){
                if ($isFixed) {
                    $k3_order_list = Db::table($table_name)->alias('k')->join($condition_table_name.' g', 'k.kcloud_order_sn = g.kcloud_order_sn', 'LEFT')->where('k.status', 3)->select();
                }else{
                    $k3_order_list = Db::table($table_name)->alias('k')->join($condition_table_name.' g', 'k.kcloud_order_sn = g.kcloud_order_sn', 'LEFT')->where('k.status', 0)->whereOr('k.status', 2)->select();

                }
            }else {
                if ($isSale || $model == 2) {
                    //手工单和菜鸟
                    if ($isFixed) {
                        $where = "status = 3";  //异常数据
                    } else {
                        $where = "status in (0,2)";  //手工销售订单【未同步】、【同步失败】
                    }
                } else {
                    //EDB
                    $yesterday = date("Y-m-d", strtotime("-1 day")); //昨天
                    $yesterday_start = $yesterday . " 00:00:00";
                    $yesterday_end = $yesterday . " 23:59:59";

                    if ($isFixed) {
                        $where = "status = 3";  //异常数据
                    } else {
                        $where = "status in (0,2)";  //手工销售订单【未同步】、【同步失败】
                    }
                    $where .= " AND delivery_date>='" . $yesterday_start . "' AND delivery_date<='" . $yesterday_end . "'";//要货时间（发货时间）

                }
                $where .= " AND material_id != ''";    //物料编码不为空，为空则代表数据有问题，所以直接过滤不同步到金蝶云

                $k3_order_list = Db::table($table)->where($where)->select();
            }
        }

        if ($k3_order_list) {
            $res = array();
            foreach ($k3_order_list as $rk => $rv) {
                $res[$rv['bill_no']][] = $rv;
            }

            foreach ($res as $res_key => $res_value) {

                $FSaleOrderEntry_array = $dataArr = $modelArr = array();   //初始化请求数据

                foreach ($res_value as $key => $value) {
                    if ($key == 0) {
                        $modelArr["FBillNo"] = $isFixed ? $res_key."_".$value['id'] : $res_key;  //唯一的,单据编号,必填
                        //订单基本信息
                        $modelArr["FID"] = "0";  //ID，0为新增，有值为修改
                        $modelArr["FBillTypeID"] = array("FNumber" => $value["bill_type_id"]); //单据类型(必填项)
                        $modelArr["FDate"] = $isSale ? $value['bill_date'] : date("Y-m-d",strtotime($value["delivery_date"])); //日期(必填项)
                        $modelArr["FSaleOrgId"] = array("FNumber" => $value["sale_org_id"]); //销售组织(必填项)
                        $modelArr["FCustId"] = array("FNumber" => $value["erp_shop_code"]); //客户(必填项)
                        $modelArr["FReceiveId"] = array("FNumber"=>$value["receive_id"]); //收货方,客户联动
                        $modelArr["FSaleDeptId"] = array("FNumber" => $value["sale_dept_id"]); //销售部门
                        $modelArr["FSaleGroupId"] = array("FNumber" => $value["sale_group_id"]); //销售组，null
                        $modelArr["FSalerId"] = array("FNumber" => $value["sale_id"]); //销售员(必填项)
                        $modelArr["FSettleId"] = array("FNumber"=>$value["settle_id"]); //结算方,客户联动
                        $modelArr["FChargeId"] = array("FNumber"=>$value["charge_id"]); //付款方,客户联动

                        $modelArr["FBusinessType"] = "NORMAL"; //业务类型,普通销售(默认)，固定默认值
                        $modelArr["F_PAEZ_ASSISTANT"] = array("FNumber"=>$value['commerce_category_code']); //自营电商分类

                        $modelArr["F_PAEZ_BASE1"] = array("FStaffNumber"=>$value["auditor_id"]); //审核人

                        //销售订单财务信息
                        $modelArr["FSaleOrderFinance"] = array(
                            "FSettleCurrId" => array("FNumber" => $value["settle_curr_id"]), //结算币别
                            "FRecConditionId" => array("FNumber" => $value["rec_condition_id"]), //收款条件
                            "FIsPriceExcludeTax" => "true", //价外税，固定默认值
                            "FSettleModeId" => array("FNumber" => $value["settle_mode_id"]), //结算方式
                            "FIsIncludedTax" => "true", //是否含税，固定默认值
                            "FExchangeTypeId" => array("FNumber" => $value["exchange_type_id"]), //汇率类型，必填
                            "FExchangeRate" => $value["exchange_rate"], //汇率,必填,默认1

                            "FLocalCurrId" => array("FNumber" => $value["local_curr_id"]),//本位币
                        );
                    }

                    //销售订单明细
                    $FSaleOrderEntry = Array(
                        "FEntryID" => "0", //0为新增
                        //"FRowType" => "Standard", //产品类型
                        "FMapId" => Array("FNumber" => $isSale ? $value['customer_material_code'] : ""), //客户物料编码

                        "FMaterialId" => Array("FNumber" => $value["material_id"]), //物料编码(必填项)
                        "FUnitID" => Array("FNumber" => $value["unit_id"]), //销售单位(必填项)
                        "FQty" => $value["qty"], //销售数量
                        //"FOldQty" => "900", //原数量

                        "FIsFree" => $isSale ? $value['is_gift'] : (intval($value["all_amount"]) == 0 ? "true" : "false"), //是否赠品
                        "FEntryTaxRate" => $model == 5 ? "0" : "16", //税率%，固定默认值,海外订单税率为0
                        "FExpPeriod" => "365", //保质期，固定默认值
                        "FExpUnit" => "D", //保质期单位，固定默认值
                        "FDeliveryDate" => $value["delivery_date"], //要货日期(必填项),即发货日期
                        "F_PAEZ_BASE" => Array("FNumber" => $value["erp_storage_code"]), //发货仓库
                        "FStockOrgId" => Array("FNumber" => $value["stock_org_id"]), //库存组织
                        "FSettleOrgId" => Array("FNumber" => $value["settle_org_id"]), //结算组织(必填项)
                        //"FSettleOrgIds" => Array("FNumber" => $value["settle_org_id"]), //结算组织(必填项)
                        "FSupplyOrgId" => Array("FNumber" => $value["supply_org_id"]), //供应组织
                        "FOwnerTypeId" => "BD_OwnerOrg", //货主类型,必填，固定默认值
                        "FOwnerId" => Array("FNumber" => $value["owner_id"]), //货主,必填
                        "FReserveType" => "1", //预留类型(必填项)，固定默认值
                        //"FPriceBaseQty" => "900", //计价基本数量
                        "FStockUnitID" => Array("FNumber" => $value["unit_id"]), //库存单位,等同销售单位
                        //"FStockQty" => "900", //库存数量
                        //"FStockBaseQty" => "900", //库存基本数量
                        "FOUTLMTUNIT" => "SAL", //超发控制单位类型(必填项)，固定默认值
                        "FOutLmtUnitID" => Array("FNumber" => "Pcs"), //超发控制单位，固定默认值
                        //交货明细
                        "FOrderEntryPlan" => Array(
                            Array(
                                "FDetailID" => "0", //0为新增
                                "FPlanDate" => date("Y-m-d",strtotime($value["delivery_date"])), //要货日期
//                                    "FPlanDeliveryDate" => $value["delivery_date"], //计划发货日期
                                "FPlanQty" => $value["qty"], //数量
                                "FStockId" => array("FNumber" => $value["erp_storage_code"]) //仓库
                            )
                        ),
                        //税务明细
                        "FTaxDetailSubEntity" => Array(
                            Array(
                                "FDetailID" => "0", //0为新增
                                "FTaxRate" => $model == 5 ? "0" : "16", //税率%，固定默认值
                            )
                        ),
                        //"FPrice" => $value["unit_price"]/1.17, //单价 = 含税价格/1.17，保留6位小数
                        "FAllAmount" => $value["all_amount"], //价税合计
                        "FTaxPrice" => empty($value['qty']) ? 0 : $value["all_amount"]/abs($value["qty"]), //$value["unit_price"],//含税单价,真实价格
//                            "FLimitDownPrice" => "0.1", //最低限价
//                            "FSysPrice" => "0.1", //系统定价
                        "F_PAEZ_Text" => isset($value["jd_client_id"])?$value["jd_client_id"]:"",  //京东客户id
                        "F_PAEZ_Text1" => isset($value["jd_upc_code"])?$value["jd_upc_code"]:"",  //JD UPC编码
                        "F_PAEZ_Text2" => isset($value["jd_sku"])?$value["jd_sku"]:"",  //JD商品编码
                        "F_PAEZ_Text3" => isset($value["jd_order_id"])?$value["jd_order_id"]:"",  //京东订单号
                    );
                    $FSaleOrderEntry = array_filter($FSaleOrderEntry);//除去数组中的空字符元素
                    array_push($FSaleOrderEntry_array, $FSaleOrderEntry);
                }

                $modelArr["FSaleOrderEntry"] = $FSaleOrderEntry_array;
                $modelArr = array_filter($modelArr); //除去数组中的空字符元素
                $dataArr["Model"] = $modelArr;
                $dataJson = json_encode($dataArr);

                $this->ApiAddCloudOrder($dataJson,$table,$res_key);
            }

            if($isBillNo){
                KCloudLog::saveHandleLog($model,6,2); //保存执行日志
            }else{
                KCloudLog::saveHandleLog($model,3,2); //保存执行日志
            }
            echo "同步完成......";
        }else{
            echo "没有发现需要同步的订单......";
        }
    }

    /**
     *
     * 增加金蝶云订单ERP
     *
     *
     * @author         ken
     * @param          array  $array
     * @return         true/false
     */
    public static function ApiAddCloudOrder($data_model,$table,$bill_no)
    {
//        echo $data_model;exit;
        $res = Session::get('k3CloudLoginResult') === true ? 1 : KCloud::check_login();
        echo "\n bill_no：".$bill_no;
        if ($res == 1){
            //登陆成功
            $data = array(
                'SAL_SaleOrder',//业务对象标识FormId
                $data_model   //具体Json字串
            );
            $post_content = KCloud::create_postdata($data);

            $cloudUrl = Session::get('cloudUrl');
            $cookie_jar = Session::get('cookieJar');
            $result = KCloud::invoke_save($cloudUrl, $post_content, $cookie_jar);

            $result_arr = json_decode($result,true);
            print_r($result_arr);

            $shop_table = array("fa_kcloud_order","fa_overseas_kcloud_order");
            if ($result_arr['Result']['Id']){
                //同步成功
                if(in_array($table,$shop_table)){
                    Db::table($table)->where('bill_no',$bill_no)->update(["status"=>1,"sync_date"=>date("Y-m-d H:i:s")]);
                }else{
                    Db::table($table)->where('bill_no',$bill_no)->where('material_id','neq','')->update(["status"=>1,"sync_date"=>date("Y-m-d H:i:s")]);
                }


                echo "\n单据编号：".$bill_no."，同步金蝶云完成\n";
            }else{
                //同步失败
                if(in_array($table,$shop_table)){
                    Db::table($table)->where('bill_no',$bill_no)->update(["status"=>2,"sync_date"=>date("Y-m-d H:i:s")]);
                }else{
                    Db::table($table)->where('bill_no',$bill_no)->where('material_id','neq','')->update(["status"=>2,"sync_date"=>date("Y-m-d H:i:s")]);
                }

                echo "\n单据编号：".$bill_no."，同步金蝶云失败\n";
//                if(!$result){
                $data_model = json_encode(array("Number"=>$bill_no));
                $data = array(
                    'SAL_SaleOrder',//业务对象标识FormId
                    $data_model   //具体Json字串
                );
                $post_content = KCloud::create_postdata($data);
                $result_view = KCloud::invoke_view($res["cloudUrl"],$post_content,$res["cookie_jar"]);
                $result_arr_view = json_decode($result_view,true);
                if ($result_arr_view['Result']['Id']){
                    //同步成功
                    if(in_array($table,$shop_table)){
                        Db::table($table)->where('bill_no',$bill_no)->update(["status"=>1,"sync_date"=>date("Y-m-d H:i:s")]);
                    }else{
                        Db::table($table)->where('bill_no',$bill_no)->where('material_id','neq','')->update(["status"=>1,"sync_date"=>date("Y-m-d H:i:s")]);
                    }

                    echo "\n异常数据单据编号：".$bill_no."，同步金蝶云完成\n";
                }
                unset($result_view);
                unset($result_arr_view);
//                }
            }
            //$unusual_update_sql = "bill_no='".$bill_no."' AND material_id = ''";
            //异常数据
            if(!in_array($table,$shop_table)){
                Db::table($table)->where('bill_no',$bill_no)->where('material_id','eq','')->update(["status"=>3,"sync_date"=>date("Y-m-d H:i:s")]);
            }
            //日志
            $logParm = array(
                'table_name'=>$table,
                'data_model'=>$data_model,
                'sale_id'=>$result_arr['Result']['Id'],
                'bill_no'=>$bill_no,
                'is_success'=>$result_arr['Result']['ResponseStatus']['IsSuccess'],
                'res_msg'=>$result,
                'add_time'=>date("Y-m-d H:i:s"),
            );
            Db::table('fa_save_cloud_sale_log')->insert($logParm);

//            echo '<pre>';print_r('销售单请求数据：');echo '</pre>';
//            echo '<pre>';print_r(json_decode($post_content));echo '</pre>';
            echo '<pre>';print_r('保存返回结果：');echo '</pre>';
            echo '<pre>';print_r($result_arr);echo '</pre>';

            unset($data_model);
            unset($data);
            unset($post_content);
            unset($result);
            unset($result_arr);
            unset($logParm);
        }
    }


    /**
     *
     * 查询金蝶云销售订单记录
     *
     *
     * @author         weibx
     * @param          string
     * @return         true/false
     */
    public function viewSaleOrder($bill_no)
    {
        $result = array('code' => 200, 'server' => 10, 'data' => array());

        $res = Session::get('k3CloudLoginResult') === true ? 1 : KCloud::check_login();

        if ($res == 1){
            //登陆成功
            $data_model = '{"CreateOrgId":"0","Number":"'.$bill_no.'","Id":""}';
            $data = array(
                'SAL_SaleOrder',//业务对象标识FormId
                $data_model   //具体Json字串
            );
            $post_content = KCloud::create_postdata($data);

            $cloudUrl = Session::get('cloudUrl');
            $cookie_jar = Session::get('cookieJar');
            $result['data'] = KCloud::invoke_view($cloudUrl,$post_content,$cookie_jar);

            $info = json_decode($result['data'],true);
            if ($info['Result']['ResponseStatus'] != null){
                $result['code'] = 500; //获取数据失败
                $result['is_record'] = false;//无记录
            }else{
                $result['is_record'] = true;//有记录
            }

//            echo '<pre>';print_r('销售单请求数据：');echo '</pre>';
//            echo '<pre>';print_r(json_decode($post_content));echo '</pre>';
//            echo '<pre>';print_r('查询返回结果：');echo '</pre>';
//            echo '<pre>';print_r(json_decode($result['data'],true));echo '</pre>';
        }else{
            $result['code'] = 500;
        }

        return $result;
    }
}