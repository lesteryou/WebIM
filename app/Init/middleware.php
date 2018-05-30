<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/23 13:35
 * Desc:
 */

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
$checkProxyHeaders = true;
$trustedProxies = ['10.0.0.1', '10.0.0.2'];
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));