<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/14 11:04
 * Desc:
 */

namespace App\Http\Controllers;


use App\Http\Models\Friend;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class FriendGroupController extends Controller
{
    /**
     * FriendGroupController constructor.
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
    public function add(Request $request, Response $response, $args)
    {
        $formData  = $request->getParsedBody();
        trim_params($formData);
        if (!isset($formData['name']) || empty($formData['name'])) {
            TEA('640');
        }
        $id = (new Friend())->addFriendGroup(session('userInfo')->id, $formData['name']);
        return $response->withJson(ASS(['id' => $id]));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function delete(Request $request, Response $response, $args)
    {
        $formData = $request->getParsedBody();
        trim_params($formData);
        if (!isset($formData['id']) || empty($formData['id'])) {
            TEA('640');
        }
        $Friend = new Friend();
        $has = $Friend->getFriendGroup($formData['id']);
        if (empty($has)) {
            TEA('642');
        }
        if ($has->uid != session('userInfo')->id) {
            TEA('643');
        }
        $Friend->deleteFriendGroup($formData['id']);
        return $response->withJson(ASS(['id' => $formData['id']]));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function rename(Request $request, Response $response, $args)
    {
        $formData = $request->getParsedBody();
        trim_params($formData);
        if (empty($formData['name'])) {
            TEA('640');
        }
        if(empty($formData['id'])){
            TEA('643');
        }
        $Friend = new Friend();
        $has = $Friend->getFriendGroup($formData['id']);
        if (empty($has)) {
            TEA('644');
        }
        if ($has->uid != session('userInfo')->id) {
            dd($has);
            TEA('643');
        }
        $Friend->renameFriendGroup($formData['id'], $formData['name']);
        return $response->withJson(ASS(['id' => $formData['id']]));
    }


}