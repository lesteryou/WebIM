<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/15 15:43
 * Desc:
 */

namespace App\Http\Models;

use Illuminate\Database\Capsule\Manager as DB;

class GroupMessage extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'group_message';
    }

    public function listHistoryMessage($group_id, $page = 1, $pageSize = 15)
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
            ->where([['m.is_deleted', '=', 0], ['m.group_id', '=', $group_id]])
            ->orderBy('m.create_time', 'DESC')
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