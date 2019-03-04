<?php

namespace app\api\model;

use app\common\library\Auth;
use app\common\library\GearmanAsync;
use think\Exception;
use think\exception\DbException;
use think\Model;
use think\Db;

class Project extends Model
{
    //已退市
    const EXIT_LISTED = -3;
    //停止
    const STOPPED = -2;
    //暂停
    const PAUSED = -1;
    //未开始
    const NOT_STARTED = 0;
    //开发在途
    const DEV = 1;
    //已上市
    const LISTED = 2;

    /**
     * 查询frontEndData详情
     * @param string $frontEndData json数据
     * @throws DbException
     */
    public static function getFrontEndDataDetail($frontEndData, $projectId = '')
    {
        $frontEndData = json_decode($frontEndData, true);

        $admin = new Admin();
        $docTemplate = new DocTemplate();

        $adminField = 'admin_id,nickname';
        $docTemplateField = 'tpl_id,tpl_name';

        $frontEndData['product_manager'] = $admin->field($adminField)
            ->select($frontEndData['product_manager']);

        $frontEndData['project_manager'] = $admin->field($adminField)
            ->select($frontEndData['project_manager']);

        $frontEndData['product_committee'] = $admin->field($adminField)
            ->select($frontEndData['product_committee']);

        //ERP物料编码
        $map['plm_no'] = $frontEndData['material_sn'];
        $erpData = ErpAttribute::materialData($map, 'erp_no');
        $frontEndData['erp_material_sn'] = $erpData['erp_no'] ? $erpData['erp_no'] : '';

        foreach ($frontEndData['flow_chart_data']['nodeInfoArray'] as $k => &$nodeData) {
            //查询节点状态
            if ($projectId) {
                $where['node_key'] = $k;
                $where['project_id'] = $projectId;
                $field = 'status';
                $status = ProjectNode::getProjectNodeInfo($where, $field);
                $nodeData['status'] = $status['status'];
            }
            if (isset($nodeData['uploader'])) {
                $nodeData['uploader'] = $admin->field($adminField)->select($nodeData['uploader']);
            }

            if (isset($nodeData['auditor'])) {
                $nodeData['auditor'] = $admin->field($adminField)->select($nodeData['auditor']);
            }

            if (isset($nodeData['output_doc'])) {
                $nodeData['output_doc'] = $docTemplate->field($docTemplateField)->select($nodeData['output_doc']);
            }
        }

        return $frontEndData;
    }

    /**
     * 获取项目详情
     * @throws DbException
     */
    public function getProjectDetail()
    {
        $projectNode = new ProjectNode();
        $admin = new Admin();
        $dir = new Dir();
        $docTemplate = new DocTemplate();

        $adminField = 'admin_id,nickname';
        $docTemplateField = 'tpl_id,tpl_name';

        $frontEndData = json_decode($this->front_end_data, true);

        $frontEndData['product_manager'] = $admin->field($adminField)
            ->select($frontEndData['product_manager']);

        $frontEndData['project_manager'] = $admin->field($adminField)
            ->select($frontEndData['project_manager']);

        /** @var ProjectNode[] $projectNodes */
        $projectNodes = $projectNode->where([
            'status' => ProjectNode::STATUS_STARTED,
            'project_id' => $this->project_id,
            ])->select();

        $currentNode = [];
        $nodeInfoArray = $frontEndData['flow_chart_data']['nodeInfoArray'];
        foreach ($projectNodes as &$projectNode) {
            $nodeData = isset($nodeInfoArray[$projectNode->node_key]) ? $nodeInfoArray[$projectNode->node_key] : null;
            $plmDirName = $dir->where('plm_dir_id', $projectNode->process_id)->value('plm_dir_name');

            $currentNodeData = [
                'project_id' => $this->project_id,
                'project_name' => $this->project_name,
                'node_id' => $projectNode->node_id,
                'node_name' => $plmDirName,
                'version' => $projectNode->version,
                'status' => $projectNode->status,
                'start_time' => $projectNode->start_time,
                'end_time' => getWorkTime($projectNode->actual_start_time, $projectNode->days),
                'actual_start_time' => $projectNode->actual_start_time,
                'actual_end_time' => 0,
            ];

            if (isset($nodeData['uploader'])) {
                $currentNodeData['uploader'] = $admin->field($adminField)->select($nodeData['uploader']);
            }

            if (isset($nodeData['auditor'])) {
                $currentNodeData['auditor'] = $admin->field($adminField)->select($nodeData['auditor']);
            }

            if (isset($nodeData['output_doc'])) {
                $currentNodeData['output_doc'] = $docTemplate->field($docTemplateField)->select($nodeData['output_doc']);
            }

            $currentNode[] = $currentNodeData;
        }

        return [
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'start_time' => $this->start_time,
            'end_time' => $this->calculateProjectTime('start_project'),
            'actual_start_time' => $this->actual_start_time,
            'actual_end_time' => $this->actual_end_time,
            'is_plan' => $this->is_plan,
            'pp_id' => $this->pp_id,
            'add_time' => $this->add_time,
            'product_manager' => $frontEndData['product_manager'],
            'project_manager' => $frontEndData['project_manager'],
            'current_node' => $currentNode,
        ];
    }

    /*
     * 通过节点id获取项目信息表的数据
     */
    public static function getInfo($gc_id)
    {
        $projectInfo = Db::name('project')
            ->alias('p')
            ->field('p.project_id,p.status,p.start_time,p.end_time,p.actual_start_time,p.actual_end_time,d.gc_name')
            ->join('goods_category d', 'p.category_id = d.gc_id', 'LEFT')
           // ->join('relation r','p.project_id = r.target_id','LEFT')
            ->where('p.category_id',$gc_id)
            ->find();


        return $projectInfo;
    }

    /*
     * 通过gc_id查找project表的主键值（project_id)
     */
    public static function getProjectId($gc_id)
    {
        $projectInfo = Db::name('project')
            ->field('project_id')
            ->where('category_id',$gc_id)
            ->find();

        return $projectInfo;
    }

    /*
     * 通过project_id获取流程图
     */
    public static function flowchartByPid($project_id)
    {
        $flowchartList = Db::name('project')->field('front_end_data')->where('project_id',$project_id)->find();


        return $flowchartList;
    }

    /**
     * 获取某条数据信息
     */
    public static function getProjectInfo($condition,$field = '*')
    {
        return Db::table('plm_project')->where($condition)->field($field)->select();
    }

    /**
     * 获去项目状态
     */
    public static function getProjectStatus($map)
    {
        return Db::table('plm_project')->where($map)->value('status');
    }


    /**
     * 新建项目
     * @param $data
     * @return mixed
     */
    public static function newProject($data)
    {
        if (self::where('project_name', $data['project_name'])->count()) {
            throw new Exception('项目名称已存在');
        }
        if (Basedata::where('plm_no', $data['material_sn'])->count()) {
            throw new Exception('PLM物料编码已存在');
        }

        $current_time = time();
        $nodeList = self::nodeList($data['flow_chart_data']);

        //获取admin_id
        $adminId = Auth::instance()->getUser()['admin_id'];

        //保存项目
        $project = new Project();
        $project->project_name = $data['project_name'];
        $project->category_id = $data['category_id'];
        $project->is_plan = $data['is_plan'];
        $project->front_end_data = json_encode($data);
        $project->pp_id = isset($data['pp_id']) ? $data['pp_id'] : '';
        $project->add_time = $current_time;
        //开始节点的完成日期，即项目的计划开始时间
        $project->start_time = $nodeList['start']['finish_date'];
        $project->creator_id = $adminId;
        $project->save();

        //保存物料编码
        $basedata = new Basedata();
        $basedata->plm_no = $data['material_sn'];
        $basedata->plm_ttm = date('Y');
        $basedata->admin_id = $adminId;
        $basedata->createtime = $current_time;
        $basedata->project_id = $project->project_id;
        $basedata->save();

        $relation_list = [];

        //保存项目的产品经理
        foreach ($data['product_manager'] as $productManager) {
            $relation = new Relation();
            $relation->target_id = $project->project_id;
            $relation->target_type = Relation::TARGET_TYPE_PROJECT;
            $relation->admin_id = $productManager;
            $relation->role_type = Relation::ROLE_TYPE_PRODUCT_MANAGER;
            $relation_list[] = $relation->toArray();
        }

        //保存项目的项目经理
        foreach ($data['project_manager'] as $projectManager) {
            $relation = new Relation();
            $relation->target_id = $project->project_id;
            $relation->target_type = Relation::TARGET_TYPE_PROJECT;
            $relation->admin_id = $projectManager;
            $relation->role_type = Relation::ROLE_TYPE_PROJECT_MANAGER;
            $relation_list[] = $relation->toArray();
        }

        //保存项目的产品委员会
        foreach ($data['product_committee'] as $productCommittee) {
            $relation = new Relation();
            $relation->target_id = $project->project_id;
            $relation->target_type = Relation::TARGET_TYPE_PROJECT;
            $relation->admin_id = $productCommittee;
            $relation->role_type = Relation::ROLE_TYPE_PRODUCT_COMMITTEE;
            $relation_list[] = $relation->toArray();
        }

        //保存项目节点
        foreach ($nodeList as $key => $item) {
            //节点数据
            $projectNode = new ProjectNode();
            $projectNode->project_id = $project->project_id;
            $projectNode->process_id = isset($item['node_name_id']) ? $item['node_name_id'] : '';
            $projectNode->days = isset($item['days']) ? $item['days'] : '';
            $projectNode->node_key = isset($item['key']) ? $item['key'] : '';
            $projectNode->node_type = isset($item['category']) ? $item['category'] : '';
            $projectNode->end_time = isset($item['finish_date']) ? $item['finish_date'] : '';
            if ($key === 'start') {
                $projectNode->start_time = $projectNode->end_time;
            }
            $projectNode->doc_tpl_ids = isset($item['output_doc']) ? implode(',', $item['output_doc']) : '';
            $projectNode->save();

            //节点上传人员
            if (isset($item['uploader'])) {
                foreach ($item['uploader'] as $uploader) {
                    $relation = new Relation();
                    $relation->target_id = $projectNode->node_id;
                    $relation->target_type = Relation::TARGET_TYPE_PROJECT_NODE;
                    $relation->admin_id = $uploader;
                    $relation->role_type = Relation::ROLE_TYPE_UPLOADER;
                    $relation_list[] = $relation->toArray();
                }
            }

            //节点审核人员
            if (isset($item['auditor'])) {
                foreach ($item['auditor'] as $auditor) {
                    $relation = new Relation();
                    $relation->target_id = $projectNode->node_id;
                    $relation->target_type = Relation::TARGET_TYPE_PROJECT_NODE;
                    $relation->admin_id = $auditor;
                    $relation->role_type = Relation::ROLE_TYPE_AUDITOR;
                    $relation_list[] = $relation->toArray();
                }
            }
        }
        (new Relation)->saveAll($relation_list);

        //保存节点连线
        $line_list = [];
        foreach ($data['flow_chart_data']['linkDataArray'] as $link) {
            $projectLine = new ProjectLine();
            $projectLine->project_id = $project->project_id;
            $projectLine->from = isset($link['from']) ? $link['from'] : '';
            $projectLine->to = isset($link['to']) ? $link['to'] : '';
            $projectLine->text = isset($link['text']) ? $link['text'] : '';
            $line_list[] = $projectLine->toArray();
        }
        (new ProjectLine)->saveAll($line_list);

        $project->sendMessage('new');

        $project->calculateProjectTime();
        
        return $project->project_id;
    }

    /**
     * 节点数据合并
     * @param $flowChartData
     * @return array
     */
    public static function nodeList($flowChartData)
    {
        $nodeList = [];
        $otherList = [];
        foreach ($flowChartData['nodeDataArray'] as $node) {
            $nodeData = isset($flowChartData['nodeInfoArray'][$node['key']]) ?
                $flowChartData['nodeInfoArray'][$node['key']] : [];

            $mergeData = array_merge($node, $nodeData);

            if (in_array($node['category'], ['start', 'end'])) {
                $otherList[$node['category']] = $mergeData;
            } else {
                $nodeList[] = $mergeData;
            }
        }
        $nodeList = ['start' => $otherList['start']] + $nodeList + ['end' => $otherList['end']];
        return $nodeList;
    }

    /**
     * 项目流程图信息
     * @param $projectId
     * @return array|mixed|string
     * @throws DbException
     */
    public static function getFlowChart($projectId)
    {
        $frontEndData = self::where(['project_id' => $projectId])->value('front_end_data');
        $returnData = $frontEndData ? self::getFrontEndDataDetail($frontEndData,$projectId) : [];

        $returnData['status'] = self::getProjectStatus(['project_id' => $projectId]);
        //节点项目的开始时间、结束时间等
        $timeInfo = self::getInfoByProjectId($projectId,'start_time,end_time,actual_start_time,actual_end_time');
        $returnData['start_time'] = $timeInfo['start_time'];
        $returnData['end_time'] = $timeInfo['end_time'];
        $returnData['actual_start_time'] = $timeInfo['actual_start_time'];
        $returnData['actual_end_time'] = $timeInfo['actual_end_time'];
        //统计完成进度
        $doneStatus = self::getInfoByProjectId($projectId,'status');
        if($doneStatus['status'] == self::LISTED )//已经上市
        {
            //计算完成进度
            if($timeInfo['actual_end_time'] <= $timeInfo['end_time'])
            {
                $returnData['progress'] = '100%';
            }
            else
            {
                $day = intval(($timeInfo['actual_end_time'] - $timeInfo['end_time'])/(3600*24));
                $returnData['progress'] = '超时'.$day.'天';
            }
        }
        elseif($doneStatus['status'] == self::DEV)//如果项目在开发中，计算该项目节点的完成进度
        {
            $where['project_id'] = $projectId;
            //统计所有的项目
            $projectCount = self::countProjects($where);
            //统计已结束的所有项目
            $where['status'] = 2;
            $finishCount = self::countProjects($where);
            $returnData['progress'] = round($finishCount/$projectCount*100).'%';
        }
        elseif($doneStatus['status'] == self::NOT_STARTED)//未开始
        {
            $returnData['progress'] = self::NOT_STARTED;
        }else{
            $returnData['progress'] = '-';
        }
        //当前节点信息
        $map['project_id'] = $projectId;
        $map['n.status'] =  [['=',1],['=',3],'or'];
        $returnData['current_node'] = \app\api\model\ProjectNode::getCurrentNode($map, 'n.node_id,n.process_id node_name_id,d.plm_dir_name node_name,n.node_key,n.version,n.status,n.audit_time,n.start_time,n.actual_start_time,n.actual_end_time,n.end_time');

        return $returnData;
    }

    /**
     * 项目列表
     * @param $data
     * @throws DbException
     */
    public static function getList($data)
    {
        if (!isset($data['page_no'])) $data['page_no'] = 1;
        if (!isset($data['page_size'])) $data['page_size'] = 10;

        $where = [];
        if (!empty($data['keyword'])) {
            $where['project_name'] = ['like', "%{$data['keyword']}%"];
        }
        if (isset($data['status'])) {
            $where['status'] = $data['status'];
        }

        $totalNumber = self::where($where)->count();
        $list = [];
        /** @var Project[] $projects */
        $projects = self::where($where)->page($data['page_no'], $data['page_size'])->select();
        foreach ($projects as $project) {
            $list[] = $project->getProjectDetail();
        }

        return [
            'totalNumber' => $totalNumber,
            'list' => $list,
        ];
    }

    /**
     * 根据时间统计项目
     * @param $data
     * @return array
     */
    public static function countByTime($data)
    {
        $project = new Project();
        $projectPlan = new ProjectPlan();
        $projectCount = [];
        $planProjectCount = [];
        $time = strtotime($data['year'] . '0101');
        //type：1 按月份统计，2 按季度统计
        $type = $data['type'] == 1 ? 12 : 4;
        for ($i = 1; $i <= $type; $i++) {
            $time2 = strtotime(12 / $type . 'month', $time);
            $projectCount[] = $project
                ->where('add_time', '>=', $time)
                ->where('add_time', '<', $time2)
                ->where('status', Project::LISTED)
                ->count();
            $planProjectCount[] = $projectPlan
                ->where('add_time', '>=', $time)
                ->where('add_time', '<', $time2)
                ->count();
            $time = $time2;
        }

        return [
            'project' => $projectCount,
            'plan_project' => $planProjectCount
        ];
    }

    /*
     * 根据project_id获取一条信息
     */
    public static function getInfoByProjectId($projectId,$field)
    {
        return Db::name('project')->where('project_id',$projectId)->field($field)->find();
    }

    /*
     *通过project_id统计节点项目
     */
    public static function countProjects($where)
    {
        return Db::name('project_node')->where($where)->count();
    }

    /**
     * 启动项目
     * @param $project_id
     * @throws Exception
     */
    public static function start($project_id)
    {
        $project = self::get($project_id);

        if ($project['status'] != self::NOT_STARTED) return;

        $current_time = time();

        //设置项目的实际开始时间
        $project['actual_start_time'] = $current_time;
        $project['status'] = self::DEV;
        $project->save();

        //设置开始节点状态为已结束
        /** @var ProjectNode $start_node */
        $start_node = ProjectNode::getStartNode($project_id);
        $start_node['status'] = ProjectNode::STATUS_END;
        $start_node->save();

        //设置开始节点下一节点的实际开始时间
        $first_node_ids = ProjectNode::getNextNodeIds($start_node['node_id']);
        ProjectNode::update(['actual_start_time' => $current_time, 'status' => ProjectNode::STATUS_STARTED],
            ['node_id' => ['in', $first_node_ids]]);

        //添加待处理任务
        ProjectMsg::documentUpload($first_node_ids);

        $project->sendMessage('start', compact('first_node_ids'));
    }

    /**
     * 修改项目状态
     * @param $data
     * @throws Exception
     */
    public static function editStatus($data)
    {
        $project = self::get($data['project_id']);

        $current_time = time();

        //暂停项目
        if ($data['status'] == self::PAUSED) {
            //记录暂停时间
            $project->pause_time = $current_time;

            //发短信邮件
            $project->sendMessage('pause');
        }

        //暂停再启动
        if ($project->status == self::PAUSED && $data['status'] == self::DEV) {
            $project->calculateProjectTime('restart_project');

            //发短信邮件
            $project->sendMessage('restart');
        }

        //终止项目
        if ($data['status'] == self::STOPPED) {
            ProjectMsg::finishProjectMsg($project['project_id']);

            $project->sendMessage('stop');
        }

        $project->status = $data['status'];
        $project->save();
    }

    /**
     * 关联项目与ERP物料
     * @param $data
     * @throws Exception
     */
    public static function bindErpMaterial($data)
    {
        $material = Material::where('material_code', $data['erp_material_sn'])->find();
        $plmNo = Basedata::where('project_id', $data['project_id'])->value('plm_no');

        if (!$plmNo) {
            throw new Exception('未找到PLM物料编码');
        }
        if (!$material) {
            throw new Exception('未找到ERP物料编码');
        }

        $erpAttribute = ErpAttribute::where(['plm_no' => $plmNo])->find();
        if ($erpAttribute) {
            $erpAttribute->updatetime = time();
        } else {
            $erpAttribute = new ErpAttribute();
            $erpAttribute->plm_no = $plmNo;
            $erpAttribute->status = 1;
            $erpAttribute->admin_id = Auth::instance()->getUser()['admin_id'];
            $erpAttribute->createtime = time();
        }
        $erpAttribute->erp_no = $material['material_code'];
        $erpAttribute->erp_name = $material['material_name'];
        $erpAttribute->material_attr = $material['specifications'];
        $erpAttribute->basic_unit = $material['basic_unit'];
        $erpAttribute->model_attr = $material['specifications_code'];
        $erpAttribute->material_group = $material['mg_code'];
        $erpAttribute->material_properties = $material['material_attribute'];
        $erpAttribute->save();
    }
    
    /**
     * 重新计算项目节点的计划完成时间
     * 通过节点间的连线不停计算每个节点的完成时间，最后选出最大的一个
     * @param type $project_id 项目ID
     * @param type $update_front_data 是否更新前端流程图数据，即project表的字段front_end_data
     */
    public static function recomputeFinishDate($project_id, $update_front_data = true)
    {
        // 取出项目的所有节点和连线
        $proj_nodes = ProjectNode::getProjectNodes($project_id);
        $proj_lines = ProjectLine::getProjectLines($project_id);
        if (empty($proj_nodes) || empty($proj_lines))
        {
            return;
        }
        // 找出项目的起始节点
        $first_node = ProjectNode::findTheSideNode($proj_nodes);
        if (empty($first_node))
        {
            return;
        }
        // 初始化end_time
        if ($first_node['end_time'] == 0 && $first_node['start_time'] != 0)
        {
            $first_node['end_time'] = $first_node['start_time'];
            ProjectNode::update(['end_time' => $first_node['start_time']], ['node_id' => $first_node['node_id']]);
        }
        // 保存每个节点的完成时间
        $start_time_arr = $end_time_arr = [];
        // 保存无效的连线，即此种连线不作处理
        $invalid_lines = [];
        // 保存已处理过的节点（即计算过完成时间）
        $processed = [$first_node];
        // 不停从队列中取出第一个节点计算下一个节点的完成时间，然后将计算过时间的加入队列以便处理下一个节点
        for( ;count($processed)>0; )
        {
            // 把节点从队列中($processed)取出
            $processed_node = array_shift($processed);
            foreach($proj_lines as $line)
            {
                // 找到当前节点出发的连线
                if ($line['from'] == $processed_node['node_key'])
                {
                    // 判断如果是无效路径则跳过
                    foreach($invalid_lines as $invalid_line)
                    {
                        list($invalid_from, $invalid_to) = $invalid_line;
                        if ($line['from'] == $invalid_from && ($line['to'] == $invalid_to || ProjectLine::reachTo($line['to'], $invalid_to, $proj_lines)))
                        {
                            continue 2;
                        }
                    }
                    // 找到下一个节点，并计算下一个节点的开始和结束时间
                    $to_node = ProjectNode::getNodeByKey($line['to'], $proj_nodes);
                    if (empty($to_node))
                    {
                        continue;
                    }
                    $to_node['start_time'] = $processed_node['end_time'];
                    $to_node['end_time'] = getWorkTime($processed_node['end_time'], $to_node['days']);
                    // 将下一个节点加入队列($processed)
                    if (!in_array($to_node['node_type'], ['start', 'end']))
                    {
                        array_push($processed, $to_node);
                    }
                }
                // 记录返回（无效）的路径
                if ($line['from'] == $processed_node['node_key'] && ProjectLine::reachTo($line['to'], $line['from'], $proj_lines))
                {
                    array_push($invalid_lines, [$line['to'], $line['from']]);
                }
                // 记录有效的时间
                if ($line['from'] == $processed_node['node_key'])
                {
                    !isset($start_time_arr[$to_node['node_key']]) && $start_time_arr[$to_node['node_key']] = [];
                    !isset($end_time_arr[$to_node['node_key']]) && $end_time_arr[$to_node['node_key']] = [];
                    // 暂存下一个节点的开始和结束时间
                    $start_time_arr[$to_node['node_key']][] = $to_node['start_time'];
                    $end_time_arr[$to_node['node_key']][] = $to_node['end_time'];
                }
            }
        }
        if (empty($end_time_arr))
        {
            return;
        }
        // 找出项目的结束节点，再找出结束结点的上一级节点，此节点的最大时间即为项目的结束时间
        $end_node = ProjectNode::findTheSideNode($proj_nodes, false);
        if (!empty($end_node))
        {
            $last_nodes = ProjectLine::getNodeFrom($project_id, $end_node['node_key']);
            $last_nodes_keys = empty($last_nodes) ? [] : array_column($last_nodes, 'from');
        } else {
            $last_nodes_keys = [];
        }
        $proj_end_time = [];
        // 取出前端流程图数据
        $front_end_data = @json_decode(self::getInfoByProjectId($project_id, 'front_end_data')['front_end_data'], true);
        // 取出最大值并保存到数据库
        foreach($end_time_arr as $node_key => $time_arr)
        {
            if (empty($time_arr))
            {
                continue;
            }
            $max_time = max($time_arr);
            $max_key = array_search($max_time, $time_arr);
            if (!empty($last_nodes_keys) && in_array($node_key, $last_nodes_keys))
            {
                $proj_end_time[] = $max_time;
            }
            $data = ['end_time' => $max_time];
            if (isset($start_time_arr[$node_key][$max_key]))
            {
                $data['start_time'] = $start_time_arr[$node_key][$max_key];
            }
            if (isset($front_end_data['flow_chart_data']) && isset($front_end_data['flow_chart_data']['nodeInfoArray']) && isset($front_end_data['flow_chart_data']['nodeInfoArray'][$node_key]))
            {
                $front_end_data['flow_chart_data']['nodeInfoArray'][$node_key]['finish_date'] = $max_time;
            }
            ProjectNode::update($data, ['project_id' => $project_id, 'node_key' => $node_key]);
        }
        // 更新项目结束时间
        $proj_data = [];
        if (!empty($proj_end_time))
        {
            $proj_data['end_time'] = max($proj_end_time);
        }
        if ($update_front_data)
        {
            $proj_data['front_end_data'] = @json_encode($front_end_data);
        }
        if (!empty($proj_data))
        {
            self::update($proj_data, ['project_id' => $project_id]);
        }
    }

    /**
     * 保存修改的项目流程图前端数据
     */
    public static function saveFrontEndData($front_end_data,$project_id)
    {
        if($project_id)
        {
            //return self::update(['front_end_data'=>@json_encode($front_end_data)],['project_id'=>$project_id]);
            return Db::name('project')->where(['project_id'=>$project_id])->setField(['front_end_data'=>@json_encode($front_end_data)]);
        }
        return false;
    }

    public static function updateInfo($where,$setData)
    {
        return Db::name('project')->where($where)->update($setData);
    }

    /**
     * 项目已暂停,无法操作
     * - 不能上传文档
     * - 不能审核文档
     * - 不能转至下一流程
     * @param $project_id
     */
    public static function checkProjectPause($project_id)
    {
        $text = [
            self::EXIT_LISTED => '已退市',
            self::STOPPED => '已停止',
            self::PAUSED => '已暂停',
            self::NOT_STARTED => '未开始',
            self::LISTED => '已上市',
        ];
        $status = self::where('project_id', $project_id)->value('status');
        return $status != self::DEV ? '项目' . $text[$status] . ',无法操作' : false;
    }

    /**
     * 获取项目经理
     * @param $project_id
     * @return array
     */
    public static function getProjectManager($project_id)
    {
        return Relation::where(['target_id' => $project_id, 'target_type' => Relation::TARGET_TYPE_PROJECT, 'role_type' => Relation::ROLE_TYPE_PROJECT_MANAGER])->column('admin_id');
    }

    /**
     * 获取产品经理
     * @param $project_id
     * @return array
     */
    public static function getProductManager($project_id)
    {
        return Relation::where(['target_id' => $project_id, 'target_type' => Relation::TARGET_TYPE_PROJECT, 'role_type' => Relation::ROLE_TYPE_PRODUCT_MANAGER])->column('admin_id');
    }

    /**
     * 获取产品委员会
     * @param $project_id
     * @return array
     */
    public static function getProductCommittee($project_id)
    {
        return Relation::where(['target_id' => $project_id, 'target_type' => Relation::TARGET_TYPE_PROJECT, 'role_type' => Relation::ROLE_TYPE_PRODUCT_COMMITTEE])->column('admin_id');
    }

    /**
     * 获取项目所有相关人员id
     * @param $project_id
     * return array
     */
    public static function getProjectUserIds($project_id)
    {
        $admin[] = Project::getProjectManager($project_id);
        $admin[] = Project::getProductManager($project_id);
        $admin[] = Project::getProductCommittee($project_id);
        return array_unique(array_merge(...$admin));
    }

    /**
     * 获取项目用户+项目所有节点用户
     * @param $project_id
     * @return array
     */
    public static function getProjectAllUsers($project_id)
    {
        $project_node_user_ids = ProjectNode::getNodeUserIds(ProjectNode::getProjectNodeIds($project_id));
        $project_user_ids = Project::getProjectUserIds($project_id);
        return userData(array_unique(array_merge($project_node_user_ids, $project_user_ids)));
    }

    /**
     * 发短信邮件
     * @param string $type new,start,pause,restart,stop,overdue
     * @param null $send_message_data
     * @throws DbException
     */
    public function sendMessage($type, $send_message_data = null)
    {
        $current_user = userData(Auth::instance()->getUser()['admin_id'])['nickname'];
        $current_time_text = date('Y-m-d H:i:s', time());
        $project_name = $this['project_name'];
        $project_id = $this['project_id'];

        $messages = [];

        switch ($type) {
            case 'new':
                $all_users = self::getProjectAllUsers($project_id);

                $message = [];
                $message['users'] = $all_users;
                $message['tpl_code'] = 'SMS_142010224';
                $message['tpl_content'] = '${someone}于${time}，创建了新项目${project}，请关注你在项目中的工作';
                $message['tpl_param']['someone'] = $current_user;
                $message['tpl_param']['time'] = $current_time_text;
                $message['tpl_param']['project'] = $project_name;

                $messages[] = $message;
                break;
            case 'start':
                //第一个节点相关人员
                $first_node_users = ProjectNode::getNodeUsers($send_message_data['first_node_ids']);
                //下一节点相关人员
                $next_node_ids = ProjectNode::getNextNodeAllIds($send_message_data['first_node_ids']);
                $next_node_users = ProjectNode::getNodeUsers($next_node_ids);
                //其他节点相关人员+项目相关人员
                $other_node_user_ids = ProjectNode::getNodeUserIds(array_diff(ProjectNode::getProjectNodeIds($project_id), $send_message_data['first_node_ids'], $next_node_ids));
                $project_user_ids = Project::getProjectUserIds($project_id);
                $other_node_users = userData(array_unique(array_merge($other_node_user_ids, $project_user_ids)));

                $messages = [];
                $first_node_message = [];
                $next_node_message = [];
                $other_node_message = [];

                $first_node_message['users'] = $first_node_users;
                $first_node_message['tpl_code'] = 'SMS_142010259';
                $first_node_message['tpl_content'] = '${someone}于${time}，启动了项目${project}，请${operate}你在项目中的工作';
                $first_node_message['tpl_param']['someone'] = $current_user;
                $first_node_message['tpl_param']['time'] = $current_time_text;
                $first_node_message['tpl_param']['project'] = $project_name;
                $first_node_message['tpl_param']['operate'] = '处理';

                $next_node_message['users'] = $next_node_users;
                $next_node_message['tpl_code'] = 'SMS_142020291';
                $next_node_message['tpl_content'] = '${someone}于${time}，启动了项目${project}，请你做好相关准备，相关工作即将开启';
                $next_node_message['tpl_param']['someone'] = $current_user;
                $next_node_message['tpl_param']['time'] = $current_time_text;
                $next_node_message['tpl_param']['project'] = $project_name;

                $other_node_message['users'] = $other_node_users;
                $other_node_message['tpl_code'] = 'SMS_142010259';
                $other_node_message['tpl_content'] = '${someone}于${time}，启动了项目${project}，请${operate}你在项目中的工作';
                $other_node_message['tpl_param']['someone'] = $current_user;
                $other_node_message['tpl_param']['time'] = $current_time_text;
                $other_node_message['tpl_param']['project'] = $project_name;
                $other_node_message['tpl_param']['operate'] = '关注';

                $messages[] = $first_node_message;
                $messages[] = $next_node_message;
                $messages[] = $other_node_message;
                break;
            case 'pause':
                $all_users = self::getProjectAllUsers($project_id);

                $message = [];
                $message['users'] = $all_users;
                $message['tpl_code'] = 'SMS_142000363';
                $message['tpl_content'] = '${someone}于${time}，暂停了项目${project}，请你密切关注该项目状态，持续跟进项目相关内容';
                $message['tpl_param']['someone'] = $current_user;
                $message['tpl_param']['time'] = $current_time_text;
                $message['tpl_param']['project'] = $project_name;

                $messages[] = $message;
                break;
            case 'restart':
                //当前节点相关人员
                $first_node_ids = ProjectNode::getStartedNodeIds($project_id);
                $first_node_users = ProjectNode::getNodeUsers($first_node_ids);
                //下一节点相关人员
                $next_node_ids = ProjectNode::getNextNodeAllIds($first_node_ids);
                $next_node_users = ProjectNode::getNodeUsers($next_node_ids);
                //其他节点相关人员+项目相关人员
                $other_node_user_ids = ProjectNode::getNodeUserIds(array_diff(ProjectNode::getProjectNodeIds($project_id), $first_node_ids, $next_node_ids));
                $project_user_ids = Project::getProjectUserIds($project_id);
                $other_node_users = userData(array_unique(array_merge($other_node_user_ids, $project_user_ids)));

                $messages = [];
                $first_node_message = [];
                $next_node_message = [];
                $other_node_message = [];

                $first_node_message['users'] = $first_node_users;
                $first_node_message['tpl_code'] = 'SMS_142015312';
                $first_node_message['tpl_content'] = '${someone}于${time}，再次启动了项目${project}，请处理你在项目中的工作';
                $first_node_message['tpl_param']['someone'] = $current_user;
                $first_node_message['tpl_param']['time'] = $current_time_text;
                $first_node_message['tpl_param']['project'] = $project_name;

                $next_node_message['users'] = $next_node_users;
                $next_node_message['tpl_code'] = 'SMS_142000383';
                $next_node_message['tpl_content'] = '${someone}于${time}，再次启动了项目${project}，请你做好相关准备，相关工作即将开启';
                $next_node_message['tpl_param']['someone'] = $current_user;
                $next_node_message['tpl_param']['time'] = $current_time_text;
                $next_node_message['tpl_param']['project'] = $project_name;

                $other_node_message['users'] = $other_node_users;
                $other_node_message['tpl_code'] = 'SMS_142015317';
                $other_node_message['tpl_content'] = '${someone}于${time}，再次启动了项目${project}，请${operate}你在项目中的工作';
                $other_node_message['tpl_param']['someone'] = $current_user;
                $other_node_message['tpl_param']['time'] = $current_time_text;
                $other_node_message['tpl_param']['project'] = $project_name;
                $other_node_message['tpl_param']['operate'] = '关注';

                $messages[] = $first_node_message;
                $messages[] = $next_node_message;
                $messages[] = $other_node_message;
                break;
            case 'stop':
                $all_users = self::getProjectAllUsers($project_id);

                $message = [];
                $message['users'] = $all_users;
                $message['tpl_code'] = 'SMS_142015306';
                $message['tpl_content'] = '${someone}于${time}，终止了项目${project}，该项目已终止';
                $message['tpl_param']['someone'] = $current_user;
                $message['tpl_param']['time'] = $current_time_text;
                $message['tpl_param']['project'] = $project_name;

                $messages[] = $message;
                break;
            case 'overdue':
                break;
        }

        (new GearmanAsync())->sendMessage($messages);
    }

    /**
     * 计算项目时间
     * @param string $save
     * new_project：新建项目时间计算；
     * restart_project：再启动项目时间计算；
     * start_project：启动项目时间计算；
     * route：获取节点路由；
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function calculateProjectTime($type = 'new_project')
    {
        $all_nodes = ProjectNode::where(['project_id' => $this->project_id])->select();
        if ($type == 'restart_project') {
            $current_time = time();
            $time_diff = floor(($current_time - $this->pause_time) / 86400) * 86400;
            if ($time_diff == 0) {
                return null;
            }
        }

        $lines_map_to = [];
        $lines_map_from = [];
        foreach (ProjectLine::getProjectLines($this->project_id) as $line) {
            $lines_map_to[$line['from']][] = $line['to'];
            $lines_map_from[$line['to']][] = $line['from'];
        }

        $nodes_map = [];
        $from_nodes = [];
        $route = $type == 'restart_project' ? $this->calculateProjectTime('route') : [];
        $node_times = [];
        foreach ($all_nodes as $node) {
            $nodes_map[$node['node_key']] = $node;
            if (($type == 'new_project' || $type == 'route') && $node['node_type'] == 'start') {
                $from_nodes[$node['node_key']] = $node;
                $route[$node['node_key']] = [];
                $node_times[$node['node_key']]['start_time'] = $node['start_time'];
                $node_times[$node['node_key']]['end_time'] = $node['end_time'];
            }
            if ($type == 'restart_project' && $node['status'] == ProjectNode::STATUS_STARTED) {
                $node['end_time'] += $time_diff;
                $node->save();
                $from_nodes[$node['node_key']] = $node;
                $node_times[$node['node_key']]['start_time'] = $node['start_time'];
                $node_times[$node['node_key']]['end_time'] = $node['end_time'];
            }
            if ($type == 'start_project' && $node['node_type'] == 'start') {
                $node['end_time'] = $this->actual_start_time;
                $from_nodes[$node['node_key']] = $node;
                $route[$node['node_key']] = [];
                $node_times[$node['node_key']]['start_time'] = $node['start_time'];
                $node_times[$node['node_key']]['end_time'] = $node['end_time'];
            }
        }

        $project_end_time = [];
        do {
            $next_from_nodes = [];
            foreach ($from_nodes as $node) {
                if (!isset($lines_map_to[$node['node_key']])) continue;
                $to_node_keys = $lines_map_to[$node['node_key']];
                $node_route = $route[$node['node_key']];
                $next_node_route = $node_route;
                $next_node_route[] = $node['node_key'];
                foreach ($to_node_keys as $to_node_key) {
                    if (in_array($to_node_key, $node_route)) {
                        continue;
                    }

                    if (isset($route[$to_node_key])) {
                        $route[$to_node_key] = array_unique(array_merge($route[$to_node_key], $next_node_route));
                    } else {
                        $route[$to_node_key] = $next_node_route;
                    }

                    if (isset($node_times[$to_node_key])) {
                        continue;
                    }

                    $from_node_keys = $lines_map_from[$to_node_key];
                    foreach ($from_node_keys as $from_node_key) {
                        if (!isset($node_times[$from_node_key])) {
                            continue 2;
                        }
                    }

                    $end_time = [];
                    foreach ($from_node_keys as $from_node_key) {
                        $end_time[] = $node_times[$from_node_key]['end_time'];
                    }

                    $to_node = $nodes_map[$to_node_key];

                    $node_times[$to_node_key]['start_time']
                        = max($end_time);
                    $node_times[$to_node_key]['end_time']
                        = $project_end_time[]
                        = getWorkTime(max($end_time), $to_node['days']);
                    $next_from_nodes[] = $to_node;
                }
            }
        } while ($from_nodes = $next_from_nodes);

        if ($type == 'new_project' || $type == 'restart_project') {
            $front_end_data = json_decode($this->front_end_data, true);

            foreach ($node_times as $key => $item) {
                $node = $nodes_map[$key];
                $node['start_time'] = $item['start_time'];
                $node['end_time'] = $item['end_time'];
                $node->save();
                if (isset($front_end_data['flow_chart_data']['nodeInfoArray'][$key]['finish_date'])) {
                    $front_end_data['flow_chart_data']['nodeInfoArray'][$key]['finish_date'] = $item['end_time'];
                }
            }

            if ($project_end_time) {
                $this->end_time = max($project_end_time);
            }

            $this->front_end_data = json_encode($front_end_data);
            $this->save();
        }

        if ($type == 'start_project' && $project_end_time) {
            return max($project_end_time);
        }

        return $route;
    }
}