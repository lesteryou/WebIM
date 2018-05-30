<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/22 15:26
 * Desc:
 */

namespace App\Http\Models;


class Model
{
    /**
     * 相关联的表名
     * @var string $table
     */
    protected $table;

    /**
     * 默认的表的主键字段
     * @var string $primaryKey
     */
    public $primaryKey = 'id';

    /**
     * api主键名称
     * @var string $apiPrimaryKey
     */
    protected $apiPrimaryKey;

    public function __construct()
    {
        //null
    }

}