<?php
/**
 * Created by PhpStorm.
 * User: chenjiahao
 * Date: 2019-09-12
 * Time: 09:17
 */

namespace CJHRabbitmq;
use Throwable;
use Exception;

class MQException  extends  Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}