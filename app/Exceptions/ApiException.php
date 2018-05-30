<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/25 9:01
 * Desc:
 */

namespace App\Exceptions;

use Exception;
/**
 * Class ApiException
 * @package App\Exceptions
 */
class ApiException extends Exception
{
    public function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}