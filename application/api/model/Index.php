<?php
/**
 * Created by PhpStorm.
 * User: chenhailiang
 * Date: 2018/4/16
 * Time: 15:08
 */
namespace app\api\model;

use think\Db;
use think\Model;

class Index extends Model
{
    public static function getInfo(){

        $sum = 0;
        $data = $status = [];
        $goodscategory = Db::name('goods_category')
                ->where('parent_id = 0 AND status = 1')
                ->select();
        if(!empty($goodscategory)){
            foreach($goodscategory as $k=>$v){
                $count = Db::name('product_attribute')
                    ->where('category_one_id = '.$v['gc_id'])
                    ->count();

                $data[] = [
                    'gc_name'=>$v['gc_name'],
                    'count'=>$count
                ];
                $sum += $count;
            }
        }

        $salesStatus = Db::name('sales_status')
            ->where('status = 1')
            ->select();
        if(!empty($salesStatus)){
            foreach($salesStatus as $k=>$v){
                $count = Db::name('product_attribute')
                    ->where('sales_status_id = '.$v['ss_id'])
                    ->count();

                $status[] = [
                    'ss_name'=>$v['ss_name'],
                    'count'=>$count
                ];
            }
        }

        $result = [
            'list'=>$data,
            'sum' =>$sum,
            'status'=>$status
        ];

        return $result;
    }
}