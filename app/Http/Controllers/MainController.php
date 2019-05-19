<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/30 14:33
 * Desc:
 */

namespace App\Http\Controllers;


use App\Http\Models\Apply;
use App\Http\Models\Friend;
use App\Http\Models\Group;
use App\Http\Models\GroupMember;
use App\Http\Models\User;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class MainController extends Controller
{
    /**
     * MainController constructor.
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * IM面板初始化数据
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function init(Request $request, Response $response, $args)
    {
        $userInfo = session('userInfo');
        $uid = $userInfo->id;

        $User = new User();
        $userInfo = $User->getUserInfo($uid);
        session(['userInfo' => $userInfo]);

        $mine = [
            'username' => $userInfo->nickname,
            'id' => $userInfo->id,
            'status' => ($userInfo->online_status) == 1 ? 'online' : 'hide',
            'sign' => $userInfo->sign,
            'avatar' => $userInfo->profile_photo
        ];

        $FriendModel = new Friend();
        $friend = $FriendModel->listAllFriendsAndGroupsTree($userInfo->id);

        $GroupModel = new Group();
        $group = $GroupModel->listGroups($userInfo->id);

//        $Apply = new Apply();
//        $applyNum = $Apply->getApplyNum($uid);

        $responseData = [
            'mine' => $mine,
            'friend' => $friend,
            'group' => $group,
//            'applyNum' => $applyNum
        ];
        return $response->withJson(IM_ASS($responseData));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function listApply(Request $request, Response $response, $args)
    {
        $queryData = $request->getQueryParams();
        $queryData['page'] = empty($queryData['page']) ? 1 : $queryData['page'];
        $queryData['pageSize'] = empty($queryData['pageSize']) ? 10 : $queryData['pageSize'];
        $Apply = new Apply();
        $list = $Apply->list(session('userInfo')->id, $queryData['page'], $queryData['pageSize']);
        return $response->withJson(ASS($list));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function doApply(Request $request, Response $response, $args)
    {
        $formData = $request->getParsedBody();
        trim_params($formData);
        if (empty($formData['type'])) TEA('682');
        if (empty($formData['apply_id'])) TEA('450', '请刷新页面重新尝试');
        $formData['remark'] = empty($formData['remark']) ? '' : $formData['remark'];
        // 1.验证当前用户是否为被审核者(被添加者/群管理员)
        // 2.如果同意，入库，同时添加为好友管理，或者进群
        // 3.给相关人员推送（个人已同意加好友/已同意进群）
        $Apply = new Apply();
        $applyData = $Apply->getApplyInfo($formData['apply_id']);
        $responseData = [];
        $applyNum = $Apply->getApplyNum($applyData->friend_uid);
        if ($applyData->type == 1) {      // 添加好友
            if ($applyData->friend_uid != session('userInfo')->id) {
                TEA('643');     // 提交数据的异常，刷新页面
            }
            if ($formData['type'] == 1) {
                $Apply->doApplyByApply($formData['apply_id'], $applyData);
                // 推送
                $User = new User();
                $friendInfo = $User->getUserInfo($applyData->friend_uid);
                $sendData = [
                    'type' => 'doApplyFriend',
                    'is_accept' => 1,
                    'receiver_uid' => $applyData->applicant_uid,
                    'applyNum' => $applyNum,
                    'info' => [
                        'id' => $friendInfo->id,
                        'username' => $friendInfo->nickname,
                        'avatar' => $friendInfo->profile_photo,
                        'sign' => $friendInfo->sign,
                        'fg_id' => $applyData->group_id
                    ]
                ];
                sendToWS($sendData);
                // 判断 申请人是否为被申请人的好友
                $Friend = new Friend();
                $responseData['is_friend'] = 1;
                if (!$Friend->checkIsFriend($applyData->friend_uid, $applyData->applicant_uid)) {
                    $applicantData = $User->getUserInfo($applyData->applicant_uid);
                    $responseData['is_friend'] = 0;
                    $responseData['userInfo'] = [
                        'id' => $applicantData->id,
                        'username' => $applicantData->nickname,
                        'avatar' => $applicantData->profile_photo,
                        'sign' => $applicantData->sign,
                        'sex' => $applicantData->sex
                    ];
                }
            } else {
                $Apply->doNotApplyByApply($formData['apply_id'], $formData['remark']);
                $User = new User();
                $friendInfo = $User->getUserInfo($applyData->friend_uid);
                $sendData = [
                    'type' => 'doApplyFriend',
                    'is_accept' => 0,
                    'receiver_uid' => $applyData->applicant_uid,
                    'applyNum' => $applyNum,
                    'info' => [
                        'id' => $friendInfo->id,
                        'username' => $friendInfo->nickname,
                        'avatar' => $friendInfo->profile_photo,
                        'sign' => $friendInfo->sign,
                        'sex' => $friendInfo->sex,
                        'fg_id' => $applyData->group_id
                    ]
                ];
                sendToWS($sendData);
            }

        } else {      // 添加群
            $GroupMember = new GroupMember();
            if (!$GroupMember->checkIsMaster(session('userInfo')->id, $applyData->group_id)) {
                TEA('680');     // 不是管理员
            }
            // 验证群是否存在
            $Group = new Group();
            $groupInfo = $Group->getGroup($applyData->group_id);
            if (empty($groupInfo)) {
                TEA('681');
            }

            // 处理 & 推送
            if ($formData['type'] == 1) {
                $Apply->doApplyByApply($formData['apply_id'], $applyData);
                // 推送
                $sendData = [
                    'type' => 'doApplyGroup',
                    'is_accept'=>1,
                    'receiver_uid' => $applyData->applicant_uid,
                    'applyNum' => $applyNum,
                    'info' => [
                        'id' => $groupInfo->id,
                        'group_name' => $groupInfo->name,
                        'avatar' => $groupInfo->image
                    ]
                ];
                sendToWS($sendData);
            } else {
                $Apply->doNotApplyByApply($formData['apply_id'], $formData['remark']);
                // 推送
                $sendData = [
                    'type' => 'doApplyGroup',
                    'is_accept'=>0,
                    'receiver_uid' => $applyData->applicant_uid,
                    'applyNum' => $applyNum,
                    'info' => [
                        'id' => $groupInfo->id,
                        'group_name' => $groupInfo->name,
                        'avatar' => $groupInfo->image
                    ]
                ];
                sendToWS($sendData);
            }

        }
        return $response->withJson(ASS($responseData));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getApplyNum(Request $request, Response $response, $args)
    {
        $Apply = new Apply();
        $applyNum = $Apply->getApplyNum(session('userInfo')->id);
        return $response->withJson(ASS(['applyNum' => $applyNum]));
    }

}