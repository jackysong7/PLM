<?php

namespace app\common\library;

use app\api\model\KcloudLog;

class KCloudException extends \Exception
{
    protected $error;

    public function __construct($message = '', $error = [], $code = 0)
    {
        parent::__construct($message, $code);
        $this->error = $error;
        if ($error) {
            $this->code = 200;
            $this->message = false;

            KcloudLog::create([
                'request' => json_encode($_REQUEST, JSON_UNESCAPED_UNICODE),
                'log' => json_encode($error),
                'admin_id' => Auth::instance()->getUser()['admin_id'],
                'create_time' => time(),
            ]);
        } else {
            $this->code = 400;
        }
    }

    public function getError()
    {
        return $this->error;
    }
}