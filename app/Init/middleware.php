<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/23 13:35
 * Desc:
 */

use Illuminate\Database\Capsule\Manager as DB;

/**
 * 重定向，去除url最后的 “/”
 */
//$app->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) {
//    $uri = $request->getUri();
//    $path = $uri->getPath();
//    if ($path != '/' && substr($path, -1) == '/') {
//        // permanently redirect paths with a trailing slash
//        // to their non-trailing counterpart
//        $uri = $uri->withPath(substr($path, 0, -1));
//        return $response->withRedirect((string)$uri, 301);
//    }
//    return $next($request, $response);
//});

/**
 * 获取客户端ip组件
 * 使用 $request->getAttribute('ip_address'); 获取ip地址
 */
$checkProxyHeaders = true; // Note: Never trust the IP address for security processes!
$trustedProxies = ['10.0.0.1', '10.0.0.2']; // Note: Never trust the IP address for security processes!
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));

$app->add(function (Slim\Http\Request $request, Slim\Http\Response $response, $next) {
    $logger = $this->get('logger');

    $requestData = $request->getParams();

    $logger->req->Info($request->getUri(),is_array($requestData)?$requestData:[$requestData]);

    $response = $next($request, $response);
    return $response;
});