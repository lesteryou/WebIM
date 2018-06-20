<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/8 0:50
 * Desc:
 */

namespace Server\Model;

use Illuminate\Database\Capsule\Manager as DB;

class GroupMessage
{
    public $table = 'group_message';

    public function add($from_uid, $group_id, $content)
    {
        $is_deleted = 0;
        $create_time = time();
        $keyVal = compact('from_uid', 'group_id', 'content', 'is_deleted', 'create_time');
        return DB::table($this->table)->insertGetId($keyVal);
    }

    public function addLink($message_id, $uid, $group_id, $is_offline = 0, $is_read = 0)
    {
        $keyVal = compact('message_id', 'uid', 'group_id', 'is_offline', 'is_read');
        return DB::table('group_message_member')->insertGetId($keyVal);
    }

}

