<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/22 14:45
 * Desc:
 */

namespace App\Http\Models;

use Illuminate\Database\Capsule\Manager as DB;

class User extends Model
{
    public function __construct()
    {

    }

    public function list()
    {
        return DB::table('user')
            ->get();
    }

    /**
     * @param $userName
     * @param array $fields
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getUserInfoByEmail($userName, $fields = [])
    {
        $select = empty($fields) ? '*' : $fields;
        return DB::table('user')->select($select)->where('email', trim($userName))->first();
    }

    /**
     * @param $formData
     * @return int
     * @throws \App\Exceptions\ApiException
     */
    public function register($formData)
    {
        $password = encrypted_password($formData['password']);
        $data = [
            'nickname' => $formData['nickname'],
            'email' => $formData['username'],
            'password' => $password,
            'create_ip' => $formData['create_ip'],
            'create_time' => time(),
            'status'=>1
        ];
        $insertID = DB::table('user')->insertGetId($data);
        if ($insertID) TEA('604');
        return $insertID;
    }

    /**
     * 更新上次登录时间
     * @param $uid
     * @throws \App\Exceptions\ApiException
     */
    public function updateLastLogin($uid)
    {
        $uid = DB::table('user')->where('id', $uid)->update(['last_login_time' => time()]);
        if($uid===false) TEA('500', 'Server_ERROR');
    }
}