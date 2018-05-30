<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/30 10:44
 * Desc:
 */

namespace App\Http\Controllers;


use App\Libraries\Upload;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class UploadController extends Controller
{
    /**
     * UploadController constructor.
     * @param ContainerInterface $container
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function image(Request $request, Response $response, $args)
    {
        if (!isset($_FILES['file'])) {
            TEA('630');
        }
        $config = [
            'path' => 'uploads/image/'
        ];
        $Upload = new Upload($config);
        if (!$Upload->upload("file")) {
            TEA('500', $Upload->getErrorMsg());
        }
        $responseData = [
            'src' => $Upload->getFilePath()
        ];

        return $response->withJson(IM_ASS($responseData));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function file(Request $request, Response $response, $args)
    {
        if (!isset($_FILES['file'])) {
            TEA('630');
        }
        $config = [
            'path' => 'uploads/file/'
        ];
        $Upload = new Upload($config);
        if (!$Upload->upload("file")) {
            TEA('500', $Upload->getErrorMsg());
        }
        $responseData = [
            'src' => $Upload->getFilePath(),
            'name' => $Upload->getFileName()
        ];
        return $response->withJson(IM_ASS($responseData));
    }
}