<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/7 22:46
 * Desc:
 */

namespace App\Http\Controllers;

use App\Http\Models\User;
use Slim\Http\Request;
use Slim\Http\Response;
use Interop\Container\ContainerInterface;

/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class ExampleController extends Controller
{

    /**
     * ExampleController constructor.
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function Index(Request $request, Response $response, $args)
    {
        $User = new User();
        $list = $User->list();
        return $this->render($response, 'index.html', $list);
    }
}