<?php
/**
 * PLM项目的异步任务，主要处理一些比较耗时的任务
 */

namespace app\cli\controller;

use app\api\model\Project;
use think\Validate;

class PlmAsync
{
    
    function __construct() {
        
        if (php_sapi_name() !== 'cli')
        {
            die('错误的执行方式');
        }
    }
    
    /**
     * worker监听函数
     */
    public function listen()
    {
        $config = \think\Config::get('gearman');
        $worker = new \GearmanWorker();
        $worker->addServer($config['host'], $config['port']);
        
        $worker->addFunction("plmRecomputeFinishDate", [$this, "recomputeFinishDate"]);
        $worker->addFunction("resourceUpload", [$this, "resourceUpload"]);
        while ($worker->work());
    }
    
    /**
     * 重新计算项目节点的完成时间
     * @param type $job
     * @return type
     */
    function recomputeFinishDate($job)
    {
        $str = $job->workload();
        $jobData = @json_decode($str, true);
        var_dump($jobData);
        if (!isset($jobData['project_id']) || $jobData['project_id'] < 1)
        {
            return;
        }
        $update_front_data = isset($jobData['update_front_data']) ? $jobData['update_front_data'] : true;
        Project::recomputeFinishDate($jobData['project_id'], $update_front_data);
    }

    public function resourceUpload($job)
    {
        $str = $job->workload();
        $jobData = @json_decode($str, true);
        var_dump($jobData);

        $rule = [
            'year' => 'require|integer',
        ];
        if (!((new Validate($rule))->check($jobData)))
            return;

        \app\api\model\Resource::upload($jobData);
    }
}

