<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2018/7/10 15:48
// +----------------------------------------------------------------------
// | TITLE: 项目文档各api
// +----------------------------------------------------------------------

namespace app\api\controller\v2;

use app\api\controller\Api;
use app\api\model\BomMaterial;
use app\api\model\DocTemplate;
use app\api\model\Document;
use app\api\model\Material;
use app\api\model\ProjectBom;
use app\api\model\Project;
use app\api\model\Relation;
use app\api\model\Resource;
use app\api\model\UitableviewDefault;
use app\api\model\UitableviewValue;
use app\common\library\Auth;
use app\common\library\GearmanAsync;
use think\Exception;
use think\Validate;
use think\Db;

class Doc extends Api
{
    const DOC_TYPE = 3;//自定义文档类型
    const BOM_TYPE = 4;//BOM类型
    const UITABLEVIEW_TYPE = 6;//开发立项表类型

    /*
     * 提交文档审核
     *
     */
    public function submit()
    {
        $jsonData = $this->request->param();
        $params = json_decode($jsonData['data'],true);
        $rule = [
            'doc_id' => 'require|integer',
            'tpl_type' => 'require|integer',
        ];
        $msg = [
            'doc_id.require' => '文档ID不能为空！',
            'tpl_type.require' => '文档模板类型不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($params);

        if (!$result)
        {
            $this->returnmsg(401,$this->validate->getError(),"");
        }
        $projectInfo='';
        if($params['tpl_type'] == 1)//文档模板类型:1 BOM表;2 开发立项表;3 自定义文档
        {
            $result = ProjectBom::edit($params);
            //查询项目id
            $projectId = ProjectBom::tplInfo(['bom_id'=>$params['doc_id']],'project_id');
            $projectInfo = Project::getInfoByProjectId($projectId['project_id'],'project_name');

        }elseif($params['tpl_type'] == 2){
            $result = UitableviewDefault::edit($params);
            //查询项目id
            $projectId = UitableviewDefault::docIdData(['ud_id'=>$params['doc_id']],'project_id');
            $projectInfo = Project::getInfoByProjectId($projectId['project_id'],'project_name');

        }elseif($params['tpl_type'] == 3){
            $result = Document::edit($params);
            //查询项目id
            $projectId = Document::tplInfo(['pd_id'=>$params['doc_id']],'project_id');
            $projectInfo = Project::getInfoByProjectId($projectId['project_id'],'project_name');
        }

        if($result)
        {
            \app\api\model\ProjectMsg::documentAudit($params['doc_id'], $params['tpl_type']);

            //短信通知审核人员审核
            $auditInfo = auditPerson($params);
            $sendModel = new GearmanAsync();
            $creatorId = Auth::instance()->getUser()['admin_id'];
            $nickname = Relation::getNickName($creatorId);
            $tpl_param = '';$users='';
            foreach($auditInfo['auditor'] as $v)
            {
                $users[] = userData($v['admin_id']);
                $tpl_param[] = [
                    'someone'=>$nickname,
                    'project' =>$projectInfo['project_name']
                ];

            }
            $user = array_column($users,'telephone');
            $job = [
                'batch_sms' => 1,
                'mobile' => $user,
                'tpl_code' => 'SMS_142015296',//通知审核
                'tpl_param' => $tpl_param,
            ];
            $sendModel->sendSms($job);

            //邮件发送
            $email = array_column($users,'email');
            $emailJob = [
                'email' => $email,
                'title' => 'PLM通知审核',
                'content' => $nickname.'处理了在项目'.$projectInfo['project_name'].'的工作，请审核相关内容',
            ];
            $sendModel->sendEmail($emailJob);
            $this->returnmsg(200,'success！');
        }
        else
        {
            $this->returnmsg(402,'参数错误！');
        }

    }


    /*
     * 审核文档
     */
    public function audit()
    {
        $jsonData = $this->request->param();
        $params = json_decode($jsonData['data'],true);
        $rule = [
            'doc_id' => 'require|integer|>:0',
            'tpl_type' => 'require|integer|>:0',
            'audit_status' => 'require|integer',
        ];
        $msg = [
            'doc_id.require' => '文档ID不能为空！',
            'tpl_type.require' => '文档模板类型不能为空！',
            'audit_status.require' => '审核状态不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($params);
        if (!$result)
        {
            $this->returnmsg(401,$this->validate->getError(),"");
        }

        $data['doc_id'] = isset($params['doc_id']) ? intval($params['doc_id']) : '';//文档ID

        $data['audit_status'] = isset($params['audit_status']) ? $params['audit_status'] : '';//审核状态
        $data['audit_note'] = isset($params['audit_note']) ? $params['audit_note'] : '';//备注信息
        $data['admin_id'] = Auth::instance()->getUser()['admin_id'];
        $data['tpl_type'] = isset($params['tpl_type']) ? $params['tpl_type'] : '';//备注信息
        // 审核状态为被驳回时，备注信息必填
        if($params['audit_status'] == -1 && !$params['audit_note'])
        {
            $this->returnmsg(401,'审核状态为被驳回时，备注信息不能为空');
        }
        $result = Relation::editInfo($data);
        $error = '';
        //如果全部审核通过，修改各类型的审核状态为已审核
        if(($result == 1 && $params['tpl_type'] == 1) ||($result == 3 && $params['tpl_type'] == 1))
        {
            //修改BOM文档最终审核状态
            $result = ProjectBom::audit($data, $error);
        } elseif(($result == 1 && $params['tpl_type'] == 2) ||($result == 3 && $params['tpl_type'] == 2)) {
            //修改开发立项表最终审核状态
            $result = UitableviewDefault::audit($data, $error);
        } elseif(($result == 1 && $params['tpl_type'] == 3) ||($result == 3 && $params['tpl_type'] == 3)) {
            //修改自定义文档最终审核状态
            $result = Document::audit($data, $error);
        }

        if ($error)
        {
            $this->returnmsg(402,$error);
        }
        elseif($result)
        {
            if ($data['audit_status'] == 1) {
                \app\api\model\ProjectMsg::documentAuditPass($data['doc_id'], $params['tpl_type']);
                //审核通过短信、邮件通知
                \app\api\model\ProjectNode::sendDocMes($data['doc_id'], $params['tpl_type'],$audit = true);
            } else {
                \app\api\model\ProjectMsg::documentAuditReject($data['doc_id'], $params['tpl_type']);
                //审核驳回短信、邮件通知
                \app\api\model\ProjectNode::sendDocMes($data['doc_id'], $params['tpl_type'],$audit = false);
            }
            $this->returnmsg(200,'success！');
        }
        else
        {
            $this->returnmsg(402,'参数错误！');
        }

    }


    /*
     * 新增、修改BOM文档
     */
    public function saveBom()
    {
        $jsonData = $this->request->param();
        $params = json_decode($jsonData['data'],true);

        $rule = [
            'tpl_id' => 'require|integer|>:0',
            'project_id' => 'require|integer|>:0',
            'node_id' => 'require|integer|>:0',
//            'material_sn' => 'require',
            'sub_material' => 'require',
            'is_new' =>'integer|between:0,1'
        ];
        $msg = [
            'tpl_id.require' => '文档模板ID不能为空！',
            'project_id.require' => '项目ID不能为空！',
            'node_id.require' => '项目节点ID不能为空！',
//            'material_sn.require' => '父项物料号不能为空！',
            'sub_material' => '子项物料不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($params);
        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        //项目已暂停,无法操作
        if ($error = \app\api\model\Project::checkProjectPause($params['project_id'])) {
            $this->returnmsg(401, $error);
        }

        //验证子项物料
        if(is_array($params['sub_material']))
        {
            foreach ($params['sub_material'] as $v)
            {
                if (!isset($v['material_sn']) || !$v['material_sn']) return $this->returnmsg(401, '子项物料号不能为空');
                if (!isset($v['numerator_amount']) || !$v['numerator_amount']) return $this->returnmsg(401, '分子用量不能为空');
                if (!isset($v['denominator_amount']) || !$v['numerator_amount']) return $this->returnmsg(401, '分母用量不能为空');
            }
        }

        $isNew = isset($params['is_new']) && !empty($params['is_new']) ? 1 : 0;//是否增加版本号
        $bomId= isset($params['bom_id']) ? $params['bom_id'] : '';  //主键值bom_id
        $proData['node_id'] = $params['node_id'] ;  //项目节点id
        $proData['project_id'] = $params['project_id']; //项目id
        $proData['mg_id'] = isset($params['mg_id'])?$params['mg_id']:0; //分组ID
        $proData['project_name'] = \app\api\model\Project::getInfoByProjectId($params['project_id'],'project_name')['project_name'];//BOM名称
        $proData['tpl_id'] = $params['tpl_id']; //文档模板ID
        $materialData['material_sn'] = isset($params['material_sn']) ? $params['material_sn'] : ''; //父项物料号
        $proData['creator_id'] = Auth::instance()->getUser()['admin_id'];   //获取admin_id

        //is_new为0、bom_id不为0则修改项目，反之则版本号加0.1
        if($bomId && !$isNew)
        {
            //通过bom_id查询数据是否存在
            $bomResult = ProjectBom::getInstanceByBomId($bomId);
            $proData['bom_id'] = $bomId;
            if ($bomResult)
            {
                $map = true;
                Db::startTrans();
                //通过文档模板id获取文档名称、文档类型
                $tpl = DocTemplate::getTplName($proData['tpl_id']);
                $proData['tpl_name'] = $tpl['tpl_name'];
                $proData['tpl_type'] = $tpl['tpl_type'];
                //修改Bom文档
                $result = ProjectBom::updateInfo(['bom_id'=>$bomId],$proData);
                $sonMaterialData['bom_id'] = $materialData['bom_id'] = $bomId;
                //通过父项物料编码到标准资源库查询一条记录,如果没有就不能修改
                if(!empty($materialData['material_sn']))
                {
                    $materialResult = Resource::findIdByMaterialCode($materialData['material_sn']);
                    if($materialResult)
                    {
                        $materialInfo = Material::getMaterialInfo($materialData['material_sn']);//通过物料编码获取物料名称等数据
                        $materialData['material_name'] = $materialInfo['material_name'];//物料名称
                        $materialData['specification'] = $materialInfo['specifications'];//规格型号
                        $materialData['unit'] = $materialInfo['basic_unit'];//基本单位
                        $materialData['relative'] = 1;  //父项关联物料
                        $materialData['project_id'] = $params['project_id'];  //项目id

                        //$res = BomMaterial::getMaterialInfo($bomId);
                        $res = BomMaterial::materialSn(['material_sn'=>$materialData['material_sn'],'relative'=>1,'project_id'=>$params['project_id']]);
                        if($res)
                        {
                            $this->returnmsg(402,'父项物料号已经存在，无需重复添加','');
                            //BomMaterial::updateInfo(['bom_id'=>$bomId,'relative'=>1],$materialData);
                        }else{

                            BomMaterial::add($materialData);
                        }
                    }
                }

                //删除原有的子项物料
                BomMaterial::del(['bom_id'=>$bomId,'relative'=>0]);

                foreach ($params['sub_material'] as $v)
                {
                    $sonMaterialData['material_sn'] = $v['material_sn'];
                    $sonInfo = Material::getMaterialInfo($v['material_sn']);
                    $sonMaterialData['material_name'] = $sonInfo['material_name'];
                    $sonMaterialData['specification'] = $sonInfo['specifications'];
                    $sonMaterialData['unit'] = $sonInfo['basic_unit'];
                    $sonMaterialData['numerator_amount'] = $v['numerator_amount'];//分子用量
                    $sonMaterialData['denominator_amount'] = $v['denominator_amount'];//分母用量
                    $sonMaterialData['remark'] = isset($v['remark']) ? $v['remark'] : '';
                    $sonMaterialData['location_num'] = isset($v['location_num']) ? $v['location_num'] : ''; //位号
                    //标准资源库里没有的物料编码，不允许添加
                    if(Resource::findIdByMaterialCode($v['material_sn']))
                    {
                        //子项物料的添加
                        BomMaterial::add($sonMaterialData);
                    } else {
                        $map = false;
                        break;
                    }
                }
                $arr['bom_id'] = $bomId;
            } else {
                $map = false;
            }
            //判断事务的操作，true则提交，false则回滚
            if($map)
            {
                Db::commit();
                $this->returnmsg(200, 'success！',$arr );
            }
            Db::rollback();
            $this->returnmsg(402,'标准资源库无对应的物料编码','');
        }else{
            //通过bom_id查询数据是否存在,存在则版本号加0.1并新增到数据库
            $where['node_id'] = $params['node_id'];
            $where['project_id'] = $params['project_id'];
            $where['tpl_id'] = $proData['tpl_id'];
            $bomInfo = ProjectBom::getInfoByNodeId($where);
            if($bomInfo){
                $proData['bom_id'] = $bomInfo[0]['bom_id'];
                $version = ProjectBom::getVersion($proData['bom_id']);
                $proData['version'] = $version+0.1;
                //重新编辑的修改之前的状态
                if ($bomInfo[0]['audit_status'] == -1) {
                    ProjectBom::reedit(['bom_id' => $bomInfo[0]['bom_id']]);
                }
            }

            $map = true;
            Db::startTrans();
            //通过文档模板id获取文档名称、文档类型
            $tpl = DocTemplate::getTplName($proData['tpl_id']);
            $proData['tpl_name'] = $tpl['tpl_name'];
            $proData['tpl_type'] = $tpl['tpl_type'];

            //Bom文档添加到数据库
            $result = ProjectBom::add($proData);
            if ($result)
            {
                $sonMaterialData['bom_id'] = $materialData['bom_id'] = $result;
                //通过父项物料编码到标准资源库查询一条记录,如果没有就不能添加
                if(!empty($materialData['material_sn']))
                {
                    $materialResult = Resource::findIdByMaterialCode($materialData['material_sn']);
                    if($materialResult)
                    {
                        $materialInfo = Material::getMaterialInfo($materialData['material_sn']);//通过物料编码获取物料名称等数据
                        $materialData['material_name'] = $materialInfo['material_name'];//物料名称
                        $materialData['specification'] = $materialInfo['specifications'];//规格型号
                        $materialData['unit'] = $materialInfo['basic_unit'];//基本单位
                        $materialData['relative'] = 1;  //父项关联物料
                        $materialData['project_id'] = $params['project_id'];  //项目id

                        BomMaterial::add($materialData);
                    }
                }

                foreach ($params['sub_material'] as $v)
                {
                    $sonMaterialData['material_sn'] = $v['material_sn'];
                    $sonInfo = Material::getMaterialInfo($v['material_sn']);
                    $sonMaterialData['material_name'] = $sonInfo['material_name'];
                    $sonMaterialData['specification'] = $sonInfo['specifications'];
                    $sonMaterialData['unit'] = $sonInfo['basic_unit'];
                    $sonMaterialData['numerator_amount'] = $v['numerator_amount'];//分子用量
                    $sonMaterialData['denominator_amount'] = $v['denominator_amount'];//分母用量
                    $sonMaterialData['remark'] = isset($v['remark']) ? $v['remark'] : '';
                    $sonMaterialData['location_num'] = isset($v['location_num']) ? $v['location_num'] : ''; //位号
                    //标准资源库里没有的物料编码，不允许添加
                    if(Resource::findIdByMaterialCode($v['material_sn']))
                    {
                        //子项物料的添加
                        BomMaterial::add($sonMaterialData);
                    } else {
                        $map = false;
                        break;
                    }
                }
                $arr['bom_id'] = $result;
            } else {
                $map = false;
            }
            //判断事务的操作，true则提交，false则回滚
            if($map) {
                Db::commit();
                \app\api\model\ProjectMsg::documentSubmit($params['node_id'], $result, $proData['tpl_id']);
                $this->returnmsg(200, 'success！',$arr );
            }
            Db::rollback();
            $this->returnmsg(402,'标准资源库无对应的物料编码','');
        }
    }

    /*
     * 获取BOM表详情
     */
    public function getBomDetail()
    {
        $jsonData = $this->request->param();
        $params = json_decode($jsonData['data'],true);
        $bomId = isset($params['bom_id']) ? $params['bom_id'] : '';
        if(!$bomId)
        {
            $this->returnmsg(401,'请检查必填参数！');
        }
        else
        {
            $result = ProjectBom::getInstanceByBomId($bomId);
            if($result)
            {
                $BomArr = ProjectBom::getDetail($bomId);
                $where['target_id'] = $BomArr['project_id'];
                /*  $where['target_type'] = 4;
                  $nameList = Relation::getRoleName($where);//获取各角色类型
                  $BomArr['creator'] = $nameList['uploads'];//创建者
                  $BomArr['auditor'] = $nameList['auditor'];//审核人员*/
                $materialInfo = BomMaterial::getMaterialInfo($bomId);//查询父项物料信息
                $BomArr['material_sn'] = $materialInfo['material_sn'];
                $BomArr['material_name'] = $materialInfo['material_name'];
                $BomArr['specification'] = $materialInfo['specification'];
                $BomArr['unit'] = $materialInfo['unit'];
                $materialInfo = BomMaterial::getSonMaterialInfo($bomId);//查询子项物料信息
                $BomArr['sub_material'] = $materialInfo;
                /* echo '<pre>';
                 print_r($BomArr);die;*/
                $this->returnmsg(200,'success！',$BomArr);
            }
            else
            {
                $this->returnmsg(402,'参数错误！');
            }

        }
    }


    /*
     * 删除文档
     */
    public function del()
    {
        $jsonData = $this->request->param();
        $params = json_decode($jsonData['data'],true);

        $rule = [
            'doc_id' => 'require|integer|>:0',
            'tpl_type' => 'require|integer|>:0',
            'del_all_version' => 'integer|between:0,1'
        ];
        $msg = [
            'doc_id.require' => '文档模板ID不能为空！',
            'tpl_type.require' => '项目ID不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($params);
        if (!$result)
        {
            return $this->returnmsg(401,$this->validate->getError(),"");
        }
        //文档模板ID
        $docId = intval($params['doc_id']);
        //文档模板类型:1 BOM表;2 开发立项表;3 自定义文档;
        $tplType =  $params['tpl_type'];
        $delVal = isset($params['del_all_version']) && !empty($params['del_all_version']) ? 1 : 0;

        //del_all_version为1，则要删除这个文档的所有版本（不包括已审核的）
        if($delVal)
        {
            if($tplType == 1)
            {
                $tplInfo = ProjectBom::tplInfo(['bom_id'=>$docId],'tpl_id,node_id');
                if($tplInfo)
                {
                    $map['tpl_id'] = $tplInfo['tpl_id'];
                    $map['node_id'] = $tplInfo['node_id'];
                    $map['audit_status'] = ['<',1];
//                    DocTemplate::setStatus($tplInfo['tpl_id'],$tplType);
                    $result = ProjectBom::delAll($map);
                }else{
                    return $this->returnmsg(402, '数据库无此doc_id记录');
                }
            }
            elseif($tplType == 2)
            {
                $tplInfo = UitableviewDefault::getInfo(['ud_id'=>$docId],'tpl_id,node_id');
                if($tplInfo)
                {
                    $map['tpl_id'] = $tplInfo['tpl_id'];
                    $map['node_id'] = $tplInfo['node_id'];
                    UitableviewValue::del($map);
//                    DocTemplate::setStatus($tplInfo['tpl_id'],$tplType);
                    $map['audit_status'] = ['<',1];
                    $result = UitableviewDefault::delInfo($map);
                }else{
                    return $this->returnmsg(402, '数据库无此doc_id记录');
                }
            }
            elseif($tplType == 3)
            {
                $tplInfo = Document::tplInfo(['pd_id'=>$docId],'tpl_id,node_id');
                if($tplInfo)
                {
                    $map['tpl_id'] = $tplInfo['tpl_id'];
                    $map['node_id'] = $tplInfo['node_id'];
                    $map['audit_status'] = ['<',1];
//                    DocTemplate::setStatus($tplInfo['tpl_id'],$tplType);
                    $result = Document::del($map);
                }else{
                    return $this->returnmsg(402, '数据库无此doc_id记录');
                }
            }
            if($result)
            {
                return $this->returnmsg(200,'success！');
            }
            else
            {
                return $this->returnmsg(400,'操作失败');
            }
        }
        else
        {
            if ($tplType == 1)
            {
//                DocTemplate::setStatus($docId, $tplType);
                $result = ProjectBom::del($docId, $tplType);
            }
            elseif ($tplType == 2)
            {
//                DocTemplate::setStatus($docId, $tplType);
                UitableviewValue::setState($docId);
                $tplInfo = UitableviewDefault::getInfo(['ud_id'=>$docId],'tpl_id,node_id');
                if($tplInfo)
                {
                    $result = UitableviewDefault::setState($docId,$tplInfo['tpl_id']);
                }

            }
            elseif ($tplType == 3)
            {
//                DocTemplate::setStatus($docId, $tplType);
                $result = Document::setStatus($docId);
            }
            if ($result)
            {
                return $this->returnmsg(200, 'success！');
            }
            else
            {
                return $this->returnmsg(400, '操作失败');
            }
        }
    }

    /*
     * 新增、修改开发立项表
     */
    public function setUpProject()
    {
        $jsonData = $this->request->param();
        $params = json_decode($jsonData['data'],true);
        //数据验证
        $result = $this->validate($params, [
            'tpl_id' => 'require|integer|>:0',//>:0
            'project_id' => 'require|integer|>:0',
            'node_id' => 'require|integer|>:0',
            'open_mode' => 'require|array',
            'product_location' => 'require|array',
            'is_new' =>'integer|between:0,1'
        ]);

        if ($result !== true)
        {
            return $this->returnmsg(401,$result);
        }

        $isNew = isset($params['is_new']) && !empty($params['is_new']) ? 1 : 0;//是否增加版本号0--否，1--是
        $info['project_id'] = $data['project_id'] = $params['project_id'];//项目id
        $info['tpl_id'] =  $data['tpl_id'] = $params['tpl_id'];//文档模板id
        $info['node_id'] = $data['node_id'] = $params['node_id'];//节点id
        $data['open_mode'] = implode(',',$params['open_mode']);//开案模式
        $data['product_location'] = implode(',',$params['product_location']);//产品定位
        $data['user_face'] = isset($params['user_face']) ? $params['user_face'] : '';
        $data['usage_scenario'] = isset($params['usage_scenario']) ? $params['usage_scenario'] : '';//场景使用
        $data['pain_spot_solve'] = isset($params['pain_spot_solve']) ? $params['pain_spot_solve'] : '';//痛点解决
        $channelMatchArr = isset($params['channel_matching']) ? $params['channel_matching'] : '';//渠道匹配
        $data['product_cost'] = isset($params['product_cost']) ? $params['product_cost'] : '';//产品成本
        $data['target_cost'] = isset($params['target_cost']) ? $params['target_cost'] : '';//目标成本
        $data['first_number'] = isset($params['first_number']) ? $params['first_number'] : '';//首单数量
        $data['channel_pricing'] = isset($params['channel_pricing']) ? $params['channel_pricing'] : '';//渠道定价
        $data['gross_margin'] = isset($params['gross_margin']) ? $params['gross_margin'] : '';
        $data['annual_sales'] = isset($params['annual_sales']) ? $params['annual_sales'] : '';
        $data['lifecycle'] = isset($params['lifecycle']) ? $params['lifecycle'] : ''; //生命周期
        $data['annual_money'] = isset($params['annual_money']) ? $params['annual_money'] : ''; //年销售额
        $data['market_time'] = isset($params['market_time']) ? strtotime($params['market_time']) : ''; //上市时间
        $data['remarks'] = isset($params['remarks']) ? $params['remarks'] : ''; //备注
        $tplData = isset($params['tpl_data']) ? $params['tpl_data'] : '';//根据模板填写的信息

        //判断渠道匹配的参数
        if(is_array($channelMatchArr) && !empty($channelMatchArr))
        {
            if(is_array($channelMatchArr['option']) && $channelMatchArr['option'])
            {
                $data['channel_matching'] = implode(',',$channelMatchArr['option']);
            }
            if($channelMatchArr['text'])
            {
                $data['channel_matching'] = $data['channel_matching'].'|'.$channelMatchArr['text'];
            }
        }

        //开启事务
        Db::startTrans();
        $map = true;
        //新增、修改开发立项（如果is_new为0则代表修改，1为新增）
        if(!$isNew)
        {
            $docIdInfo = UitableviewDefault::getInfo(['node_id'=>$info['node_id'],'tpl_id'=>$info['tpl_id']],'ud_id');
            $docId = $docIdInfo['ud_id'];
            $result = UitableviewDefault::updateInfo(['node_id'=>$info['node_id'],'tpl_id'=>$info['tpl_id']],$data);
            if(!$result) $map = false;

            if($tplData)
            {
                foreach($tplData as $v)
                {
                    $info['view_id'] = $v['view_id'];
                    $info['attr_value'] = isset($v['attr_value']) ? $v['attr_value'] : '';
                    $res = UitableviewValue::updateInfo($info);
                    if(!$res)
                    {
                        $map = false;
                        break;
                    }
                }
            }
            if($map)
            {
                Db::commit();
                return $this->returnmsg(200,'success！',['doc_id'=>$docId]);
            }
            Db::rollback();
            return $this->returnmsg(402,'数据重复，请修改后再提交！','');
        }
        else
        {
            //获取admin_id
            $data['creator_id'] = $info['admin_id'] = Auth::instance()->getUser()['admin_id'];
            //查询版本号是否存在
            $where['project_id'] = intval($data['project_id']);
            $where['node_id'] = $data['node_id'];
            $where['tpl_id'] = $data['tpl_id'];
            $version = UitableviewDefault::getUdVersion($where);
            if($version){
                $data['version'] = $version+0.1;
                //驳回后的再编辑修改重新编辑状态
                UitableviewDefault::reedit($where,$version);
            }

            //新增到表格固定模块数据表plm_uitableview_default
            $result = UitableviewDefault::add($data);
            if (!$result) $map = false;
            if ($tplData) {
                foreach ($tplData as $v) {
                    $info['view_id'] = $v['view_id'];
                    $info['attr_value'] = isset($v['attr_value']) ? $v['attr_value'] : '';
                    //新增到表格固定模块数据表plm_uitableview_value
                    $res = UitableviewValue::add($info);
                    if (!$res) {
                        $map = false;
                        continue;
                    }
                }
            }
            if ($map) {
                Db::commit();
                \app\api\model\ProjectMsg::documentSubmit($data['node_id'], $result, $data['tpl_id']);
                $this->returnmsg(200, 'success！',['doc_id'=>$result]);
            }
            Db::rollback();
            $this->returnmsg(402, '新增失败！', '');
        }
    }

    /*
     *获取开发立项表详情
     */
    public function getProjectSetUpDoc()
    {
        $jsonData = $this->request->param();
        $params = json_decode($jsonData['data'],true);

        $rule = [
            'tpl_id' => 'require|integer|>:0',
            'project_id' => 'require|integer|>:0',
            'node_id' => 'require|integer|>:0',
        ];
        $msg = [
            'tpl_id.require' => '文档模板ID不能为空！',
            'project_id.require' => '项目ID不能为空！',
            'node_id.require' => '项目节点ID不能为空！',
        ];
        $this->validate = new Validate($rule, $msg);
        $result = $this->validate->check($params);
        if (!$result)
        {
            $this->returnmsg(401,$this->validate->getError(),"");
        }

        $where['tpl_id'] = $params['tpl_id'] ;
        $where['project_id'] = $params['project_id'];
        $where['node_id'] = $params['node_id'];
        //获取开发立项表格固定模块的数据
        $uDInfo = UitableviewDefault::getInfo($where);
        if($uDInfo)
        {
            $creatorInfo = Relation::getCreatorInfo($uDInfo['creator_id'],'username,nickname');
            $data['creator'] = ['admin_id'=>$uDInfo['creator_id'],'username'=>$creatorInfo['username'],'nickname'=>$creatorInfo['nickname'] ];
            $data['create_at'] = $uDInfo['create_at'];
            $data['project_name'] = Project::getInfoByProjectId($params['project_id'], 'project_name')['project_name'];
            //节点审核人员
            $node_map['target_id'] = $params['node_id'];
            $node_map['target_type'] = 2;
            $node_map['role_type'] = 5;
            $field = 'admin_id,role_type,audit_time';
            $nodeAuditor = Relation::auditInfo($node_map);
            $map['target_id'] = $data['doc_id'] = $uDInfo['ud_id'];
            $map['target_type'] = 6;
            $map['role_type'] = 5;//文档审核人员
            $udAuditor = Relation::auditInfo($map);
            //最后审核时间
            $udAuditorTime = Relation::getAuditorMes($map,$field);
            if (!empty($nodeAuditor['auditor']) && isset($udAuditor['auditor']) ) {
                foreach ($nodeAuditor['auditor'] as &$item) {
                    foreach ($udAuditor['auditor'] as &$doc_item) {
                        if ($item['admin_id'] == $doc_item['admin_id']) {
                            $item = $doc_item;
                        }
                    }
                }
            }

            $data['auditor'] = isset($nodeAuditor['auditor'])?$nodeAuditor['auditor']:[];
            $data['audit_time'] = $udAuditorTime['audit_time'];
            $data['version'] = $uDInfo['version'];
            $data['user_face'] = $uDInfo['user_face'];
            $data['usage_scenario'] = $uDInfo['usage_scenario'];
            $data['pain_spot_solve'] = $uDInfo['pain_spot_solve'];
            $data['product_cost'] = $uDInfo['product_cost'];
            $data['target_cost'] = $uDInfo['target_cost'];
            $data['first_number'] = $uDInfo['first_number'];
            $data['channel_pricing'] = $uDInfo['channel_pricing'];
            $data['gross_margin'] = $uDInfo['gross_margin'];
            $data['annual_sales'] = $uDInfo['annual_sales'];
            $data['lifecycle'] = $uDInfo['lifecycle'];
            $data['annual_money'] = $uDInfo['annual_money'];
            $data['market_time'] = date('Y-m-d',$uDInfo['market_time']);
            $data['remarks'] = $uDInfo['remarks'];

            $data['open_mode'] = explode(',',$uDInfo['open_mode']);
            $data['product_location'] = explode(',',$uDInfo['product_location']);
            if(strpos($uDInfo['channel_matching'],'|') !== false)
            {
                $arr = explode('|',$uDInfo['channel_matching']);
                $data['channel_matching']['option'] = explode(',',$arr[0]);
                $data['channel_matching']['text'] = $arr[1];
            }
            else
            {
                $data['channel_matching']['option'] = explode(',',$uDInfo['channel_matching']);
                $data['channel_matching']['text'] = '';
            }

            //获取开发立项表填写的信息
            $data['tpl_data'] = UitableviewValue::getUValueInfo($where['tpl_id']);
            $this->returnmsg(200,'success！',$data);
        }
        else
        {
            $this->returnmsg(402,'数据库无此数据','');
        }
    }

    /*
     * 获取自定义文档详情
     */
    public function getDocDetail()
    {
        $jsonData = $this->request->param();
        $params = json_decode($jsonData['data'],true);
        //数据验证
        $result = $this->validate($params, [
            'doc_id' => 'require|integer|>:0'

        ]);

        if ($result !== true)
        {
            $this->returnmsg(401,$result);
        }
        $data = Document::docData($params['doc_id']);

        if(!empty($data))
        {
            $this->returnmsg(200,'success！',$data);
        }
        $this->returnmsg(402,'数据库无数据！','');
    }

    /**
     * 上传自定义文档
     * @param $data
     */
    public function uploadDoc($data)
    {
        //读取请求数据
        $data = json_decode($data, true);

        //数据验证
        $result = $this->validate($data, [
            'node_id' => 'require',
            'tpl_id' => 'require',
            'file_path' => 'require',
            'is_new' => 'in:0,1',
        ]);

        if ($result !== true) {
            $this->returnmsg(402, $result);
        }

        try {
            $this->returnmsg(200, 'success!', Document::uploadDoc2($data));
        } catch (Exception $e) {
            $this->returnmsg(400, $e->getMessage());
        }
    }

    /**
     * 获取项目文档列表
     * @param $data
     * @link http://192.168.80.240:4999/web/#/5?page_id=217
     */
    public function getProjDocList($data)
    {
        //读取请求数据
        $data = json_decode($data, true);

        //数据验证
        $result = $this->validate($data, [
            'project_id' => 'require',
        ]);

        if ($result !== true) {
            $this->returnmsg(402, $result);
        }

        try {
            $this->returnmsg(200, 'success!', Document::getProjDocList($data));
        } catch (Exception $e) {
            $this->returnmsg(400, $e->getMessage());
        }
    }

    /**
     * 获取项目文档历史
     * @param $data
     * @link http://192.168.80.240:4999/web/#/5?page_id=218
     */
    public function getProjDocHistory($data)
    {
        //读取请求数据
        $data = json_decode($data, true);

        //数据验证
        $result = $this->validate($data, [
            'project_id' => 'require',
            'plm_dir_id' => 'require',
            'tpl_type' => 'require',
            'page_no' => 'number|>:0',
            'page_size' => 'number|>:0',
        ]);

        if ($result !== true) {
            $this->returnmsg(402, $result);
        }

        try {
            $this->returnmsg(200, 'success!', Document::getProjDocHistory($data));
        } catch (Exception $e) {
            $this->returnmsg(400, $e->getMessage());
        }
    }

    //搜索文档下载记录
    public function getDownRecord()
    {
        try {
            $this->returnmsg(200, 'success!', Document::getDownRecord($this->autoValidate()));
        } catch (Exception $e) {
            $this->returnmsg(400, $e->getMessage());
        }
    }

    //添加下载记录
    public function addDownRecord()
    {
        $data = $this->autoValidate(['doc_id' => 'require']);
        try {
            $this->returnmsg(200, 'success!', Document::addDownRecord($data));
        } catch (Exception $e) {
            $this->returnmsg(400, $e->getMessage());
        }
    }
}
