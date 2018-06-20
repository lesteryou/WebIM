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
        $this->table = 'user';
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
            'email' => $formData['email'],
            'password' => $password,
            'create_ip' => $formData['create_ip'],
            'create_time' => time(),
            'account_status' => 1
        ];
        $insertID = DB::table('user')->insertGetId($data);
        if (!$insertID) TEA('604');
        return $insertID;
    }

    /**
     * 更新上次登录时间
     * @param $uid
     * @throws \App\Exceptions\ApiException
     * @return string
     */
    public function updateLastLogin($uid)
    {
        $_token = create_token($uid);
        $uid = DB::table('user')->where('id', $uid)->update(['last_login_time' => time(), '_token' => $_token]);
        if ($uid === false) TEA('500', 'Server_ERROR');
        return $_token;
    }

    /**
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getUserInfo($uid)
    {
        $obj = DB::table($this->table)->select(['*'])->where('id', '=', $uid)->first();
        return $obj;
    }

    /**
     * @param $field
     * @param $value
     * @throws \App\Exceptions\ApiException
     */
    public function checkIsExisted($field, $value)
    {
        $has =  DB::table($this->table)
            ->select(['id'])
            ->where([[$field, '=', $value], ['is_deleted', '=', 0]])
            ->count();
        if($has) TEA('606');
    }

}