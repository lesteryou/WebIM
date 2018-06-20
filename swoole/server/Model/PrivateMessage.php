<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/8 0:45
 * Desc:
 */

namespace Server\Model;

use Illuminate\Database\Capsule\Manager as DB;

class PrivateMessage
{
    public $table = 'private_message';

    public function add($from_uid, $to_uid, $content, $is_offline = 0, $is_read = 0)
    {
        $is_deleted = 0;
        $create_time = time();
        $keyVal = compact('from_uid', 'to_uid', 'content', 'is_deleted', 'is_offline', 'create_time', 'is_read');
        return DB::table($this->table)->insertGetId($keyVal);
    }

    public function listOfflineMessage($uid)
    {
        $obj_list = DB::table($this->table . ' as pm')
            ->select(['pm.id as messageID', 'pm.to_uid', 'pm.from_uid', 'pm.content', 'pm.create_time', 'u.nickname', 'u.profile_photo'])
            ->leftJoin('user as u', 'u.id', '=', 'pm.from_uid')
            ->where([['pm.is_deleted', '=', 0], ['pm.is_offline', '=', 1], ['pm.to_uid', '=', $uid]])
            ->orderBy('pm.create_time')
            ->get();
        DB::table($this->table)
            ->where([['is_deleted', '=', 0], ['is_offline', '=', 1], ['to_uid', '=', $uid]])
            ->update(['is_offline' => 0, 'is_read' => 1]);
        return $obj_list;
    }
}