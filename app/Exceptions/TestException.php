<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/25 15:23
 * Desc:
 */

namespace App\Exceptions;

use Exception;
use Throwable;
class TestException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}