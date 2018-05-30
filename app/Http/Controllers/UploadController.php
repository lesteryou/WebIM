<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/30 10:44
 * Desc:
 */

namespace App\Http\Controllers;


use Interop\Container\ContainerInterface;

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
}