<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/23 9:46
 * Desc:
 */

namespace App\Http\Controllers;


use App\Exceptions\TestException;
use App\Http\Models\User;
use App\Http\Models\UserLoginHistory;
use Interop\Container\ContainerInterface;
use Slim\Exception\SlimException;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Libraries\Verify;

class PassController extends Controller
{

    /**
     * PassController constructor.
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        empty($this->model) && $this->model = new User();

    }


    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function login(Request $request, Response $response, $args)
    {
        //如果已经登录，就禁止重复登录
        if (!empty(session('userInfo'))) {
            TEA('600');
        }
        $formData = $request->getParsedBody();
        if (empty($formData['username'])) TEA('450', 'username');
        if (empty($formData['password'])) TEA('450', 'password');
        if (empty($formData['captcha'])) TEA('450', 'captcha');
        $Verify = new Verify();
        !($Verify->check($formData['captcha'])) && TEA('605');


        //使用名称获取用户信息
        $userInfo = $this->model->getUserInfoByEmail($formData['username']);
        //用户不存在
        empty($userInfo) && TEA('601');

        //密码错误
        if (encrypted_password($formData['password']) != $userInfo->password) {
            TEA('602');
        }
        //添加上次登录时间
        $this->model->updateLastLogin($userInfo->id);
        //添加本次登录log
        (new UserLoginHistory())->addRecord($request, $userInfo->id);
        session(['userInfo' => $userInfo]);
        return $response->withJson(ASS(['uid' => $userInfo->id, 'nickname' => $userInfo->nickname, 'next_url' => '/']));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function register(Request $request, Response $response, $args)
    {
        $formData = $request->getParsedBody();
        if (empty($formData['nickname'])) TEA('450', 'nichname');
        if (empty($formData['username'])) TEA('450', 'username');
        if (empty($formData['password'])) TEA('450', 'password');
        if (empty($formData['captcha'])) TEA('450', 'captcha');
        //使用名称获取用户信息
        $userInfo = $this->model->getUserInfoByEmail($formData['username']);
        //该用户名已被注册
        !empty($userInfo) && TEA('603');
        $Verify = new Verify();
        !($Verify->check($formData['captcha'])) && TEA('605');
        $formData['create_ip'] = $request->getAttribute('ip_address');
        $uid = $this->model->register($formData);
        return $response->withJson(ASS(['uid' => $uid]));
    }

    public function makeCaptcha(Request $request, Response $response, $args)
    {
        $config = array(
            'imageW' => 0, //验证码宽度 设置为0为自动计算
            'imageH' => 0, //验证码高度 设置为0为自动计算
            'length' => 4, //验证码位数
            'fontSize' => 12, //验证码字体大小（像素） 默认为25
            'fontttf' => '5.ttf', //指定验证码字体 默认为随机获取
            'useImgBg' => false, //是否使用背景图纸 默认为false
            'useCurve' => false, //是否使用混淆曲线 默认为true
            'useNoise' => false, //是否添加杂点 默认为true
            'useZh' => false, //是否使用中文验证码
            'save' => true,//是否保存成图纸
        );
        //生成验证码
        $Verify = new Verify($config);
        $path = $Verify->entry();//同一个用户如果有多处交互界面需要用到验证码,则传递$id值即可,每处的值不同即可搞定了

        return $response->withJson(ASS(['captcha' => ltrim($path, '.')]));

    }
}