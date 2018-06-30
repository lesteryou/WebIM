<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/25 16:44
 * Desc:
 */

namespace App\Http\Models;

use Illuminate\Database\Capsule\Manager as DB;

class Apply extends Model
{
    public function __construct()
    {
        $this->table = 'apply';
    }

    /**
     * @param $uid
     * @return int
     */
    public function getApplyNum($uid)
    {
        $total = DB::table($this->table)
            ->where([['applicant_uid', '=', $uid], ['is_read', '=', 0], ['is_deleted', '=', 0]])
            ->count();
        if (!$total) {
            $total = 0;
        }
        return $total;
    }

    /**
     * 把申请设为未读
     *
     * @param int $apply_id
     */
    public function setUnread($apply_id)
    {
        DB::table($this->table)->where('id', '=', $apply_id)->update(['is_read' => 0]);
    }

    /**
     * @param $uid
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function list($uid, $page = 1, $pageSize = 10)
    {
        $select = [
            'a.id',
            'a.type',
            'a.applicant_uid',
            'a.group_id',
            'a.friend_uid',
            'a.status',
            'a.create_time',
            'a.modify_time',
            'a.remark',
            'a.refuse_message',
            'u_a.nickname as apply_username',
            'u_a.profile_photo as apply_avatar',
            'u_f.nickname as friend_username',
            'u_f.profile_photo as friend_avatar',
            'g.name as group_name',
            'g.image as group_image',


        ];
        $builder = DB::table($this->table . ' as a')
            ->select($select)
            ->leftJoin('user as u_a', 'a.applicant_uid', '=', 'u_a.id')
            ->leftJoin('user as u_f', 'a.friend_uid', '=', 'u_f.id')
            ->leftJoin('group as g', 'g.id', '=', 'a.group_id')
            ->where([['a.is_deleted', '=', 0]]);
        $builder->where(function ($query) use ($uid) {
            $query->orWhere([['a.applicant_uid', '=', $uid]]);
            $query->orWhere([['a.type', '=', 1], ['a.friend_uid', '=', $uid]]);
            $query->orWhere(function ($_query) use ($uid) {
                $_query->where([['a.type', '=', 2]]);
                $_query->whereRaw(DB::raw("a.group_id in (select gm.group_id from group_member as gm where gm.uid = $uid and gm.is_master=1)"));
            });
        });
        $list['total'] = $builder->count();
        $list['page'] = $page;
        $list['pageSize'] = $pageSize;
        $list['dataList'] = $builder->orderBy('a.modify_time', 'DESC')->forPage($page, $pageSize)->get();
        $idArr = [];
        foreach ($list['dataList'] as $key => &$value) {
            $idArr[] = $value->id;
            $string = $value->status == 2 ? '已经同意' : '拒绝了';
            if ($value->type == 1) {
                if ($value->applicant_uid == $uid) {
                    if ($value->status == 1) {
                        $value->content = '你已申请添加 [' . $value->friend_username . '] 为好友';
                        $value->_type = 3;
                    } else {
                        $value->content = '[' . $value->friend_username . '] ' . $string . '你的好友申请';
                        $value->_type = 2;
                    }
                } else {
                    $value->content = '申请添加你为好友';
                    $value->_type = 1;
                }
            } else {
                if ($value->applicant_uid == $uid) {
                    if ($value->status == 1) {
                        $value->content = '你已申请添加群 [' . $value->group_name . ']';
                        $value->_type = 3;
                    } else {
                        $value->content = '[' . $value->group_name . '] 管理员' . $string . '你的请求';
                        $value->_type = 2;
                    }
                } else {
                    $value->content = '申请添加群 [' . $value->group_name . '] ';
                    $value->_type = 1;
                }
            }
            $value->create_time = empty($value->create_time) ? '无' : date('Y-m-d H:i:s', $value->create_time);
            $value->modify_time = empty($value->modify_time) || $value->create_time == $value->modify_time ? '无' : date('Y-m-d H:i:s', $value->modify_time);
        }
        DB::table($this->table)->whereIn('id', $idArr)->update(['is_read' => 1]);
        return $list;
    }

    /**
     * @param $apply_id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     * @throws \App\Exceptions\ApiException
     */
    public function getApplyInfo($apply_id)
    {
        $data = DB::table($this->table)
            ->select(['*'])
            ->where([['is_deleted', '=', 0], ['id', '=', $apply_id], ['status', '=', 1]])
            ->first();
        if (empty($data)) TEA('643');
        return $data;
    }

    /**
     * @param $apply_id
     * @param $applyData
     * @throws \App\Exceptions\ApiException
     */
    public function doApplyByApply($apply_id, $applyData)
    {
        $data = empty($applyData) ? $this->getApplyInfo($apply_id) : $applyData;
        if ($data->type == 1) {
            $inertKeyVal = [
                'uid' => $data->applicant_uid,
                'friend_uid' => $data->friend_uid,
                'create_time' => time(),
                'fg_id' => $data->group_id,
            ];
            $Friend = new Friend();
            if ($Friend->checkIsFriend($data->applicant_uid, $data->friend_uid)) {
                DB::table('friend')
                    ->where([['uid', '=', $data->applicant_uid], ['friend_uid', '=', $data->friend_uid]])
                    ->update(['create_time' => time(), 'fg_id' => $data->group_id]);
            } else {
                DB::table('friend')->insertGetId($inertKeyVal);
            }
            DB::table($this->table)->where('id', $data->id)->update(['status' => 2, 'modify_time' => time()]);
        } else {
            $inertKeyVal = [
                'group_id' => $data->group_id,
                'uid' => $data->applicant_uid,
                'create_time' => time(),
                'is_master' => 0
            ];
            DB::table('group_member')->insertGetId($inertKeyVal);
            DB::table($this->table)->where('id', $data->id)->update(['status' => 2, 'is_read' => 0, 'modify_time' => time()]);
        }
    }

    public function doNotApplyByApply($apply_id, $refuse_message)
    {
        DB::table($this->table)
            ->where('id', '=', $apply_id)
            ->update(['status' => 3, 'is_read' => 0, 'modify_time' => time(), 'refuse_message' => $refuse_message]);

    }

}