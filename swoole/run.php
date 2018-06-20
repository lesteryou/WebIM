<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/7 16:59
 * Desc:
 */

require 'vendor/autoload.php';
require 'server/functions.php';

$databaseConfig = require_once 'config.php';

/**
 * Illuminate\Database
 */
$capsule = new Illuminate\Database\Capsule\Manager;
// 创建链接
$capsule->addConnection($databaseConfig);
// 设置全局静态可访问DB
$capsule->setAsGlobal();
// 启动Eloquent
//$capsule->bootEloquent();

$ws = new Server\WebSocketServer();