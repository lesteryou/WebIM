<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/15 13:14
 * Desc:
 */

namespace App\Http\Controllers;


use App\Http\Models\GroupMessage;
use App\Http\Models\PrivateMessage;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class MessageController extends Controller
{
    /**
     * MessageController constructor.
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * 获取私聊/群聊的历史记录
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function history(Request $request, Response $response, $args)
    {
        $queryArr = $request->getQueryParams();
        trim_params($queryArr);
        if(empty($queryArr['id'])) TEA('450', 'id');
        if(empty($queryArr['type'])||($queryArr['type']!='friend'&&$queryArr['type']!='group')) TEA('450', 'group');
        $page = isset($queryArr['page']) ? $queryArr['page'] : 1;
        $pageSize = isset($queryArr['pageSize']) ? $queryArr['pageSize'] : 1;

        if ($queryArr['type'] == 'friend') {
            $PrivateMessage = new PrivateMessage();
            $dataList = $PrivateMessage->listHistoryMessage(1,$queryArr['id'],$page,$pageSize);
        }else{
            $GroupMessage = new GroupMessage();
            $dataList = $GroupMessage->listHistoryMessage($queryArr['id'],$page,$pageSize);
        }
        return $response->withJson(IM_ASS($dataList));
    }

}