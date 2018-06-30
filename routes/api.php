<?php

use App\Http\Controllers;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * 通行证相关
 */
$app->group('/pass', function () {
    $this->post('/login', Controllers\PassController::class . ':login');
    $this->get('/captcha[/{time}]', Controllers\PassController::class . ':makeCaptcha');
    $this->post('/register', Controllers\PassController::class . ':register');
    $this->post('/checkRegister', Controllers\PassController::class . ':checkIsExisted');
});


/**
 * main
 */
$app->group('/main', function () {
    $this->get('/init', Controllers\MainController::class . ':init');
    $this->get('/applyList', Controllers\MainController::class . ':listApply');
    $this->get('/applyNum', Controllers\MainController::class . ':getApplyNum');
    $this->post('/doApply', Controllers\MainController::class . ':doApply');

    $this->get('/message/history', Controllers\MessageController::class . ':history');

    $this->get('/group/members', Controllers\GroupController::class . ':listMembers');
    $this->get('/group/search', Controllers\GroupController::class . ':search');
    $this->post('/group/apply', Controllers\GroupController::class . ':apply');

    $this->post('/friendGroup/add', Controllers\FriendGroupController::class . ':add');
    $this->post('/friendGroup/delete', Controllers\FriendGroupController::class . ':delete');
    $this->post('/friendGroup/rename', Controllers\FriendGroupController::class . ':rename');

    $this->get('/friend/search', Controllers\FriendController::class . ':search');
    $this->post('/friend/apply', Controllers\FriendController::class . ':apply');
});

/**
 * upload
 */
$app->group('/upload', function () {
    $this->post('/image', Controllers\UploadController::class . ':image');
    $this->post('/file', Controllers\UploadController::class . ':file');
});
