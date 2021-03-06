<?php
namespace app\api\model;

use think\Db;
use think\Model;
use app\common\library\Excel;
class Resource extends Model
{
     protected $table = 'plm_resource_name';
     
     public static function addNode ($params)
    {

        $data = array(
            "year"=>$params['year'],
            "process_id"=>$params['process_id'],
            "type"=>1,
            "status"=> 1
        );
        Db::name('resource_name')->insert($data);
        $id = Db::name('resource_name')->getLastInsID();
        return array("id"=>(int)$id);
    }
    
    public static function addDoc($params)
    {
        $data = array(
            "year"=>$params['year'],
            "process_id"=>$params['process_id'],
            "file_name"=>$params['file_name'],
            "resource_file"=>$params['resource_file'],
            "type"=>2,
            "status"=>1
        );
        Db::name('resource_name')->insert($data);
        $id = Db::name('resource_name')->getLastInsID();
        return array("id"=>(int)$id);
    }
    
    public static function getResource($params,$field = '*')
    {
        $where['id'] = $params['id'];
        $where['status'] = 1;
        $result = Db::table('plm_resource_name')->where($where)->field($field)->find();
        if($result){
            if($result['type'] == 1 ){//标示节点删除节点的时候需要判断【plm_resource_name表是否有值(type等于2,process_id等于节点ID)】
                $where1['type'] = 2;
                $where1['process_id'] = $where['id'];
                $row = Db::table('plm_resource_name')->where($where1)->field($field)->find();
                if($row){
                    return 4;//节点有记录不可删
                }else{
                    return 3;//节点无记录可以删
                }
            }          
            if($result['type'] == 2){
                return 3;//标示是文档可直接删除
            }            
        }else{
            return 2;
        }
    }
    
    public static function deleteResource($params)
    {
        $data['status'] = 3;
        $where['id'] = $params['id'];
        return self::update($data,$where);
    }

    public static function getTree($data, $pId)
    {
        $tree = '';
        foreach($data as $k => $v){
            if($v['parent_id'] == $pId) {
                $childs = self::getTree($data, $v['mg_id']);
                if(!empty($childs)){
                    $v['list'] = $childs;
                }else{
                    $v['list'] = [];
                }
                unset($v['parent_id']);
                $tree[] = $v;
            }
        }
        return $tree;
    }

    public static function getInfo($params)
    {
        $year_where['type'] = 2;
        $year_where['status'] = 1;
        $field_year = 'year';
        //标准资源库年度列表 type = 2
        
        $year_list = Db::table('plm_year')->where($year_where)->field($field_year)->select();
        $year_list = array_column($year_list, 'year');
        
        //物料分组
        
        $mgwhere['type'] = 2;
        $mgwhere['status'] = 1;
        $field_group = 'mg_id,mg_code,mg_name,parent_id';
        $info = Db::table('plm_material_grouping')->where($mgwhere)->field($field_group)->select();
        $material_grouping = self::getTree($info, 0);

        //Bom组
        $bomwhere['type'] = 1;
        $bomwhere['status'] = 1;
        $row = Db::table('plm_material_grouping')->where($bomwhere)->field($field_group)->select();
        $bom_grouping = self::getTree($row, 0);
        //文档列表
        
        $reswhere['status'] = 1;
        $reswhere['year'] = $params['year'];  
        $reswhere['type'] = 1;  
        $field_rescoues = 'id,process_id,file_name,resource_file';
        
        $list = Db::table('plm_resource_name')->where($reswhere)->field($field_rescoues)->select();
        
        //查询文档目录属性名称（资源库名称），根据process_id获取
        $resource_list = array();
        foreach($list as $key=>$v)
        {
            $resource_list[$key]["id"] = $v['id'];
            $resource_list[$key]["plm_dir_name"] = Db::name('dir')->field('plm_dir_name')->where('plm_dir_id', $v['process_id'])->value('plm_dir_name');
            $reswhere['status'] = 1;
            $reswhere['year'] = $params['year'];  
            $reswhere['type'] = 2;
            $reswhere['process_id'] = $v['id'];
            $resource_list[$key]["doc_list"] = Db::table('plm_resource_name')->where($reswhere)->field('id,file_name,resource_file')->select();
        }
        
        $data = [
            'year_list'=>$year_list,
            'material_grouping'=>$material_grouping,
            'bom_grouping'=>$bom_grouping,
            'resource_list' => $resource_list
        ];
        return $data;
    }


    public static function getSettingInfo($params)
    {
        //文档列表

        $reswhere['status'] = 1;
        $reswhere['year'] = $params['year'];
        $reswhere['type'] = 1;
        $field_rescoues = 'id,process_id,file_name,resource_file';

        $list = Db::table('plm_resource_name')->where($reswhere)->field($field_rescoues)->select();

        //查询文档目录属性名称（资源库名称），根据process_id获取
        $resource_list = array();
        foreach($list as $key=>$v)
        {
            $resource_list[$key]["id"] = $v['id'];
            $resource_list[$key]["plm_dir_name"] = Db::name('dir')->field('plm_dir_name')->where('plm_dir_id', $v['process_id'])->value('plm_dir_name');
            $reswhere['status'] = 1;
            $reswhere['year'] = $params['year'];
            $reswhere['type'] = 2;
            $reswhere['process_id'] = $v['id'];
            $resource_list[$key]["doc_list"] = Db::table('plm_resource_name')->where($reswhere)->field('id,file_name,resource_file')->select();
        }

        $data = [
            'resource_list' => $resource_list
        ];
        return $data;
    }

    public static function upload($params)
    {
        @set_time_limit(0);
        $ExcelToArrary = new Excel();

        $pathurl = $params['upload_file'];

        //$res = $ExcelToArrary->read('C:\Users\liujun\Desktop\excel\upload\Book1New.xls');
        $abc = rand(100, 10000);

        if (!is_dir(ROOT_PATH . 'public/upload/'))
        {
            mkdir(ROOT_PATH . 'public/upload/', 0777, true);
        }

        $xsl = trim(strrchr($pathurl, '.'),'.');

        $str = file_put_contents(ROOT_PATH . 'public/upload/' . $abc . '.'.$xsl, file_get_contents($pathurl));
        if ($str) {
            //$path = 'C:\Users\liujun\Desktop\excel\upload\Book1New.xls';

            $path = ROOT_PATH . 'public/upload/' . $abc .'.' .$xsl;
            $res = $ExcelToArrary->read($path);
            unset($res['excelData'][0]);//去掉key = 0 的
//            print_r($res);
//            exit;
            $page = 0;
            $count = 100;
            $total_page = ceil($res['total_count'] / $count);

            $newArray = '';
            $error_array = array();

            if ($params['resource_type'] == 1) { //物料导入
                for ($page = 0; $page < $total_page; $page++) {
                    $newArray = array_slice($res['excelData'], $page * $count, $count);
                    //print_r($newArray);
                    foreach ($newArray as $key => $v) {
                        //查询物料表是否存在记录
                        $v[0] = trim($v[0]);
                        $result = Db::table('plm_material')->where('material_code', $v[0])->field('material_code,material_id')->find();
                        if (empty($result)) {
                            array_push($error_array, $v[0]);
                            continue;
                        } else {
                            $data = array(
                                "year" => $params['year'],
                                "resource_id" => $result['material_id'],
                                "resource_type" => 1,
                                "material_code" => $result['material_code'],
                                "add_time" => time()
                            );

                            $where['resource_type'] = 1;
                            $where['material_code'] = $v[0];
                            $info = Db::table('plm_resource')->where($where)->field('material_code')->find();
                            if (empty($info)) {
                                Db::name('resource')->insert($data);
                            }
                        }
                    }
                }
                @unlink($path);
                return array_unique($error_array);

//            if ($res) {
//                unset($res[0]);//去掉key = 0 的。
//                if ($params['resource_type'] == 1)  //物料导入
//                {
//                    $error_array = array();
//
//                    //Db::startTrans();
//                    //try {
//                        foreach ($res as $key => $v) {
//                            //查询物料表是否存在记录
//                            $result = Db::table('plm_material')->where('material_code', $v[0])->field('*')->find();
//                            if (empty($result)) {
//                                array_push($error_array, $v[0]);
//                                continue;
//                            }
//                            $data = array(
//                                "year" => $params['year'],
//                                "resource_id" => $result['material_id'],
//                                "resource_type" => 1,
//                                "material_code" => $result['material_code'],
//                                "add_time" => time()
//                            );
//
//                            $where['resource_type'] = 1;
//                            $where['material_code'] = $v[0];
//                            $info = Db::table('plm_resource')->where($where)->field('*')->find();
//                            if (empty($info)) {
//                                Db::name('resource')->insert($data);
//                            }
//                        }
//
//                        //Db::commit();
//                    //} catch (\Exception $e) {
//                       // Db::rollback();
//                   // }
//                } else {
//
//                    $error_array = array();
//
//                    Db::startTrans();
//                    try {
//
//                        foreach ($res as $key => $v) {
//                            //查询物料表是否存在记录
//                            $where = "pb.audit_status = 1 AND pb.submit_status = 1 AND bm.material_sn = '" . $v[0] . "'";
//                            $list = Db::name('project_bom')
//                                ->alias('pb')
//                                ->field('bm.bom_id,bm.material_sn')
//                                ->join('bom_material bm', 'pb.bom_id = bm.bom_id', 'LEFT')
//                                ->where($where)
//                                ->find();
//                            //return Db::name('project_bom')->getLastSql();
//
//                            if ($list['material_sn'] == '') {
//                                array_push($error_array, $v[0]);
//                                continue;
//                            }
//                            $data = array(
//                                "year" => $params['year'],
//                                "resource_id" => $list['bom_id'],
//                                "resource_type" => 2,
//                                "material_code" => $list['material_sn'],
//                                "add_time" => time()
//                            );
//
//                            $where1['resource_type'] = 2;
//                            $where1['material_code'] = $v[0];
//                            $info = Db::table('plm_resource')->where($where1)->field('*')->find();
//                            if (empty($info)) {
//                                Db::name('resource')->insert($data);
//                            }
//                        }
//                        Db::commit();
//                    } catch (\Exception $e) {
//                        Db::rollback();
//                    }
//                }
//                @unlink($path);
//                return $error_array;
//            } else {
//                return 2;
//            }

            }else{
                return 'BOM导入';
            }
        }else{
            return 2;
        }
    }

    /*
     * 通过物料编码查找一条记录
     */
    public static function findIdByMaterialCode($materialCode)
    {

        $where['material_code'] = $materialCode;
        return  Db::table('plm_resource')->where($where)->value('id');

    }
}
