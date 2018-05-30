<?php

// DIC configuration
$container = $app->getContainer();

// view twig-view
$container['view'] = function (\Slim\Container $c) {
    $settings = $c->get('settings')['twig_view'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
//        'cache' => $settings['cache']
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));
    return $view;
};

// monolog
$container['logger'] = function (\Slim\Container $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

/**
 * rewrite notAllowedHandler
 * @param \Slim\Container $c
 * @return Closure
 */
$container['notAllowedHandler'] = function (\Slim\Container $c) {
    return function ($request, $response, $methods) use ($c) {
        return $c['response']->withJson(
            [
                'msg' => 'Method must be one of: ' . implode(', ', $methods),
                'code' => 405
            ]
        );
    };
};

/**
 * rewrite errorHandler
 * 处理自定义异常
 *
 * Slim\Handlers\Error 是slim内置的异常处理类，所有的异常都由此处理。
 *
 * @param \Slim\Container $c
 * @return Closure
 */
$container['errorHandler'] = function (\Slim\Container $c) {
    return function (\Slim\Http\Request $request, \Slim\Http\Response $response, \Exception $e) use ($c) {

        //处理自定义的异常
        if ($e instanceof \App\Exceptions\ApiException) {
            $res = get_api_response($e->getCode(), $e->getMessage());
            return $c['response']
                ->withJson($res);
        }
//        if($e instanceof \App\Exceptions\TestException){
////            $res = get_api_response($e->getCode(), $e->getMessage());
//            return $c['response']->withStatus(500)
//                ->withJson('Test_exception_in_container');
//        }
        //Database 异常
        if ($e instanceof \Illuminate\Database\QueryException) {
            $res = [
                'code' => 500,
                "message" => "Database_error",
                'error_code' => $e->getCode(),
                'error_msg' => $e->getMessage(),
            ];
            return $c['response']->withStatus(500)
                ->withJson($res);
        }
        return (new Slim\Handlers\Error)($request, $response, $e);
    };
};

/**
 * Illuminate\Database
 */
$capsule = new Illuminate\Database\Capsule\Manager;
// 创建链接
$capsule->addConnection($container->get('settings')['database']);
// 设置全局静态可访问DB
$capsule->setAsGlobal();
// 启动Eloquent
//$capsule->bootEloquent();


