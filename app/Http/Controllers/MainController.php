<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/30 14:33
 * Desc:
 */

namespace App\Http\Controllers;


use App\Http\Models\Friend;
use App\Http\Models\Group;
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

        $responseData = [
            'mine' => $mine,
            'friend' => $friend,
            'group'=>$group
        ];
        return $response->withJson(IM_ASS($responseData));
    }

}