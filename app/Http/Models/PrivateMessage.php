<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/15 13:49
 * Desc:
 */

namespace App\Http\Models;


use Illuminate\Database\Capsule\Manager as DB;

class PrivateMessage extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'private_message';
    }

    public function listHistoryMessage($uid, $friend_uid, $page = 1, $pageSize = 15)
    {
        $select = [
            'm.id as cid',
            'm.from_uid as id',
            'm.content',
            'u.nickname as username',
            'u.profile_photo as avatar',
            'm.create_time as timestamp'
        ];
        $builder = DB::table($this->table . ' as m')
            ->select($select)
            ->leftJoin('user as u', 'u.id', '=', 'm.from_uid')
            ->where([['m.is_deleted', '=', 0]])
            ->where(function ($query) use ($uid, $friend_uid) {
                $query->where([['m.from_uid', '=', $friend_uid], ['m.to_uid', '=', $uid]]);
                $query->orWhere([['m.from_uid', '=', $uid], ['m.to_uid', '=', $friend_uid]]);
            })
            ->orderBy('m.create_time', 'DESC');
        $data['count'] = $builder->count();
        $data['page'] = $page;
        $data['pageSize'] = $pageSize;
        $data['list'] = $builder->forPage($page, $pageSize)
            ->get();
        foreach ($data['list'] as $key => &$value) {
            $value->timestamp *= 1000;
        }
        return $data;
    }

}