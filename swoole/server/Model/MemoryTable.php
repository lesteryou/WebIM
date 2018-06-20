<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/10 19:30
 * Desc:
 */

namespace Server\Model;

use Swoole\Table;

class MemoryTable
{
    public static $tables = [];

    public function __construct()
    {
    }

    /**
     * @param string $tableName
     * @param int $size
     * @param array $columns
     */
    public function createTable($tableName, $size = 1024, $columns)
    {
        self::$tables[$tableName] = new Table($size);
        foreach ($columns as $key => $value) {
            $s = 4;
            if (is_array($value) && count($value) == 3) {
                list($name, $s, $type) = $value;
            } else {
                $name = $value;
                $type = 1;
            }
            $type = self::typeIntToString($type);
            self::$tables[$tableName]->column($name, $type, $s);
        }
        self::$tables[$tableName]->create();
    }

    /**
     * @param $tableName
     * @return bool|mixed
     */
    public static function table($tableName)
    {
        return isset(self::$tables[$tableName]) ? self::$tables[$tableName] : false;
    }

    public static function typeIntToString($type)
    {
        switch ($type) {
            case 2:
                return Table::TYPE_FLOAT;
                break;
            case 3:
                return Table::TYPE_STRING;
                break;
            case 1:
            default:
                return Table::TYPE_INT;
        }
    }
}