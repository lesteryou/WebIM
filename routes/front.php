<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/7 17:59
 * Desc: 前端路由
 */

use Slim\Http\Request;
use Slim\Http\Response;
use App\Http\Controllers;

$app->get('/', Controllers\FrontController::class.':index')
    ->setName('login');

$app->get('/login', function (Request $request, Response $response, $args) {
    return $this->view->render($response, 'login.html', $args);
});
$app->get('/register', function (Request $request, Response $response, $args) {
    return $this->view->render($response, 'register.html', $args);
});

$app->get('/test', function (Request $request, Response $response, $args) {
    return $this->view->render($response, 'right.html', $args);
});



$app->group('/front',function (){
    $this->get('/main/find', Controllers\FrontController::class.':find');
    $this->get('/main/chatLog', Controllers\FrontController::class.':chatLog');
    $this->get('/main/msgBox', function (Request $request, Response $response, $args) {
        return $this->view->render($response, 'find.html', $args);
    });
});