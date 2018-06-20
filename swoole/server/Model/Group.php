<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/8 8:54
 * Desc:
 */

namespace Server\Model;

use Illuminate\Database\Capsule\Manager as DB;

class Group
{
    public $table;

    public function __construct()
    {
        $this->table = 'group';
    }

    /**
     * 根据 群id 获取群成员
     *
     * @param int $group_id
     * @return \Illuminate\Support\Collection
     */
    public function listMembers($group_id)
    {
        return DB::table('group_member as m')
            ->leftJoin('user as u', 'u.id', '=', 'm.uid')
            ->select(['m.uid', 'm.remark', 'u.fd'])
            ->where([['m.group_id', '=', $group_id], ['m.is_deleted', '=', '0']])
            ->get();
    }

    /**
     * 根据群ID 获取成员ID数组
     *
     * @param int $group_id
     * @return array
     */
    public function getMemberIDArr($group_id)
    {
        $obj_list = DB::table('group_member')
            ->select(['uid'])
            ->where([['group_id', '=', $group_id], 'is_deleted', '=', 0])
            ->get();
        $uidArr = [];
        foreach ($obj_list as $item) {
            $uidArr[] = $item->uid;
        }
        return $uidArr;
    }

    /**
     * 根据用户ID 获取该用户所在群ID以及成员ID
     *
     * @param int $uid
     * @return array
     */
    public function listGroupAndMembers($uid)
    {
        $obj_list = DB::table('group_member as u')
            ->select(['o.uid', 'u.group_id', 'o.is_master'])
            ->leftJoin('group_member as o','u.group_id','=','o.group_id')
            ->where([['u.uid', '=', $uid], ['u.is_deleted', '=', 0],['o.is_deleted', '=', 0]])
            ->get();
        $list = [];
        foreach ($obj_list as $key => $value) {
            $list[$value->group_id][] = $value->uid;
        }
        return $list;
    }

}