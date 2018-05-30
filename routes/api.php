<?php

use App\Http\Controllers;

/**
 * 通行证相关
 */
$app->group('/pass', function () {
    $this->post('/login', Controllers\PassController::class . ':login');
    $this->get('/captcha[/{time}]', Controllers\PassController::class . ':makeCaptcha');
    $this->post('/register', Controllers\PassController::class . ':register');
});


/**
 * main
 */
$app->group('/main', function () {
    $this->get('/init', Controllers\MainController::class . ':init');
    $this->get('/group/members', Controllers\GroupController::class . ':listMembers');
});

/**
 * upload
 */
$app->group('/upload', function () {
    $this->post('/image', Controllers\UploadController::class . ':image');
    $this->post('/file', Controllers\UploadController::class . ':file');
});
