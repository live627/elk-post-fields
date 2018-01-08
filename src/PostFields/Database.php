<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */

namespace ElkArte\addons\PostFields;

class Database
{
    use SingletonTrait;

    private static $db;

    /**
     * Handler to ElkArte's database functions.
     *
     * @param string $name      The name (or key) of the $smcFunc you are calling.
     * @param string $arguments This is an array of all arguments passed to the method.
     *
     * @return mixed The $db return value or false if not found.
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::$db, $name], $arguments);
    }

    public function __construct()
    {
        self::$db = database();
    }
}
