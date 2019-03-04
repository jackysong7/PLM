<?php
/**
 * 成品搜索
 * Created by PhpStorm.
 * User: liujun
 * Date: 2018/4/10
 * Time: 15:33
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Controller;
use think\Validate;

class Search extends Api
{
    /**获取筛选条件信息
     * @return array|void
     */
    public function getCondition()
    {
//        $jsonData = $this->request->param();
//        $data = json_decode($jsonData['data'],true);
//
//        $rule = [
//            'plm_no'  => 'require'
//        ];
//        $msg = [
//            'plm_no.require' => 'PLM物料编码不能为空！'
//        ];
//        $this->validate = new Validate($rule, $msg);
//        $result = $this->validate->check($data);
//        if (!$result)
//        {
//            return $this->returnmsg(401,$this->validate->getError(),"");
//        }
        $row = \app\api\model\Search::getCondition();

        return $this->returnmsg(200,'success!',$row);
    }

    /**
     * 搜索PLM产品列表信息
     */
    public function conditionSearch()
    {
        $jsonData = $this->request->param();
        $data = json_decode($jsonData['data'],true);

        $data['page_no'] = empty($data['page_no']) ? 1 : $data['page_no'];
        $data['page_size'] = empty($data['page_size']) ? 10 : $data['page_size'];
        $row = \app\api\model\Search::conditionSearch($data);
        //print_r($row);
        return $this->returnmsg(200,'success!',$row);
    }
}