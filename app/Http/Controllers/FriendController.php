<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/18 22:25
 * Desc:
 */

namespace App\Http\Controllers;


use App\Http\Models\Friend;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class FriendController extends Controller
{
    /**
     * FriendController constructor.
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function search(Request $request, Response $response, $args)
    {
        $queryData = $request->getQueryParams();
        if (empty($queryData['value'])) $queryData['value']='';
        $queryData['page'] = isset($queryData['page']) ? $queryData['page'] : 1;
        $queryData['pageSize'] = isset($queryData['pageSize']) ? $queryData['pageSize'] : 9;
        trim_params($queryData);

        $Friend = new Friend();
        $returnData = $Friend->searchFriends($queryData['value'], $queryData['page'], $queryData['pageSize']);
        return $response->withJson(ASS($returnData));
    }

    public function selectAll(Request $request, Response $response, $args)
    {

    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function apply(Request $request, Response $response, $args)
    {
        $formData = $request->getParsedBody();
        if (empty($formData['friend_uid'])) TEA('450', 'friend_uid');
        if (empty($formData['friend_group_id'])) TEA('450', 'friend_group_id');
        if (empty($formData['remark'])) $formData['remark'] = '';
        $formData['uid'] = session('userInfo')->id;

        $Friend = new Friend();
        $insertID = $Friend->apply($formData);
        return $response->withJson(ASS(['id' => $insertID]));
    }
}