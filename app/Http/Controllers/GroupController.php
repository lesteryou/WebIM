<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/29 17:29
 * Desc:
 */

namespace App\Http\Controllers;

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


}