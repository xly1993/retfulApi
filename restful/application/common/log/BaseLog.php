<?php
/**
 * Created by PhpStorm.
 * User: xv
 * Date: 2019/1/16
 * Time: 9:54
 */

namespace app\common\log;


class BaseLog
{
    public static function log($fileName,$msg)
    {
        file_put_contents(LOG_PATH.$fileName,date("Y-m-d H:i:s")." ".$msg.PHP_EOL,FILE_APPEND);
    }
}