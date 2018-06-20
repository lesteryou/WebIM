<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/29 17:42
 * Desc:
 */

namespace App\Http\Models;

use Illuminate\Database\Capsule\Manager as DB;

class Group extends Model
{
    public function __construct()
    {
        $this->table = 'group';
    }

    /**
     * @param $uid
     * @return \Illuminate\Support\Collection
     */
    public function listGroups($uid)
    {
        $select = [
            'g.name as groupname',
            'g.id',
            'g.image as avatar'
        ];
        $where = [
            ['g.is_deleted', '=', 0],
            ['m.is_deleted', '=', 0],
            ['m.uid', '=', $uid]
        ];
        $obj_list = DB::table('group_member as m')
            ->select($select)
            ->leftJoin($this->table . ' as g', 'g.id', '=', 'm.group_id')
            ->where($where)
            ->get();
        return $obj_list;
    }

    /**
     * @param string $value
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function searchGroups($value, $page = 1, $pageSize = 10)
    {
        $where[] = ['is_deleted', '=', 0];
        if (!empty($value)) $where[] = ['name', 'like', '%' . $value . '%'];
        $builder = DB::table($this->table)
            ->select(['id', 'name', 'image', 'description'])
            ->where($where);
        $data['count'] = $builder->count();
        $data['page'] = $page;
        $data['pageSize'] = $pageSize;
        $data['list'] = $builder->forPage($page, $pageSize)
            ->get();
        return $data;
    }

}