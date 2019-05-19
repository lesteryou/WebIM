<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/4/27 16:29
 * Desc:
 */
ini_set('display_errors', 0);
date_default_timezone_set('Asia/Shanghai');

$app = require_once __DIR__ . '/../app/Init/app.php';

$app->run();