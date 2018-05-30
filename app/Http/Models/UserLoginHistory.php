<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/24 17:22
 * Desc:
 */

namespace App\Http\Models;

use Illuminate\Database\Capsule\Manager as DB;
use Slim\Http\Request;

class UserLoginHistory extends Model
{
    public function __construct()
    {
        $this->table = 'user_login_history';
    }

    /**
     * @param Request $request
     * @param $uid
     * @param int $status
     */
    public function addRecord(Request $request,$uid,$status=1)
    {
        $ip = $request->getAttribute('ip_address');
        $cookies = $request->getServerParam('HTTP_COOKIE');
        $userAgent = $request->getServerParam('HTTP_USER_AGENT');
        $data = [
            'uid'=>$uid,
            'login_time' => time(),
            'ip' => $ip,
            'user_agent' => $userAgent,
            'cookies' => $cookies,
            'status'=>$status
        ];
        DB::table('user_login_history')->insert($data);
    }
}