<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/30 9:52
 * Desc:
 */

namespace App\Http\Models;

use Illuminate\Database\Capsule\Manager as DB;

class GroupMember extends Model
{
    /**
     * GroupMember constructor.
     */
    public function __construct()
    {
        $this->table = 'group_member';
    }

    /**
     * @param $groupID
     * @return \Illuminate\Support\Collection
     * @throws \App\Exceptions\ApiException
     */
    public function listMembers($groupID)
    {
        $select = [
            'u.nickname as username',
            'u.id',
            'u.profile_photo as avatar',
            'u.sign'
        ];
        $where = [
            ['g.group_id', '=', $groupID],
            ['g.is_deleted', '=', '0']
        ];
        $objList = DB::table($this->table . ' as g')
            ->select($select)
            ->leftJoin('user as u', 'u.id', '=', 'g.uid')
            ->where($where)
            ->get();
        if (empty($objList)) {
            TEA('500', 'Server_ERROR');
        }
        return $objList;
    }


}