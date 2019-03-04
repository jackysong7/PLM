<?php
/**
 * Created by PhpStorm.
 * User: chenhailiang
 * Date: 2018/7/6
 * Time: 16:02
 */
namespace app\api\model;

use think\Db;
use think\Model;
use think\Config;

class ProjectEcn extends Model
{
    public static function initConnect(){
        return Db::connect(Config::get('database.center'));
    }

    public static function getList($params){
        $where ="pb.project_type = 2 AND bm.relative = 1";
        if(isset($params['keyword'])){
            $where .=" AND pe.project_name like '%".$params['keyword']."%' OR bm.material_sn like '%".$params['keyword']."%' OR pe.change_material like '%".$params['keyword']."%'";
        }
        if(isset($params['is_me']) && $params['is_me'] == true){
            $relation = Db::name('relation')->field('target_id')->where('admin_id = '.$params['admin_id'].' AND target_type = 5 AND audit_status = 0')->select();
            $array = array();
            foreach($relation as $v){
                array_push($array,$v['target_id']);
            }
            $ecnIds = implode(",",$array);
            if(!empty($ecnIds)){
                $where .=" AND pe.ecn_id in (".$ecnIds.")";
            }else{
                $data = array(
                    'list' => array(),
                    'page' => ['total_count' =>0,
                        'current_page' => $params['page'],
                        'page_size' => $params['limit'],
                        'total_page' => 0],
                );
                return $data;
            }
        }

        $count = Db::query("
               SELECT
                 count(*) as allCount
                FROM
                    plm_project_ecn pe
                LEFT JOIN plm_project_bom pb ON pb.ecn_id = pe.ecn_id
                LEFT JOIN `7000mall_plm`.`plm_bom_material` `bm` ON `pb`.`bom_id` = `bm`.`bom_id` AND `pb`.`project_id` = `bm`.`project_id`
                LEFT JOIN `7000mall_plm`.`plm_basedata` `b` ON `pe`.`project_id` = `b`.`project_id`
                LEFT JOIN `7000mall_plm`.`plm_erp_attribute` `ea` ON `b`.`plm_no` = `ea`.`plm_no`
                WHERE
               ".$where." ORDER BY pe.ecn_id DESC");

//        $count = Db::name('project_ecn')
//            ->alias('pe')
//            ->field('pe.ecn_id,pe.project_name,ea.erp_no as finished_product_code,pe.change_doc,pe.customer_reason,pe.change_type,pe.change_material,bm.material_sn,pb.version,pe.application_user,pe.application_date,pe.final_auditor,pe.audit_time,pe.audit_status,pe.audit_note')
//            ->join('basedata b','pe.project_id = b.project_id','LEFT')
//            ->join('erp_attribute ea','b.plm_no = ea.plm_no','LEFT')
//            ->join('project_bom pb','pb.project_id = pe.project_id  AND pb.node_id = pe.project_node','LEFT')
//            ->join('bom_material bm','pb.bom_id = bm.bom_id','LEFT')
//            ->where($where)
//            ->group('pe.ecn_id')
//            ->count();

//        $list = Db::name('project_ecn')
//            ->alias('pe')
//            ->field('pe.ecn_id,pe.project_name,ea.erp_no as finished_product_code,pe.change_doc,pe.change_type,pe.customer_reason,pe.change_material,bm.material_sn,pb.version,pe.application_user,pe.application_date,pe.final_auditor,pe.audit_time,pe.audit_status,pe.audit_note')
//            ->join('basedata b','pe.project_id = b.project_id','LEFT')
//            ->join('erp_attribute ea','b.plm_no = ea.plm_no','LEFT')
//            ->join('project_bom pb','pb.project_id = pe.project_id  AND pb.node_id = pe.project_node','LEFT')
//            ->join('bom_material bm','pb.bom_id = bm.bom_id','LEFT')
//            ->where($where)
//            ->group('pe.ecn_id')
//            ->page($params['page'],$params['limit'])
//            ->select();
        $list = Db::query("
               SELECT
                    `pe`.`ecn_id`,
                    `pe`.`project_name`,
                    ea.erp_no AS finished_product_code,
                    `pe`.`change_doc`,
                    `pe`.`customer_reason`,
                    `pe`.`change_type`,
                    `pe`.`change_material`,
                    `bm`.`material_sn`,
                    `pb`.`version`,
                    `pe`.`application_user`,
                    `pe`.`application_date`,
                    `pe`.`final_auditor`,
                    `pe`.`audit_time`,
                    `pe`.`audit_status`,
                    `pe`.`audit_note`,
                    `d`.`version` AS document_version
                FROM
                    plm_project_ecn pe
                LEFT JOIN plm_project_bom pb ON pb.ecn_id = pe.ecn_id
                LEFT JOIN `7000mall_plm`.`plm_bom_material` `bm` ON `pb`.`bom_id` = `bm`.`bom_id` AND `pb`.`project_id` = `bm`.`project_id`
                LEFT JOIN `7000mall_plm`.`plm_basedata` `b` ON `pe`.`project_id` = `b`.`project_id`
                LEFT JOIN `7000mall_plm`.`plm_erp_attribute` `ea` ON `b`.`plm_no` = `ea`.`plm_no`
                LEFT JOIN `7000mall_plm`.`plm_document` `d` ON `pe`.`project_id` = `d`.`project_id` AND `pe`.`project_node` = `d`.`node_id` AND `pe`.`change_doc` = `d`.`tpl_id`
                WHERE
                ".$where." ORDER BY pe.ecn_id DESC");

        if(!empty($list)){
            foreach($list as $k=>$v){
                $applicationResult = self::initConnect()
                    ->name('admin')
                    ->field('nickname')
                    ->where("admin_id =".$v['application_user'])
                    ->find();
                $list[$k]['application_user_nickname'] = $applicationResult['nickname'];

                $result = self::initConnect()
                    ->name('admin')
                    ->field('nickname')
                    ->where("admin_id =".$v['final_auditor'])
                    ->find();
                $list[$k]['nickname'] = $result['nickname'];
            }
        }

        $endCount = empty($count)? 0 : $count[0]['allCount'];
        $data = array(
            'list' => empty($list)? array() : $list,
            'page' => ['total_count' =>$endCount,
                'current_page' => $params['page'],
                'page_size' => $params['limit'],
                'total_page' => ceil($endCount / $params['limit'])],
        );
        return $data;
    }

    public static function getInfo($params){
        if(isset($params['ecn_id'])){
            $where ="ecn_id = ".$params['ecn_id'];
            $projectEcn = Db::name('project_ecn')
                ->field('application_date,application_user,final_auditor,project_id,project_node,change_doc,change_material,customer_reason,change_type,transportation_inventory,in_stock,change_way,change_way_date,change_cost')
                ->where($where)
                ->find();

            if(!empty($projectEcn)){
                //变更申请人昵称
                $application_user_nickname = Db::query("
                SELECT
                    ca.nickname
                FROM
                    7000mall_center.c_admin AS ca
                WHERE
                    ca.status = 1
                AND ca.admin_id = ".$projectEcn['application_user']."

                ");

                $projectEcn['application_user_nickname'] = $application_user_nickname[0]['nickname'];

                //最终变更审核人昵称
                $final_auditor_nickname = Db::query("
                SELECT
                    ca.nickname
                FROM
                    7000mall_center.c_admin AS ca
                WHERE
                    ca.status = 1
                AND ca.admin_id = ".$projectEcn['final_auditor']."

                ");

                $projectEcn['final_auditor_nickname'] = $final_auditor_nickname[0]['nickname'];

                //变更审核人信息组
                $relation = Db::query("
                SELECT
                    ca.nickname,
                    pr.audit_time,
                    pr.audit_status,
                    pr.audit_note
                FROM
                    7000mall_plm.plm_relation AS pr
                LEFT JOIN 7000mall_center.c_admin AS ca ON pr.admin_id = ca.admin_id
                WHERE
                    pr.target_id = ".$params['ecn_id']."
                AND pr.target_type = 5
                ");

                $projectEcn['application_auditor'] = $relation;

                if(empty($projectEcn['change_material'])){
                    //文档信息
                    $documentInfo = Db::name('document')
                        ->alias('d')
                        ->field('d.file_name,d.file_path')
                        ->join('basedata b','d.plm_no = b.plm_no')
                        ->where('b.project_id = '.$projectEcn['project_id'].' AND d.project_type = 2 AND d.node_id = '.$projectEcn['project_node'].' AND d.tpl_id = '.$projectEcn['change_doc'])
                        ->order('d.createtime desc')
                        ->find();
                    $projectEcn['document_info'] = $documentInfo;
                }else{
                    $projectEcn['document_info'] = "";
                }

                //变更前的内容
                $prevBomId = Db::name('project_bom')->field('version,project_id,node_id,project_type')->where('ecn_id = '.$params['ecn_id'])->find();
                //$get_bom_id = Db::name('project_bom')->field('bom_id,version')->where('ecn_id = '.$params['ecn_id'])->find();
                $get_bom_id = Db::name('project_bom')->field('bom_id,version')->where(['version'=>($prevBomId['version']-0.1),'project_id'=>$prevBomId['project_id'],'node_id'=>$prevBomId['node_id'],'project_type'=>$prevBomId['project_type']])->find();
                $projectEcn['get_bom_id'] = $get_bom_id['bom_id'];
                $projectEcn['get_version'] = $get_bom_id['version'];
                $getChangeMaterial = self::changeMaterialLastInfo($projectEcn);
//                $ProjectBomVersion = Db::name('project_bom')->field('version')->where('bom_id = '.$getChangeMaterial['bom_id'])->find();
//                $getChangeMaterial['version'] = $ProjectBomVersion['version'];
                $projectEcn['original_bom'] = $getChangeMaterial;

                //变更后的内容
                $getProjectBom = Db::name('project_bom')->where('project_id = '.$projectEcn['project_id'].' AND node_id = '.$projectEcn['project_node'])->order('version desc')->find();
                if(!empty($getProjectBom)) {
                    //父项物料
                    $bom_material = Db::name('bom_material')->where('relative = 1 AND bom_id = ' . $getProjectBom['bom_id'] . ' AND project_id = ' . $projectEcn['project_id'])->find();
                    $bom_material['version'] = $getProjectBom['version'];
                    //子项物料
                    $sub_bom_material = Db::name('bom_material')->where('relative = 0 AND bom_id = ' . $getProjectBom['bom_id'])->select();

                    $bom_material['sub_material'] = $sub_bom_material;

                    $projectEcn['change_bom'] = $bom_material ;
                }else{
                    $projectEcn['change_bom'] = null;
                }

                return $projectEcn;
            }
        }
    }

    public static function getAllProject(){
        $where = 'status = 2';
        $list = Db::name('project')
            ->field('*')
            ->where($where)
            ->select();
        return $list;
    }

    public static function getProjectDoc($params){
        $where = 'pn.status = 2 AND pn.audit_status = 1';
        if(isset($params['project_id'])){
            $where .=" AND pn.project_id = ".$params['project_id'];
        }
        $project_node = Db::name('project_node')
            ->alias('pn')
            ->field('pn.node_id,pn.doc_tpl_ids,d.plm_dir_name')
            ->join('dir d','pn.process_id = d.plm_dir_id','LEFT')
            ->where($where)
            ->select();

        foreach($project_node as $k=>$v){
            $project_node[$k]['doc_tpl_ids'] = explode(',',$v['doc_tpl_ids']);
            $project_node[$k]['doc_tpl_ids'] = Db::name('doc_template')->field('tpl_id,tpl_name,tpl_type')->whereIn('tpl_type',[1,3])->whereIn('tpl_id',$project_node[$k]['doc_tpl_ids'])->select();
        }
        return $project_node;
    }

    public static function checkCreate($params){
        $check = Db::name('project_ecn')
            ->where('project_id = '.$params['project_id'].' AND project_node = '.$params['project_node'].' AND change_doc = '.$params['change_doc'])
            ->find();
        if(!empty($check)){
            $data = [
                'apply'=>1,
                'result'=>$check['audit_status']
            ];
            return $data;
        }else{
            $data = [
                'apply'=>0,
                'result'=>$check['audit_status']
            ];
            return $data;
        }
    }

    public static function createProjectEcn($params){
        Db::startTrans();
        $res = true;
        $data = array(
            'application_date'=>time(),
            'application_user'=>$params['admin_id'],
            'final_auditor'=>$params['final_auditor'],
            'project_id'=>$params['project_id'],
            'project_name'=>$params['project_name'],
            'project_node'=>$params['project_node'],
            'change_doc'=>$params['change_doc'],
            'change_material'=>$params['change_material'],
            'customer_reason'=>$params['customer_reason'],
            'change_type'=>$params['change_type'],
            'transportation_inventory'=>$params['transportation_inventory'],
            'in_stock'=>$params['in_stock'],
            'change_way'=>$params['change_way'],
            'change_way_date'=>$params['change_way_date'],
            'change_cost'=>$params['change_cost'],
            'audit_status'=>0,
            'audit_time'=>0,
            'audit_note'=>'',
        );
        /* Db::name('project_ecn')->insert($data);
         $result =Db::name('project_ecn')->getLastInsID();*/
        $result = Db::name('project_ecn')->insertGetId($data);
        if(!empty($result)){
            $array = explode(',',$params['application_auditor']);
            //变更审核人
            for($i=0;$i<count($array);$i++){
                $relation = new Relation();
                $relation->target_id = $result;
                $relation->target_type = 5;
                $relation->admin_id = $array[$i];
                $relation->role_type = 5;
                $relation->audit_status = 0;
                $relation->audit_time = time();
                $relation->audit_note = '';
                $relation->save();
            }

            //非BOM,上传文件
            if(!empty($params['document_info'])){
                $getPlmNo = Db::name('basedata')->field('plm_no')->where('project_id = '.$params['project_id'])->find();
                $getProcessId = Db::name('project_node')->field('process_id')->where('node_id = '.$params['project_node'])->find();
                $document = new Document();
                $document->plm_no =$getPlmNo['plm_no'];
                $document->plm_dir_id = $getProcessId['process_id'];
                $document->file_name = $params['document_info']['file_name'];
                $document->file_path = $params['document_info']['upload_file'];
                $document->upload_time = time();
                $document->status = 1;
                $document->project_type = 2;
                $document->admin_id = $params['admin_id'];
                $document->createtime = time();
                $document->updatetime = time();
                $document->project_id = $params['project_id'];
                $document->node_id = $params['project_node'];
                $document->tpl_id = $params['change_doc'];
                $document->save();
            }

            //BOM工程变更
            if(!empty($params['change_material'])){
                //获取项目bom
                $getProjectBom = Db::name('project_bom')->where('project_id = '.$params['project_id'].' AND node_id = '.$params['project_node'])->order('version desc')->find();
                if(!empty($getProjectBom)){
                    //生成新bom
                    $project_bom_data = array(
                        'mg_id'=>0,
                        'mg_code'=>'',
                        'project_name'=>$getProjectBom['project_name'],
                        'tpl_id'=>$getProjectBom['tpl_id'],
                        'tpl_name'=>$getProjectBom['tpl_name'],
                        'tpl_type'=>$getProjectBom['tpl_type'],
                        'version'=>($getProjectBom['version']+0.1),
                        'creator_id'=>$getProjectBom['creator_id'],
                        'add_time'=>time(),
                        'project_id'=>$getProjectBom['project_id'],
                        'project_type'=>2,
                        'node_id'=>$getProjectBom['node_id'],
                        'audit_time'=>0,
                        'audit_status'=>0,
                        'audit_note'=>'',
                        'status'=>1,
                        'submit_time'=>0,
                        'submit_status'=>0,
                        'ecn_id'=>$result,
                    );
                    Db::name('project_bom')->insert($project_bom_data);
                    $project_bom_id =Db::name('project_bom')->getLastInsID();

                    if(!empty($project_bom_id)){

                        foreach($params['change_bom']['sub_material'] as $k=>$v){
                            $getMaterial = Db::name('material')->where("status = 1 AND material_code = '".$v['material_sn']."'")->find();
                            if(!empty($getMaterial)){
                                //生成新的子项物料
                                $bom_material = new BomMaterial();
                                $bom_material->bom_id = $project_bom_id;
                                $bom_material->material_name = $getMaterial['material_name'];
                                $bom_material->material_sn = $getMaterial['material_code'];
                                $bom_material->specification = $getMaterial['specifications'];
                                $bom_material->denominator_amount = $v['denominator_amount'];
                                $bom_material->numerator_amount = $v['numerator_amount'];
                                $bom_material->unit = $getMaterial['basic_unit'];
                                $bom_material->remark = $v['remark'];
                                $bom_material->relative = 0;
                                $bom_material->save();
                            }
                        }
                    }

                }else{
                    $res = false;
                }
            }

        }else{
            $res = false;
        }
        if($res){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }

    }

    public static function getChangeMaterial($params){
        $where = 'relative = 1';
        $whereSub = 'relative = 0';
        if(isset($params['change_material'])){
            $where .=" AND material_sn = '".$params['change_material']."' AND project_id=".$params['project_id']."";
        }
        if(isset($params['get_bom_id']) && isset($params['get_version'])){
            unset($where);
            $where = 'relative = 1';
            $where .=" AND bom_id = '".$params['get_bom_id']."' AND project_id=".$params['project_id']."";
        }
        $change_bom = Db::name('bom_material')
            ->field('bom_id,material_name,material_sn,specification,unit')
            ->where($where)
            ->find();
        if(!empty($change_bom)) {
            if (isset($params['get_bom_id']) && isset($params['get_version'])) {
                $change_bom['version'] = $params['get_version'];
                $whereSub .=" AND bom_id = ".$params['get_bom_id'];
            } else {
                $ProjectBomVersion = Db::name('project_bom')->field('version')->where('bom_id = ' . $change_bom['bom_id'])->find();
                $change_bom['version'] = $ProjectBomVersion['version'];
                $whereSub .=" AND bom_id = ".$change_bom['bom_id'];
            }

            $sub_material = Db::name('bom_material')
                ->field('material_name,material_sn,specification,numerator_amount,denominator_amount,unit,remark')
                ->where($whereSub)
                ->select();
            if(!empty($sub_material)){
                $change_bom['sub_material'] = $sub_material;
                return $change_bom;
            }else{
                $change_bom['sub_material'] = array();
                return $change_bom;
            }

        }
    }

    public static function changeMaterialLastInfo($params){
        $where = 'relative = 1';
        $whereSub = 'relative = 0';
        if(isset($params['change_material'])){
            $where .=" AND material_sn = '".$params['change_material']."' AND project_id=".$params['project_id']."";
        }
        if(isset($params['get_bom_id']) && isset($params['get_version'])){
            unset($where);
            $where = 'relative = 1';
            $where .=" AND bom_id = '".$params['get_bom_id']."' AND project_id=".$params['project_id']."";
        }
        $change_bom = Db::name('bom_material')
            ->field('bom_id,material_name,material_sn,specification,unit')
            ->where($where)
            ->find();
        if(!empty($change_bom)) {
            if (isset($params['get_bom_id']) && isset($params['get_version'])) {
                $change_bom['version'] = $params['get_version'];
                $whereSub .=" AND bom_id = ".$params['get_bom_id'];
            } else {
                $ProjectBomVersion = Db::name('project_bom')->field('version')->where('bom_id = ' . $change_bom['bom_id'])->find();
                $change_bom['version'] = $ProjectBomVersion['version'];
                $whereSub .=" AND bom_id = ".$change_bom['bom_id'];
            }

            $sub_material = Db::name('bom_material')
                ->field('material_name,material_sn,specification,numerator_amount,denominator_amount,unit,remark')
                ->where($whereSub)
                ->select();
            if(!empty($sub_material)){
                $change_bom['sub_material'] = $sub_material;
                return $change_bom;
            }else{
                $change_bom['sub_material'] = array();
                return $change_bom;
            }

        }
    }

    public static function getMaterial($params){
        $method = is_array($params['material_sn']) ? 'select' : 'find';
        $getMaterial = Db::name('material')
            ->alias("m")
            ->join("resource r",'m.material_code = r.material_code')
            ->where("m.status = 1 AND r.resource_type = 1 AND r.year = '".date('Y',time())."'")
            ->where('m.material_code', 'in', (array)$params['material_sn'])
            ->$method();

        return $getMaterial;
    }

    /**
     * 工程变更审核
     * @param $params array
     * @return array|bool|int|string
     */
    public static function accraditation($params){
        if(isset($params['ecn_id']) && !empty($params['admin_id'])){
            Db::startTrans();
            try {
                $data = [
                    'audit_status' => $params['audit_status'],
                    'audit_note' => empty($params['audit_note']) ? '' : $params['audit_note'],
                    'audit_time' => time(),
                ];

                if (!empty($params['material_sn'])) {
                    $bom_id = Db::name('project_ecn')
                        ->alias('pe')
                        ->field('pb.bom_id,pb.project_id')
                        ->join("project_bom pb", 'pe.project_id = pb.project_id AND pe.project_node = pb.node_id AND pb.project_type=2')
//                        ->where("ecn_id = " . $params['ecn_id'] . " AND pb.version = (select max(version) from plm_project_bom)")
                        ->where('pe.ecn_id',$params['ecn_id'])
                        ->order('pb.bom_id desc')
                        ->find();
                    if($bom_id['bom_id']){
                        $bomData = $data;
                        $bomData['mg_id'] = $params['mg_id'];
                        //修改project_bom表的审核状态
                        Db::name('project_bom')->where("bom_id = ".$bom_id['bom_id'])->update($bomData);
                        //保存审核时填入父项物料
                        $material = Db::name('material')->where("status = 1 AND material_code = '" . $params['material_sn'] . "'")->find();
                        $bomMaterial = new BomMaterial();
                        $bomMaterial->bom_id = $bom_id['bom_id'];
                        $bomMaterial->project_id = $bom_id['project_id'];//项目id(唯一的）
                        $bomMaterial->material_name = $material['material_name'];
                        $bomMaterial->material_sn = $material['material_code'];
                        $bomMaterial->specification = $material['specifications'];
                        $bomMaterial->unit = $material['basic_unit'];
                        $bomMaterial->remark = '';
                        $bomMaterial->relative = 1;
                        $bomMaterial->save();
                        Db::name('project_ecn')->where("ecn_id = ".$params['ecn_id'])->update(['audit_status'=>$params['audit_status'],'audit_time'=>time()]);
                    }
                }
                $where = "target_type = 5 AND target_id = " . $params['ecn_id'] . " AND admin_id = " . $params['admin_id'];
                $accraditation = Db::name('relation')->where($where)->update($data);

                if($accraditation){
                    if(!empty($params['material_sn'])){
                        if (!empty($bom_id['bom_id'])) {
                            ProjectBom::saveErpBom($bom_id['bom_id']);
                        }
                        Db::commit();
                        return $accraditation;
                    }

                    if($params['audit_status']==-1){
                        Db::name('project_ecn')->where("ecn_id = ".$params['ecn_id'])->update(['audit_status'=>$params['audit_status'],'audit_time'=>time()]);
                    }else{
                        //工程变更全部审核通过状态
                        $has_not_audit = Db::name('relation')->where([
                            'target_type' => 5,
                            'target_id' => $params['ecn_id'],
                            'audit_status' => 0
                        ])->count();

                        if (!$has_not_audit) {
                            Db::name('project_ecn')->where("ecn_id = ".$params['ecn_id'])->update(['audit_status'=>$params['audit_status'],'audit_time'=>time()]);
                            $project_ecn_info = Db::name('project_ecn')->where("ecn_id = ".$params['ecn_id'])->find();
                            $where = "project_id = ".$project_ecn_info['project_id']." AND project_type = 2 AND node_id = ".$project_ecn_info['project_node']." AND tpl_id = ".$project_ecn_info['change_doc'];
                            Db::name('document')->where($where)->update(['audit_status'=>$params['audit_status']]);
                        }
                    }
                    Db::commit();
                    return $accraditation;
                }else{
                    Db::rollback();
                    return false;
                }
            }catch (\Exception $e) {
                return false;
            }
        }
        return array();
    }

    public static function isRelation($params){
        $is_check = 0;
        //校验是否在变更审核人ID组里
        $isRelation = Db::name('relation')->field('admin_id')->where('target_id = '.$params['ecn_id'].' AND target_type = 5')->select();
        foreach($isRelation as $value){
            if($value['admin_id'] == $params['admin_id']){
                $is_check = 1;
            }
        }
//        $result = Db::name('project_ecn')->field('final_auditor')->where('ecn_id = '.$params['ecn_id'])->find();
//        if($result['final_auditor'] == $params['admin_id']){
//            $is_check = 1;
//        }
        return $is_check;
    }
}