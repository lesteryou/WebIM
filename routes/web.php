<?php

/**
 * 引入其他路由文件
 * -----------------------------
 */
require_once __DIR__ . '/front.php';

require_once __DIR__ . '/api.php';

/**
 * routing example
 *
 * 路由闭包里面，以$this使用$app
 * -------------------------------------------------------------------
 */


/**
 * 使用视图层 + 中间件类
 */
$app->get('/hello/[/{name}]', function (Slim\Http\Request $request, Slim\Http\Response $response, $args) {
    $this->logger->info("Slim-Skeleton '/' route");
    $args = array('title' => 'Login', 'name' => $args['name']);
    return $this->view->render($response, 'login.html', $args);
})->add(new App\Http\Middleware\ExampleMiddleware());

/**
 * 使用 路由组 + 中间件闭包
 */
$app->group('/utils', function () use ($app) {
    $app->get('/date', function (Slim\Http\Request $request, Slim\Http\Response $response) {
        return $response->getBody()->write(date('Y-m-d H:i:s'));
    });
    $app->get('/time', function (Slim\Http\Request $request, Slim\Http\Response $response) {
        return $response->getBody()->write(time());
    });
})->add(function (Slim\Http\Request $request, Slim\Http\Response $response, $next) {
    $response->getBody()->write('It is now ');
    $response = $next($request, $response);
    $response->getBody()->write('. Enjoy!');

    return $response;
});

/**
 * 使用 控制器类
 */
$app->get('/Hello/ControllerClass', App\Http\Controllers\ExampleController::class . ':Index');

/**
 * end routing example
 * -------------------------------------------------------------------
 */
