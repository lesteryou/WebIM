<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/30 14:46
 * Desc:
 */

namespace App\Http\Models;

use Illuminate\Database\Capsule\Manager as DB;

class Friend extends Model
{
    public function __construct()
    {
        $this->table = 'friend';
    }

    /**
     * 1.先获取所有分组
     * 2.获取所有好友
     * 3.组合数据
     * @param $uid
     */
    /**
     * 获取所有的好友和群组
     *
     * 1.先获取所有分组
     * 2.获取所有好友
     * 3.组合数据
     *
     * @param $uid
     * @return array
     */
    public function listAllFriendsAndGroupsTree($uid)
    {
        $groupWhere = [
            ['is_deleted', '=', 0], ['uid', '=', $uid]
        ];
        $groupList = DB::table('friend_group')
            ->select(['id', 'name as groupname'])
            ->where($groupWhere)
            ->get();
        $temp_groupList = [];
        foreach ($groupList as $k => &$v) {
            $v->list = [];
            $temp_groupList[$v->id] = $v;
        }
        $friendSelect = [
            'f.friend_uid as id',
            'u.nickname as username',
            'f.remark',
            'u.profile_photo as avatar',
            'u.sign',
            'u.online_status as status',
            'f.gf_id'
        ];
        $friendWhere = [
            ['f.is_deleted', '=', 0], ['f.uid', '=', $uid]
        ];
        $friendList = DB::table($this->table.' as f')
            ->select($friendSelect)
            ->leftJoin('user as u','u.id','=','f.friend_uid')
            ->where($friendWhere)
            ->get();
        foreach ($friendList as $k => &$v) {
            $v->username .= empty($v->remark) ? '' : '(' . $v->remark . ')';
            $v->status = $v->status == 1 ? 'online' : 'hide';
            ($temp_groupList[$v->gf_id]->list)[] = $v;
        }
        return array_merge($temp_groupList);
    }
}