<?php

namespace app\api\model;

use think\exception\DbException;
use think\Model;

class ProjectTpl extends Model
{
    /**
     * 根据模板ID获取流程图
     * @param $tplId
     * @return array|mixed|string
     * @throws DbException
     */
    public static function getFlowChartTpl($tplId)
    {
        $frontEndData = self::where(['tpl_id' => $tplId])->value('front_end_data');
        return $frontEndData ? \app\api\model\Project::getFrontEndDataDetail($frontEndData) : [];
    }

    /**
     * 将流程图保存为模板
     * @param $data
     * @return mixed
     */
    public static function saveAsTemplate($data)
    {
        $projectTpl = new ProjectTpl();
        $projectTpl->tpl_name = $data['project_name'];
        $projectTpl->front_end_data = json_encode($data);
        $projectTpl->add_time = time();
        $projectTpl->save();
        return $projectTpl->tpl_id;
    }

    /**
     * 流程图模板列表
     * @param $data
     * @throws DbException
     */
    public static function getList($data)
    {
        if (!isset($data['page_no'])) $data['page_no'] = 1;
        if (!isset($data['page_size'])) $data['page_size'] = 10;

        $where = ['status' => ['<>', 3]];

        $totalNumber = self::where($where)->count();
        $list = self::field('tpl_id,tpl_name')->where($where)->page($data['page_no'], $data['page_size'])->select();

        return [
            'totalNumber' => $totalNumber,
            'list' => $list,
        ];
    }

    /**
     * 删除流程图模板
     * @param $tplId
     */
    public static function delFlowChartTpl($tplId)
    {
        self::update(['status' => 3], ['tpl_id' => $tplId]);
    }
}