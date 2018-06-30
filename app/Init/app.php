<?php
/**
 * Set the session be in work.
 */
!isset($_SESSION) && session_start();

/**
 *  require autoload
 */
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * The common functions
 */
require_once __DIR__ . '/../Helpers/functions.php';

/**
 * The config
 */
$appSettings = require_once __DIR__ . '/../../config/app.php';

/**
 * create the application
 */
$app = new \Slim\App($appSettings);

/**
 * register dependencies
 */
require_once __DIR__ . '/dependencies.php';

/**
 * register middleware
 */
require_once __DIR__ . '/middleware.php';

/**
 * include routes
 */
require_once __DIR__ . '/../../routes/web.php';


return $app;