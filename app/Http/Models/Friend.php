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
            'f.fg_id'
        ];
        $friendWhere = [
            ['f.is_deleted', '=', 0], ['f.uid', '=', $uid]
        ];
        $friendList = DB::table($this->table . ' as f')
            ->select($friendSelect)
            ->leftJoin('user as u', 'u.id', '=', 'f.friend_uid')
            ->where($friendWhere)
            ->get();
        foreach ($friendList as $k => &$v) {
            $v->username .= empty($v->remark) ? '' : '(' . $v->remark . ')';
            $v->status = $v->status == 1 ? 'online' : 'offline';
            ($temp_groupList[$v->fg_id]->list)[] = $v;
        }
        return array_merge($temp_groupList);
    }

    /**
     * 获取好友分组详情
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getFriendGroup($id)
    {
        return DB::table('friend_group')
            ->select(['id', 'uid', 'name', 'create_time'])
            ->where([['id', '=', $id], ['is_deleted', '=', 0]])
            ->first();
    }

    /**
     * 添加好友分组
     *
     * @param $uid
     * @param $name
     * @return int
     */
    public function addFriendGroup($uid, $name)
    {
        return DB::table('friend_group')
            ->insertGetId(
                ['uid' => $uid, 'name' => $name, 'create_time' => time(), 'is_deleted' => 0]
            );
    }

    /**
     * 删除好友分组
     *
     * @param $friendGroupID
     * @throws \App\Exceptions\ApiException
     */
    public function deleteFriendGroup($friendGroupID)
    {
        $has = DB::table($this->table)
            ->select(['id'])
            ->where([['fg_id', '=', $friendGroupID], ['is_deleted', '=', 0]])
            ->first();
        if (!empty($has)) {
            TEA('641');
        }
        DB::table('friend_group')->where('id', '=', $friendGroupID)->update(['is_deleted' => 1]);
    }

    /**
     * 重命名好友分组
     *
     * @param $uid
     * @param $newName
     * @throws \App\Exceptions\ApiException
     */
    public function renameFriendGroup($uid, $newName)
    {
        $res = DB::table('friend_group')->where([['id', '=', $uid,], ['is_deleted', '=', 0]])->update(['name' => $newName]);
        if (!$res) {
            TEA('645');
        }
    }

    /**
     * 根据邮箱或者昵称搜索好友
     *
     * @param $value
     * @param $page
     * @param $pageSize
     * @return mixed
     */
    public function searchFriends($value, $page = 1, $pageSize = 10)
    {
        $select = [
            'id',
            'nickname',
            'email',
            'sign',
            'profile_photo'
        ];
        $builder = DB::table('user')
            ->select($select)
            ->where([['is_deleted', '=', 0]]);
        if (!empty($value)) {
            $builder->where(function ($query) use ($value) {
                $query->orWhere([['nickname', 'like', '%' . $value . '%']]);
                $query->orWhere([['email', '=', $value]]);
            });
        }
        $data['count'] = $builder->count();
        $data['page'] = $page;
        $data['pageSize'] = $pageSize;
        $data['list'] = $builder->forPage($page, $pageSize)
            ->get();
        return $data;
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function searchAllFriends($page = 1, $pageSize = 10)
    {
        $select = [
            'id',
            'nickname',
            'email',
            'sign',
            'profile_photo'
        ];
        $builder = DB::table('user')
            ->select($select)
            ->where([['is_deleted', '=', 0]]);
        $data['count'] = $builder->count();
        $data['page'] = $page;
        $data['pageSize'] = $pageSize;
        $data['list'] = $builder->forPage($page, $pageSize)
            ->get();
        return $data;
    }

    /**
     * @param array $formData
     * @return int
     * @throws \App\Exceptions\ApiException
     */
    public function apply($formData)
    {
        $isExisted = DB::table('user')
            ->where([['id', '=', $formData['friend_uid']], ['account_status', '=', 1], ['is_deleted', '=', 0]])
            ->count();
        if (!$isExisted) TEA('672');

        $friend_has = DB::table('friend')
            ->where([['is_deleted', '=', 0], ['uid', '=', $formData['uid']], ['friend_uid', '=', $formData['friend_uid']]])
            ->count();
        if ($friend_has) TEA('670');

        $apply_has = DB::table('apply')
            ->where([
                ['applicant_uid', '=', $formData['uid']],
                ['friend_uid', '=', $formData['friend_uid']],
                ['type', '=', 1],
                ['status', '=', 1],
                ['is_deleted', '=', 0]
            ])
            ->count();
        if ($apply_has) TEA('671');

        $keyValArr = [
            'type' => 1,
            'applicant_uid' => $formData['uid'],
            'group_id' => $formData['friend_group_id'],
            'friend_uid' => $formData['friend_uid'],
            'status' => 1,
            'is_deleted' => 0,
            'create_time' => time(),
            'remark' => $formData['remark'],
            'modify_time' => time()
        ];
        return DB::table('apply')->insertGetId($keyValArr);
    }
}