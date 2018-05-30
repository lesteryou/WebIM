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

$app->get('/', function (Request $request, Response $response, $args) {
    return $this->view->render($response, 'main.html', $args);
});

$app->get('/login', function (Request $request, Response $response, $args) {
    return $this->view->render($response, 'login.html', $args);
});