<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/4
 * Time: 13:51
 */
namespace app\api\model;

use app\common\library\Auth;
use think\Db;
use think\Exception;
use think\Model;
use think\Config;

class Document extends Model
{
    public static function initConnect(){
        return Db::connect(Config::get('database.center'));
    }

    public static function checkList($data){
        return self::get($data);
    }

    public static function getDirDocList($params)
    {
        //查找PLM的名称，上市时间
        $plm_array = Db::name('basedata')->where('plm_no',$params['plm_no'])->find();
        if($plm_array){
            $plm_array =array("plm_no"=>$plm_array['plm_no'],"plm_ttm"=>$plm_array['plm_ttm']);
        }

        //根据admin_id获取岗位id
        $job_id = Db::name('admin_info')->field('job_id')->where('admin_id', $params['admin_id'])->value('job_id');

        //查找PLM项目文档目录属性列表

        $plm_dir = Db::table('plm_dir')->field('plm_dir_id,plm_dir_name,job_list')->where('status',1)->order('sort')->select();

        if($plm_dir){
            $plm_document = [];
            foreach ($plm_dir as $key=>$v)
            {
                if(in_array($job_id,explode(",",$v['job_list'])) || $params["username"] == "admin") { //这里的job_id 标示的是用admin_id 获取到的岗位的id,超级管理员能查看全部目录结构
                    $where['plm_dir_id'] = $v['plm_dir_id'];
                    $where['status'] = 1;
                    $where['plm_no'] = $params['plm_no'];

                    $res = Db::table('plm_document')->field('pd_id,file_name,file_path,upload_time,admin_id')->where($where)->order('upload_time DESC')->find();

                    if (empty($res)) {
                        $plm_document[$key]['file_name'] = $plm_document[$key]['file_path'] = $plm_document[$key]['upload_time'] = '';
                    } else {
                        $res['download_url'] = self::getDownloadUrl($res['file_path']);
                        $plm_document[$key] = $res;
                    }

                    $plm_document[$key]['plm_dir_id'] = $v['plm_dir_id'];
                    $plm_document[$key]['plm_dir_name'] = $v['plm_dir_name'];

                    $plm_document[$key]['admin_name'] = self::initConnect()->name('admin')->where('admin_id',$res['admin_id'])->value("nickname");  //获取用户昵称
                }
            }
        }

        $plm_document = array_values($plm_document);
        return array("baseData"=>$plm_array,"docList"=>$plm_document);
    }

    public static function getDocList($params)
    {
        $where['plm_no'] = $params['plm_no'];
        $where['plm_dir_id'] = $params['plm_dir_id'];
        $where['status'] = 1;

        $count = Db::name('document')
            ->where($where)
            ->count();//总记录数


        $list = Db::name('document')
            ->field('pd_id,file_name,admin_id,file_path,upload_time')
            ->where($where)->order('upload_time DESC')
            ->page($params['page_no'],$params['page_size'])
            ->select();
        foreach ($list as $key=>$v)
        {
            $list[$key]['admin_name'] = self::initConnect()->name('admin')->where('admin_id',$v['admin_id'])->value("nickname");//获取用户昵称
            $list[$key]['download_url'] = self::getDownloadUrl($v['file_path']);
        }

        return array("totalNumber"=>$count,"list"=>$list);
    }

    public static function deleteDoc($params)
    {
        $where['pd_id'] = $params['pd_id'];
        //$where['admin_id'] = $admin_id;

        $data = array("status"=>3,"admin_id"=>$params['admin_id']);
        $result = Db::name('document')->where($where)->update($data);
        if($result){

            $search['plm_dir_id'] = $params['plm_dir_id'];
            $search['plm_no'] = $params['plm_no'];
            $search['status'] = 1;
            $result1 = Db::table('plm_document')->field('pd_id,file_name,file_path,upload_time')->where($search)->order('upload_time DESC')->find();
            if($result1){
                $result1['admin_name'] = $params['admin_name'];
                return $result1;
            }else{
                return [];
            }
        }else{
            return -1;
        }
    }

    /**
     * 获取某条数据
     */
    public static function getDocumentDataInfo($condition)
    {
        return self::get($condition);
    }

    /**
     * @param $params
     * @return array
     */
    public static function uploadDoc($params)
    {
        $data = array(
            "plm_no" => $params['plm_no'],
            "plm_dir_id" => $params['plm_dir_id'],
            "file_name" => isset($params['file_name']) ? $params['file_name'] : '',
            "file_path" => $params['upload_file'],
            "admin_id" => $params['admin_id'],
            "status" => 1,
            "upload_time" => time(),
            "createtime" => time(),
            "updatetime" => time()
        );

        $project_id = Basedata::where('plm_no', $params['plm_no'])->value('project_id');

        if ($project_id) {
            if ($error = Project::checkProjectPause($project_id)) {
                return $error;
            }

            $project_node = ProjectNode::field('node_id,node_key')->where(['project_id' => $project_id, 'process_id' => $params['plm_dir_id']])->find();
            if ($project_node) {
                $data['node_id'] = $project_node['node_id'];
                $data['project_id'] = $project_id;
                $data['project_node'] = $project_node['node_key'];
            }
        }

        $document = self::create($data);

        return [
            'pd_id' => $document['pd_id'],
            'file_name' => $document['file_name'],
            'file_path' => $document['file_path'],
            'upload_time' => $document['upload_time'],
        ];
    }

    /**
     * 提交自定义文档审核
     * @param $params
     * @return bool|int|string
     */
    public static function edit($params)
    {
        $editResult = self::get(['pd_id'=>$params['doc_id']]);
        if($editResult)
        {
            $where = [
                'pd_id'=>$params['doc_id'],
            ];
            $data = [
                'submit_status' => 1,
                'submit_time'=>time(),
                'audit_status' => 0
            ];
            $result = Db::name('document')->where($where)->update($data);
            //被驳回再重新提交，删除之前的审核记录
            Db::name('relation')->where(['target_id' => $params['doc_id'],'target_type' => 3,'role_type' => 5])->delete();
            return $result;
        }
        return false;
    }

    /*
    * 审核文档
    */
    public static function audit($params, &$error = '')
    {
        $auditResult = self::get(['pd_id'=>$params['doc_id']]);
        if($auditResult)
        {
            if ($auditResult['project_id'] && $error = Project::checkProjectPause($auditResult['project_id'])) {
                return false;
            }

            $where = [
                'pd_id'=>$params['doc_id'],
            ];
            $data = [
                'audit_status' => $params['audit_status'],
                'audit_note' => !empty($params['audit_note'])?($params['audit_note']):'',
                'audit_time' => time()
            ];
            $result = Db::name('document')->where($where)->update($data);
            return $result;
        }
        return false;
    }

    /**
     * 通过doc_id（文档 ID）获取对应信息
     */
    public static function docData($docId)
    {
        $field = 'd.pd_id doc_id,d.admin_id,d.file_name,d.file_path,d.tpl_id,t.tpl_name,d.project_id,p.project_name,d.node_id,d.version,d.createtime add_time,d.audit_time';
        $data = Db::name('document')
            ->alias('d')
            ->field($field)
            ->join('project p','d.project_id=p.project_id','LEFT')
            ->join('doc_template t','d.tpl_id=t.tpl_id','LEFT')
            ->where('d.pd_id',$docId)
            ->where('d.tpl_id','>',0)
            ->find();
        if(!empty($data))
        {
            $creatorInfo = Relation::getNickName($data['admin_id']);//查找自定义文档创建人员
            $data['creator'] = !empty($creatorInfo)?['admin_id'=>$data['admin_id'],'nickname'=>$creatorInfo] : [];
            $data['download_url'] = self::getDownloadUrl($data['file_path']);

            // 节点审核人员
            $where['target_id'] = $data['node_id'];
            $where['target_type'] = 2;
            $where['role_type'] = 5;//审核人员
            $nodeInfo = Relation::auditInfo($where);

            // 文档审核信息
            $map['target_id'] = $data['doc_id'];
            $map['target_type'] = 3;//BOM表类型
            $map['role_type'] = 5;//审核人员
            $auditorDoc = Relation::auditInfo($map);
            if ($nodeInfo != false && $auditorDoc != false) {
                foreach ($nodeInfo['auditor'] as &$item) {
                    foreach($auditorDoc['auditor'] as &$doc_item)
                    {
                        if ($item['admin_id'] == $doc_item['admin_id'])
                        {
                            $item = $doc_item;
                        }
                    }
                }
            }
            $data['auditor'] = $nodeInfo != false ? $nodeInfo['auditor'] : [];
        }

        return $data;
    }

    /**
     * 上传自定义文档
     * @param $data
     * @return array
     * @throws Exception
     */
    public static function uploadDoc2($data)
    {
        $project_node = ProjectNode::field('node_id,node_key,project_id,process_id')->find($data['node_id']);

        $document = Document::where(['node_id' => $data['node_id'], 'tpl_id' => $data['tpl_id']])->order('version desc')->find();
        //被驳回的，再次上传文档时，更改被驳回重新编辑的状态
        if ($document) {
            if($document['audit_status'] == -1) {
                DB::name('document')->where(['pd_id'=> $document['pd_id']])->update(['reedit'=> 1]);
            }
        }

        if (!empty($data['is_new']) || !$document) {
            //新增
            $newDocument = new self();
            if ($project_node) {
                if ($error = Project::checkProjectPause($project_node['project_id'])) {
                    throw new Exception($error);
                }
                $plm_no = Basedata::where('project_id', $project_node['project_id'])->value('plm_no');

                $newDocument->plm_no = $plm_no ? $plm_no : '';
                $newDocument->plm_dir_id = $project_node['process_id'];
                $newDocument->project_id = $project_node['project_id'];
                $newDocument->project_node = $project_node['node_key'];
            }
            $newDocument->file_name = isset($data['file_name']) ? $data['file_name'] : '';
            $newDocument->file_path = isset($data['review_path']) ? $data['review_path'] : '';
            $newDocument->upload_path = $data['file_path'];
            $newDocument->admin_id = Auth::instance()->getUser()['admin_id'];
            $newDocument->status = 1;
            $newDocument->version = $document ? sprintf('%.1f', $document->version + 0.1) : '1.0';
            $newDocument->node_id = $data['node_id'];
            $newDocument->tpl_id = $data['tpl_id'];
            $newDocument->upload_time = time();
            $newDocument->createtime = time();
            $newDocument->save();

            ProjectMsg::documentSubmit($data['node_id'], $newDocument->pd_id, $newDocument->tpl_id);

            return ['doc_id' => $newDocument->pd_id];
        } else {
            //修改
            $document->file_name = isset($data['file_name']) ? $data['file_name'] : '';
            $document->file_path = isset($data['review_path']) ? $data['review_path'] : '';
            $document->upload_path = $data['file_path'];
            $document->node_id = $data['node_id'];
            $document->tpl_id = $data['tpl_id'];
            $document->updatetime = time();
            $document->status = 1;
            $document->save();

            return ['doc_id' => $document->pd_id];
        }
    }

    public static function del($where)
    {
        return Db::name('document')->where($where)->setField('status',3);
    }

    /*
     *设置文档模板为删除状态
     */
    public static function setStatus($docId)
    {
        return Db::name('document')->where('pd_id',$docId)->setField('status',3);
    }

    /*
     * 查询一条记录
     */
    public static function tplInfo($where,$field)
    {
        return Db::name('document')->where($where)->field($field)->order('pd_id desc')->find();
    }

    public static function docAuditInfo($where,$field)
    {
        $info = Db::name('document')->where($where)->field($field)->order('pd_id desc')->select();
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

    public static function docIdData($where,$field)
    {
        return Db::name('document')->where($where)->field($field)->select();
    }

    /**
     * 获取项目文档列表
     * @param $data
     * @link http://192.168.80.240:4999/web/#/5?page_id=217
     */
    public static function getProjDocList($data)
    {
        $base_data = Basedata::field('plm_no,plm_ttm')->where('project_id', $data['project_id'])->find();
        $doc_list = [];

        $node_ids = Document::where(['project_id' => $data['project_id'], 'audit_status' => 1])->column('DISTINCT node_id');
        foreach ($node_ids as $node_id) {
            $document = Document::where(['project_id' => $data['project_id'], 'node_id' => $node_id, 'audit_status' => 1])->order(['version' => 'desc', 'pd_id' => 'desc'])->find();
            $dir = Dir::find(ProjectNode::where('node_id', $node_id)->value('process_id'));
            $doc_template = DocTemplate::find($document['tpl_id']);

            if ($dir && $doc_template) {
                $doc_list[] = [
                    'plm_dir_id' => $dir['plm_dir_id'],
                    'plm_dir_name' => $dir['plm_dir_name'],
                    'doc_id' => $document['pd_id'],
                    'tpl_id' => $doc_template['tpl_id'],
                    'tpl_name' => $doc_template['tpl_name'],
                    'tpl_type' => $doc_template['tpl_type'],
                    'add_time' => $document['createtime'],
                    'version' => 'V' . $document['version'],
                    'creator' => Admin::field('admin_id,nickname')->find($document['admin_id']),
                    'node_id' => $node_id,
                    'file_name' => $document['file_name'],
                    'file_path' => $document['file_path'],
                    'download_url' => self::getDownloadUrl($document['file_path']),
                ];
            }
        }

        $node_ids = ProjectBom::where(['project_id' => $data['project_id'], 'audit_status' => 1])->column('DISTINCT node_id');
        foreach ($node_ids as $node_id) {
            $project_bom = ProjectBom::where(['project_id' => $data['project_id'], 'node_id' => $node_id, 'audit_status' => 1])->order(['version' => 'desc', 'bom_id' => 'desc'])->find();
            $dir = Dir::find(ProjectNode::where('node_id', $node_id)->value('process_id'));
            $doc_template = DocTemplate::find($project_bom['tpl_id']);

            if ($dir && $doc_template) {
                $doc_list[] = [
                    'plm_dir_id' => $dir['plm_dir_id'],
                    'plm_dir_name' => $dir['plm_dir_name'],
                    'doc_id' => $project_bom['bom_id'],
                    'tpl_id' => $doc_template['tpl_id'],
                    'tpl_name' => $doc_template['tpl_name'],
                    'tpl_type' => $doc_template['tpl_type'],
                    'add_time' => $project_bom['add_time'],
                    'version' => 'V' . $project_bom['version'],
                    'creator' => Admin::field('admin_id,nickname')->find($project_bom['creator_id']),
                    'node_id' => $node_id,
                    'file_name' => $doc_template['tpl_name'],
                    'file_path' => '',
                    'download_url' => '',
                ];
            }
        }

        $node_ids = UitableviewDefault::where(['project_id' => $data['project_id'], 'audit_status' => 1])->column('DISTINCT node_id');
        foreach ($node_ids as $node_id) {
            $uitableview_default = UitableviewDefault::where(['project_id' => $data['project_id'], 'node_id' => $node_id, 'audit_status' => 1])->order(['version' => 'desc', 'ud_id' => 'desc'])->find();
            $dir = Dir::find(ProjectNode::where('node_id', $node_id)->value('process_id'));
            $doc_template = DocTemplate::find($uitableview_default['tpl_id']);

            if ($dir && $doc_template) {
                $doc_list[] = [
                    'plm_dir_id' => $dir['plm_dir_id'],
                    'plm_dir_name' => $dir['plm_dir_name'],
                    'doc_id' => $uitableview_default['ud_id'],
                    'tpl_id' => $doc_template['tpl_id'],
                    'tpl_name' => $doc_template['tpl_name'],
                    'tpl_type' => $doc_template['tpl_type'],
                    'add_time' => $uitableview_default['create_at'],
                    'version' => 'V' . $uitableview_default['version'],
                    'creator' => Admin::field('admin_id,nickname')->find($uitableview_default['creator_id']),
                    'node_id' => $node_id,
                    'file_name' => $doc_template['tpl_name'],
                    'file_path' => '',
                    'download_url' => '',
                ];
            }
        }

        return [
            'baseData' => $base_data,
            'docList' => $doc_list,
        ];
    }

    /**
     * 获取项目文档历史
     * @param $data
     * @link http://192.168.80.240:4999/web/#/5?page_id=218
     */
    public static function getProjDocHistory($data)
    {
        if (!isset($data['page_no'])) $data['page_no'] = 1;
        if (!isset($data['page_size'])) $data['page_size'] = 10;

        $node_id = ProjectNode::where(['project_id' => $data['project_id'], 'process_id' => $data['plm_dir_id']])->value('node_id');

        $where = ['project_id' => $data['project_id'], 'node_id' => $node_id, 'audit_status' => 1];

        $return_list = [];
        switch ($data['tpl_type']) {
            case 1:
                $totalNumber = ProjectBom::where($where)->count();
                $list = ProjectBom::where($where)->page($data['page_no'], $data['page_size'])->select();
                foreach ($list as $item) {
                    $doc_template = DocTemplate::find($item['tpl_id']);
                    $return_list[] = [
                        'doc_id' => $item['bom_id'],
                        'tpl_id' => $doc_template['tpl_id'],
                        'tpl_name' => $doc_template['tpl_name'],
                        'tpl_type' => $doc_template['tpl_type'],
                        'add_time' => $item['add_time'],
                        'version' => 'V' . $item['version'],
                        'creator' => Admin::field('admin_id,nickname')->find($item['creator_id']),
                        'file_name' => $doc_template['tpl_name'],
                        'file_path' => '',
                        'download_url' => '',
                        'node_id' => $node_id,
                    ];
                }
                break;
            case 2:
                $totalNumber = UitableviewDefault::where($where)->count();
                $list = UitableviewDefault::where($where)->page($data['page_no'], $data['page_size'])->select();
                foreach ($list as $item) {
                    $doc_template = DocTemplate::find($item['tpl_id']);
                    $return_list[] = [
                        'doc_id' => $item['ud_id'],
                        'tpl_id' => $doc_template['tpl_id'],
                        'tpl_name' => $doc_template['tpl_name'],
                        'tpl_type' => $doc_template['tpl_type'],
                        'add_time' => $item['create_at'],
                        'version' => 'V' . $item['version'],
                        'creator' => Admin::field('admin_id,nickname')->find($item['creator_id']),
                        'file_name' => $doc_template['tpl_name'],
                        'file_path' => '',
                        'download_url' => '',
                        'node_id' => $node_id,
                    ];
                }
                break;
            case 3:
                $totalNumber = Document::where($where)->count();
                $list = Document::where($where)->page($data['page_no'], $data['page_size'])->select();
                foreach ($list as $item) {
                    $doc_template = DocTemplate::find($item['tpl_id']);
                    $return_list[] = [
                        'doc_id' => $item['pd_id'],
                        'tpl_id' => $doc_template['tpl_id'],
                        'tpl_name' => $doc_template['tpl_name'],
                        'tpl_type' => $doc_template['tpl_type'],
                        'add_time' => $item['createtime'],
                        'version' => 'V' . $item['version'],
                        'creator' => Admin::field('admin_id,nickname')->find($item['admin_id']),
                        'file_name' => $item['file_name'],
                        'file_path' => $item['file_path'],
                        'download_url' => self::getDownloadUrl($item['file_path']),
                        'node_id' => $node_id,
                    ];
                }
                break;
        }

        return [
            'totalNumber' => $totalNumber,
            'list' => $return_list,
        ];
    }

    public static function getDownloadUrl($review_path)
    {
        if (Auth::instance()->check('doc/downloadDoc')) {
            return str_replace('review', 'download', $review_path);
        }
        return '';
    }

    /**
     * 搜索文档下载记录
     * @param $data
     * @param $error
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getDownRecord($data)
    {
        $data += [
            'from_time' => '',
            'to_time' => '',
            'operator_name' => '',
            'project_name' => '',
            'page_no' => 1,
            'page_size' => 10,
        ];
        $admin = new Admin();
        $project = new Project();
        $dir = new Dir();
        $document = new Document();
        $document_download_record = new DocumentDownloadRecord;

        $where = [];
        if ($data['from_time']) {
            $where['create_time'] = ['>=', $data['from_time']];
        }
        if ($data['to_time']) {
            $where['create_time'] = ['<=', $data['to_time']];
        }
        if ($data['operator_name']) {
            $admin_id = $admin->where('nickname', $data['operator_name'])->value('admin_id');
            $where['admin_id'] = $admin_id;
        }
        if ($data['project_name']) {
            $project_id = $project->where('project_name', $data['project_name'])->value('project_id');
            $where['project_id'] = $project_id;
        }

        $list = $document_download_record->where($where)->order('id desc')->page($data['page_no'], $data['page_size'])->select();
        $total = $document_download_record->where($where)->count();

        $return_list = [];
        foreach ($list as $item) {
            $return_list[] = [
                'operator_name' => $admin->where('admin_id', $item['admin_id'])->value('nickname'),
                'project_name' => $project->where('project_id', $item['project_id'])->value('project_name'),
                'node_name' => $dir->where('plm_dir_id', $item['plm_dir_id'])->value('plm_dir_name'),
                'doc_name' => $document->where('pd_id', $item['pd_id'])->value('file_name'),
                'add_time' => $item['create_time'],
            ];
        }

        return [
            'list' => $return_list,
            'page' => [
                'total_count' => $total,
                'current_page' => $data['page_no'],
                'page_size' => $data['page_size'],
                'total_page' => ceil($total / $data['page_size']),
            ],
        ];
    }

    /**
     * 添加下载记录
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function addDownRecord($data)
    {
        $doc = (new Document())->find($data['doc_id']);
        DocumentDownloadRecord::create([
            'project_id' => $doc['project_id'],
            'plm_dir_id' => $doc['plm_dir_id'],
            'pd_id' => $data['doc_id'],
            'admin_id' => Auth::instance()->getUser()['admin_id'],
            'create_time' => time(),
        ]);
    }
}