<?php
/**
 * Created by PhpStorm.
 * User: chenhailiang
 * Date: 2018/4/2
 * Time: 14:53
 */
namespace app\api\controller\v1;

use app\api\controller\Api;
use think\Controller;

class Index extends Api
{
    /*
     * 获取列表
     */
    public function getInfo()
    {
        $result = \app\api\model\Index::getInfo();
        $this->returnmsg(200,'操作完成', $result);
    }


}