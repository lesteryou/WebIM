<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/4/27 17:46
 * Desc:
 */

namespace App\Http\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Interop\Container\ContainerInterface;

/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{

    /**
     * HomeController constructor.
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
        $data = ['id', 'name'];
        return $this->view->render($response, 'index.html', ['data' => $data]);
    }

    public function about()
    {
        echo 'This is HomeController@about';
    }
}