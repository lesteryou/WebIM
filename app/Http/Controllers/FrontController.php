<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/7 21:43
 * Desc:
 */

namespace App\Http\Controllers;


use App\Http\Models\User;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Http\Models\PrivateMessage;
use App\Http\Models\GroupMessage;

class FrontController extends Controller
{

    /**
     * FrontController constructor.
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
     */
    public function index(Request $request, Response $response, $args)
    {
        if (empty(session('userInfo'))) {
            return $response->withHeader('Location', '/login');
        }
        $uid = session('userInfo')->id;
        $_token = session('userInfo')->_token;
        $cookieValue = 'uid=' . $uid . ';_token=' . $_token;
        $response->withHeader('Set-Cookie', $cookieValue);
        return $this->view->render($response, 'main.html', ['uid' => $uid, '_token' => $_token]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function chatLog(Request $request, Response $response, $args)
    {
        $queryArr = $request->getQueryParams();
        trim_params($queryArr);
        if (empty($queryArr['id']) || empty($queryArr['type']) || ($queryArr['type'] != 'friend' && $queryArr['type'] != 'group')) {
            return $response->withHeader('Location', '/login');
        }
        if ($queryArr['type'] == 'friend') {
            $PrivateMessage = new PrivateMessage();
            $dataList = $PrivateMessage->listHistoryMessage(1, $queryArr['id']);
        } else {
            $GroupMessage = new GroupMessage();
            $dataList = $GroupMessage->listHistoryMessage($queryArr['id']);
        }
        return $this->view->render($response, 'main/chatLog.html', ['count' => $dataList['count']]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function find(Request $request, Response $response, $args)
    {
        $queryArr = $request->getQueryParams();
        trim_params($queryArr);
        return $this->view->render($response, 'main/find.html', $args);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function msgBox(Request $request, Response $response, $args)
    {
        $queryArr = $request->getQueryParams();
        trim_params($queryArr);
        return $this->view->render($response, 'main/msgBox.html', $args);
    }
}