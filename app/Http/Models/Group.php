<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/29 17:42
 * Desc:
 */

namespace App\Http\Models;


class Group extends Model
{
    public function __construct()
    {
        $this->table = 'group';
    }

    public function listGroupMember($groupID)
    {

    }

}