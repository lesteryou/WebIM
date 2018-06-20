<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/29 17:29
 * Desc:
 */

namespace App\Http\Controllers;

use App\Http\Models\Group;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Http\Models\GroupMember;
use Interop\Container\ContainerInterface;

class GroupController extends Controller
{
    /**
     * GroupController constructor.
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
    public function listMembers(Request $request, Response $response, $args)
    {
        $queryData = $request->getQueryParams();
        if (empty($queryData['id'])) TEA('450', 'id');

        $GroupMember = new GroupMember();
        $data = $GroupMember->listMembers($queryData['id']);
        return $response->withJson(IM_ASS(['list' => $data]));
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
        if (empty($queryData['value'])) $queryData['value'] = '';
        $queryData['page'] = isset($queryData['page']) ? $queryData['page'] : 1;
        $queryData['pageSize'] = isset($queryData['pageSize']) ? $queryData['pageSize'] : 10;
        trim_params($queryData);

        $Group = new Group();
        $returnData = $Group->searchGroups($queryData['value'], $queryData['page'], $queryData['pageSize']);
        return $response->withJson(ASS($returnData));
    }

    public function applyGroup(Request $request, Response $response, $args)
    {

    }


}