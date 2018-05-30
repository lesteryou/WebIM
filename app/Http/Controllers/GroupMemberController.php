<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/29 17:44
 * Desc:
 */

namespace App\Http\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Interop\Container\ContainerInterface;

class GroupMemberController extends Controller
{
    /**
     * GroupMemberController constructor.
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }


}