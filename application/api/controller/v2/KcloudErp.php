<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2018/7/24 13:55
// +----------------------------------------------------------------------
// | TITLE: 金蝶ERP API物料、BOM、物料分组、BOM分组的数据处理
// +----------------------------------------------------------------------

namespace app\api\controller\v2;

use app\api\controller\Api;
use app\api\model\Material;
use app\common\library\KCloudData;
use app\common\library\KCloud;
use think\Session;

class KcloudErp
{
    /**
     * 获取物料列表<通过ERP接口把所有物料批量添加到数据库>
     */
    public function getList()
    {
        $model = new KCloudData();
        //查询总页数
        $dataArr["FormId"] = "BD_MATERIAL"; //业务对象表单Id（必录）
        //编码、名称、规格型号、型号编码，单位编码、单位名称
        $dataArr["FieldKeys"] = "FNumber,FName,FSPECIFICATION,FDESCRIPTION,F_PAEZ_TEXT,FERPCLSID,FBASEUNITID.FNumber";
        $res = Session::get('k3CloudLoginResult') === true ? 1 : KCloud::check_login();
        if($res == 1)
        {
            $cloudUrl = Session::get('cloudUrl');
            $cookie_jar = Session::get('cookieJar');
            $pageInfo = $model->commonQueryAll($dataArr,$cloudUrl,$cookie_jar);
            if($pageInfo)
            {
                $pageCount = $pageInfo['totalPages'];
                $page = 1;
                /*do{
                    $page++;
                    $data = $model->getK3Material($page);
                    $data = json_decode($data['data'],true);
                    $list = [];

                    foreach($data as $k=>$v)
                    {
                        $list[$k]['material_code'] = $v['FNumber'];
                        $list[$k]['mg_id'] = $v['FMaterialGroup'];//物料分组ID
                        $list[$k]['material_name'] = $v['FName'];
                        $list[$k]['specifications'] = $v['FSPECIFICATION'];//规格型号
                        $list[$k]['description'] = $v['FDESCRIPTION'];//描述
                        $list[$k]['specifications_code'] = $v['F_PAEZ_TEXT'];//型号编码
                        $list[$k]['material_attribute'] = $v['FERPCLSID'];//物料属性
                        $list[$k]['basic_unit'] = $v['FBASEUNITID.FNumber'];//基本单位
                        $list[$k]['createtime'] = time();
                    }
                    Material::addInfo($list);
                    unset($list);
                }while($page<=$pageCount);*/
                while($page<=$pageCount)
                {
                    $obj = $model->getK3Material($page);
                    $data = json_decode($obj['data'],true);
                    $list = [];
                    foreach($data as $k=>$v)
                    {
                        $list[$k]['material_code'] = $v['FNumber'];
                        $list[$k]['mg_id'] = $v['FMaterialGroup'];//物料分组ID
                        $list[$k]['mg_code'] = $v['FMaterialGroup.FNumber'];//物料分组编码
                        $list[$k]['material_name'] = $v['FName'];
                        $list[$k]['specifications'] = $v['FSPECIFICATION'];//规格型号
                        $list[$k]['description'] = $v['FDESCRIPTION'];//描述
                        $list[$k]['specifications_code'] = $v['F_PAEZ_TEXT'];//型号编码
                        $list[$k]['material_attribute'] = $v['FERPCLSID'];//物料属性
                        $list[$k]['basic_unit'] = $v['FBASEUNITID.FNumber'];//基本单位
                        $list[$k]['createtime'] = time();
                    }
                    Material::addInfo($list);
                    unset($list);
                    $page++;
                }
                return 'done';
            }
        }else
        {
            return 'failed';
        }
    }

    /**
     * 修改物料列表<有数据则修改，没有数据则新增>
     */
    public function updateMaterial()
    {
        ini_set("max_execution_time",600);
        $model = new KCloudData();
        //查询总页数
        $dataArr["FormId"] = "BD_MATERIAL"; //业务对象表单Id（必录）
        //编码、名称、规格型号、型号编码，单位编码、单位名称
        $dataArr["FieldKeys"] = "FNumber,FName,FSPECIFICATION,FDESCRIPTION,F_PAEZ_TEXT,FERPCLSID,FBASEUNITID.FNumber";
        $res = Session::get('k3CloudLoginResult') === true ? 1 : KCloud::check_login();
        if($res == 1)
        {
            $cloudUrl = Session::get('cloudUrl');
            $cookie_jar = Session::get('cookieJar');
            $pageInfo = $model->commonQueryAll($dataArr,$cloudUrl,$cookie_jar);
            if($pageInfo)
            {
                $pageCount = $pageInfo['totalPages'];
                $page = 1;
                while($page<=$pageCount)
                {
                    $data = $model->getK3Material($page);
                    $data = json_decode($data['data'],true);
                    Material::updateInfo($data);
                    $page++;
                }
                return 'done';
            }

        } else {
            return 'failed';
        }
    }

    /**
     * 获取物料分组
     */
    public function getMaterialGroup()
    {
        $model = new KCloudData();
        $dataArr["FormId"] = "SAL_MATERIALGROUP"; //业务对象表单Id（必录）
        //编码、名称、所属部门
        $dataArr["FieldKeys"] = "FParentId,FID,FNumber,FName"; //字段keys，字符串类型用逗号分隔，比如"key1,key2..."（必录）

        $data = $model->commonQuery($dataArr,$page=1,$keyword='',$FilterString = '',$pageSize='2000',$status = false);
        $data = json_decode($data['data'],true);
        $result = \app\api\model\Grouping::addInfo($data,2);

        return $result;
    }

    /**
     * BOM分组
     */
    public function getBomGroup()
    {
        $url = 'http://192.168.80.28:8018/WebService1.asmx/GetBaseItemData';
        $params = array(
            'nFItemClassID' => 1,
            'nUseOrgID' => 0,
            'nCreateOrgID' => 0,
        );
        $info = $this->curl_post($url,$params);
        $result = 0;
        if($info)
        {
            $result = \app\api\model\Grouping::addInfo($info['listdata'],1);
        }

        return $result;
    }

    /**
     * curl请求
     * @param $url
     * @param array $params post传输参数
     * @return bool|\mix|mixed|string
     */
    public function curl_post($url,$params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);
        $retinfo = curl_getinfo($ch);
        curl_close($ch);
        if($retinfo['http_code']==200){
            $xml = simplexml_load_string($ret);
            $data = json_decode($xml, true);
            return $data;
        }else{
            return false;
        }
    }

    public function disable()
    {
        $sale_org_id = 100; //使用组织
        //查询未同步的退款单

        //创建ERP退款单的数据
                $dataModel = [
                    "IsDeleteEntry" => True,
                    "IsVerifyBaseDataField" => false,
                    "IsEntryBatchFill" => True,
                    "Model" =>
                        [
                            "FID" => 0,
                            "FBillTypeID" => ["FNumber" => 'SKTKDLX01_SYS'], //单据类型,必填
                            "FDATE" => date('Y-m-d'),   //业务日期，必填
                            "FCONTACTUNITTYPE" => 'BD_Customer',//往来单位类型,必填
                            "FCONTACTUNIT" => ["FNumber" => 'ASS0355'], //往来单位，必填
                            "FSETTLERATE" => 1,//结算汇率
                            "FDOCUMENTSTATUS" => 'Z',   //单据状态
                            "FRECTUNITTYPE" => 'BD_Customer',  //收款单位类型，必填
                            "FRECTUNIT" => ["FNumber" => 'ASS0355'],//收款单位，必填
                            "FCURRENCYID" => ["FNumber" => 'PRE001'],
                            "FSETTLEORGID" => ["FNumber" => $sale_org_id],//结算组织:必填
                            "FSALEORGID" => ["FNumber" => $sale_org_id],//销售组织
                            "FSALEDEPTID" => ["FNumber" => "BJB01"], //销售部门
                            "FSALEGROUPID" => ["FNumber" => ''], //销售组
                            "FSALEERID" => ["FNumber" => ''], //销售员
                            "FEXCHANGERATE" => 1, //汇率
                            "FCancelStatus" => 'A', //作废状态，必填
                            "FPAYORGID" => ["FNumber" => 100], //付款组织，必填
                            "FISSAMEORG" => 1,
                            "FSETTLECUR" => ["FNumber" => 'PRE001'], //结算币别，必填
                            "FSOURCESYSTEM" => 0,//来源系统
                            "FMatchMethodID" => 0,//核销方式
                            "FREMARK" =>  '',
                            "F_PAEZ_Assistant" => ["FNumber" => '0102'], //收款退款类型，必填
                            "FBookingDate" => date('Y-m-d'),
                            "FREFUNDBILLENTRY" =>
                                [
                                    [
                                        "FSETTLETYPEID" => ["FNumber" => 'JSFS04_SYS'],//结算方式，必填

                                        "FPURPOSEID" => ["FNumber" => 'SFKYT01_SYS'], //原收款用途，必填
                                        "FREFUNDAMOUNTFOR" => 600, //应退金额
                                        "FREFUNDAMOUNTFOR_E" => 600, //退款金额
                                        "FHANDLINGCHARGEFOR" => 0, //手续费
                                        "FACCOUNTID" => ["FNumber" => '755918800610808'], //我方银行账号
                                        "FNOTE" => '测试退10', //备注，必填
                                        "FRecType" => 0, //收款类型，必填
                                        "FREFUNDAMOUNT_E" => 10, //退款金额本位币
                                        "FPOSTDATE" => date('Y-m-d'), //登账日期,必填
                                        "FISPOST" => 1, //是否登账
                                        "FSALEORDERNUMBER" => '',
                                        "FMATERIALID" => ["FNumber" => ''], //物料编码
                                        "FMATERIALSEQ" => 0,//订单行号
                                        "FORDERENTRYID" => 0,//销售订单明细内码
                                        "FRuZhangType" => 1, //入账类型,必填
                                        "FEBMSG" => '',
                                        "FPayType" => 'A', //支付类型，必填
                                        "FSwiftCode" => ''
                                    ]

                                ],
                            "FREFUNDBILLSRCENTRY" =>[
                                [
                                    "FSOURCETYPE" =>"AR_RECEIVEBILL",
                                    "FSRCBILLNO" => 'SKD18100001',
                                    "FAFTTAXTOTALAMOUNT" => 70,
                                    "FPLANREFUNDAMOUNT" => 70,
                                    "FREALREFUNDAMOUNT_S" => 600,
                                    "FSRCCURRENCYID" => ['FNumber' =>'PRE001'],
                                    "FSRCSETTLETYPEID" => ['FNumber'=>'JSFS04_SYS'],
                                ]
                            ]
                        ]

                ];
                var_dump(json_encode($dataModel));die;
                //同步数据到ERP
                $res = Session::get('k3CloudLoginResult') === true ? 1 : KCloud::check_login();
                if ($res == 1) {
                    //登陆成功
                    $data = array(
                        'AR_REFUNDBILL',//业务对象标识FormId
                        $dataModel   //具体Json字串
                    );
                    $post_content = KCloud::create_postdata($data);
                    $cloudUrl = Session::get('cloudUrl');
                    $cookie_jar = Session::get('cookieJar');
                    $result = KCloud::invoke_save($cloudUrl, $post_content, $cookie_jar);
                    var_dump($result);die;
                    $result_arr = json_decode($result,true);
                    echo '<pre>';print($result_arr);die;

                }

    }

    public function test()
    {
        $data = [
            'AR_RECEIVEBILL',
            [
                //'Ids'=>'',
                'Numbers' => ["SKD18100001"],
                //'RuleId' => '',
                //'TargetBillTypeId' => '',
                'TargetOrgId' => 100,
                'TargetFormId' => 'AR_REFUNDBILL',
                'IsEnableDefaultRule' => true,
                //'CustomParams' => [],

                'F_PAEZ_Assistant' => ['FNumber'=>'0102'],
                'FNOTE' => 'abc'



            ]
        ];
        $res = Session::get('k3CloudLoginResult') === true ? 1 : KCloud::check_login();
        if ($res == 1) {
            //登陆成功
            $post_content = KCloud::create_postdata($data);
            $cloudUrl = Session::get('cloudUrl');
            $cookie_jar = Session::get('cookieJar');
            $result = KCloud::invoke_push($cloudUrl, $post_content, $cookie_jar);
            var_dump($result);die;
            $result_arr = json_decode($result,true);
            echo '<pre>';print($result_arr);die;

        }

    }
}