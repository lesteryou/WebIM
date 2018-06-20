<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/7 17:54
 * Desc:
 */

namespace Server\Model;

use Illuminate\Database\Capsule\Manager as DB;

class User
{

    public $table;

    public function __construct()
    {
        $this->table = 'user';
    }

    public function checkToken($uid, $_token)
    {
        $select = [
            'id', 'nickname', 'sign', 'profile_photo', '_token','online_status'
        ];
        $obj = DB::table('user')->select($select)->where([['id', '=', $uid], ['is_deleted', '=', 0]])->first();
        if ($obj->_token != $_token) {
            return false;
        }
        return $obj;
    }

    public function updateFd($uid, $fd)
    {
        return DB::table('user')->where('id', '=', $uid)->update(['fd' => $fd]);
    }

    public function getFd($uid)
    {
        return DB::table('user')->where('id', '=', $uid)->first(['id', 'fd', 'is_deleted']);

    }

    public function getUid($fid)
    {
        return DB::table('user')->where('fd', '=', $fid)->first(['id', 'fd', 'is_deleted']);

    }

    public function initFd()
    {
        DB::table('user')->update(['fd' => 0]);
    }

    /**
     * 更改签名
     * @param int $uid
     * @param string $content
     */
    public function updateSign($uid, $content)
    {
        DB::table($this->table)->where('id', '=', $uid)->update(['sign' => trim($content)]);
    }

    public function getFriendUidArr($uid)
    {
        $obj_list= DB::table('friend')->select(['friend_uid'])->where([['uid', '=', $uid], ['is_deleted', '=', 0]])->get();
        $uidArr = [];
        foreach ($obj_list as $item) {
            $uidArr[] = $item->friend_uid;
        }
        return $uidArr;
    }

    public function update($uid, $onlineStatus)
    {
        DB::table($this->table)->where('id', '=', $uid)->update(['online_status' => (int)$onlineStatus]);

    }

}