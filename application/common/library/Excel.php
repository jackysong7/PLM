<?php
/**
 * Created by PhpStorm.
 * User: weibx
 * Date: 2018/7/13
 * Time: 13:34
 */

namespace app\common\library;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Excel
{
    protected $dir;

    public function __construct() {
        $this->dir = ROOT_PATH . 'public/upload/';
        if (!file_exists($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }

    /**
     * 读取Excel数据
     * $filePath string excel文件路径
     */
    public function read($filePath){

        $inputFileType = IOFactory::identify($filePath);    //类型
        $objReader = IOFactory::createReader($inputFileType);

        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filePath);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        /*$highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }*/
        $excelData = $objWorksheet->toArray();
        //return $excelData;
        return array('excelData'=>$excelData,'total_count'=>$highestRow);
    }

    public function readUrl($url)
    {
        $name = rand(100, 10000);
        $ext = trim(strrchr($url, '.'), '.');
        $destination = $this->dir . $name . '.' . $ext;
        file_put_contents($destination, file_get_contents($url));
        $data = $this->read($destination);
        unlink($destination);
        unset($data['excelData'][0]);
        return $data['excelData'];
    }
}