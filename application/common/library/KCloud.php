<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2018/7/24 11:47
// +----------------------------------------------------------------------
// | TITLE:  金蝶ERP
// +----------------------------------------------------------------------

namespace app\common\library;

use think\Session;

class KCloud
{
    /*
     * 验证登陆
     * */
    public static function check_login()
    {
        $config = \think\Config::get('kcloud');
        $cloudUrl = $config['cloudUrl'];
        //定义记录Cloud服务端返回的Session
        $cookie_jar = $config['cookieJar'];

        //登陆参数
        $data = array(
            $config['accountid'],//帐套Id
            $config['username'],//用户名
            $config['password'],//密码
            $config['langtag']//语言标识
        );
        $post_content = self::create_postdata($data);
        $result = self::invoke_login($cloudUrl,$post_content,$cookie_jar);
        $resultArr = json_decode($result,true);

        $res = $resultArr["LoginResultType"]; //返回值为1，代表登陆成功
        if( $res == 1 ){
            Session::set('k3CloudLoginResult',true);  //true为已登陆，false为未登录
            Session::set('cookieJar',$cookie_jar);  //定义记录Cloud服务端返回的Session
            Session::set('cloudUrl',$cloudUrl);
        }
        return $res;
    }

    //登陆
    public static function invoke_login($cloudUrl,$post_content,$cookie_jar)
    {
        $loginurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.AuthService.ValidateUser.common.kdsvc';
        return self::invoke_post($loginurl,$post_content,$cookie_jar,TRUE);
    }

    //保存
    public static function invoke_save($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Save.common.kdsvc';
        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    /**
     * 自动保存
     * @param $form_id
     * @param $data
     * @return mixed
     * @throws KCloudException
     */
    public static function auto_save($form_id, $data)
    {
        return self::auto_action('save', $form_id, $data);
    }

    /**
     * 自动提交
     * @param $form_id
     * @param $data
     * @return mixed
     * @throws KCloudException
     */
    public static function auto_submit($form_id, $data)
    {
        return self::auto_action('submit', $form_id, $data);
    }

    /**
     * 自动审核
     * @param $form_id
     * @param $data
     * @return mixed
     * @throws KCloudException
     */
    public static function auto_audit($form_id, $data)
    {
        return self::auto_action('audit', $form_id, $data);
    }

    /**
     * 自动删除
     * @param $form_id
     * @param $data
     * @return mixed
     * @throws KCloudException
     */
    public static function auto_delete($form_id, $data)
    {
        return self::auto_action('delete', $form_id, $data);
    }

    /**
     * @param $action
     * @param $form_id
     * @param $data
     * @return mixed
     * @throws KCloudException
     */
    public static function auto_action($action, $form_id, $data)
    {
        $text = [
            'save' => '保存',
            'submit' => '提交',
            'audit' => '审核',
            'delete' => '删除',
        ];
        $res = Session::get('k3CloudLoginResult') === true ? 1 : KCloud::check_login();
        if ($res != 1) {
            throw new KCloudException('ERP登录失败');
        }

        $post_content = KCloud::create_postdata([$form_id, $data]);
        $cloudUrl = Session::get('cloudUrl');
        $cookie_jar = Session::get('cookieJar');

        $method = "invoke_{$action}";
        $result = KCloud::$method($cloudUrl, $post_content, $cookie_jar);
        $result = json_decode($result, true);

        if (empty($result['Result']['ResponseStatus']['IsSuccess']) &&
            !empty($result['Result']['ResponseStatus']['Errors'])) {
            $errors = [];
            foreach ($result['Result']['ResponseStatus']['Errors'] as $error) {
                $errors[] = $error['Message'];
            }
            throw new KCloudException('', $errors);
        } elseif(empty($result['Result']['ResponseStatus']['IsSuccess'])) {
            throw new KCloudException("ERP{$text[$action]}失败，原因未知");
        }

        return $result;
    }

    //批量保存
    public static function invoke_batch_save($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.BatchSave.common.kdsvc';
        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    //删除
    public static function invoke_delete($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Delete.common.kdsvc';
        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    //查看
    public static function invoke_view($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.View.common.kdsvc';
        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    //查询
    public static function invoke_query($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.ExecuteBillQuery.common.kdsvc';
        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    //审核
    public static function invoke_audit($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Audit.common.kdsvc';
        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    //提交
    public static function invoke_submit($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Submit.common.kdsvc';
        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    //状态转换 <禁用状态可以用>
    public static function invoke_status_convert($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.StatusConvert.common.kdsvc';
        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    //禁用/启用
    public static function invoke_set_status($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.ExcuteOperation.common.kdsvc';

        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    //下推
    public static function invoke_push($cloudUrl,$post_content,$cookie_jar)
    {
        $invokeurl = $cloudUrl.'Kingdee.BOS.WebApi.ServicesStub.DynamicFormService.Push.common.kdsvc';

        return self::invoke_post($invokeurl,$post_content,$cookie_jar,FALSE);
    }

    //请求
    public static function invoke_post($url,$post_content,$cookie_jar,$isLogin)
    {
        $ch = curl_init($url);

        $this_header = array(
            'Content-Type: application/json',
            'Content-Length: '.strlen($post_content)
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this_header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($isLogin){
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
        }
        else{
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    //构造Web API请求格式
    public static function create_postdata($args)
    {
        $postdata = array(
            'format'=>1,
            'useragent'=>'ApiClient',
            'rid'=>self::create_guid(),
            'parameters'=>$args,
            'timestamp'=>date('Y-m-d'),
            'v'=>'1.0'
        );
        return json_encode($postdata);
    }


    //生成guid
    public static function create_guid()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }

    /**get请求
     * $url 请求的地址
     * $array 请求的参数
     */
    public static function invoke_get($url,$array=array())
    {
        $curl = curl_init();
        //设置提交的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $array);
        //执行命令
        $dataInfo = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //获得数据并返回
        return $dataInfo;
    }
}