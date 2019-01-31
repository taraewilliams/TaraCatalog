<?php

namespace TaraCatalog\Config;

class Database
{
    private static $database = null;

    public static function instance()
    {
        if (self::$database == null){
            $db_host = "mysql:host=".Config::database()->host.";dbname=".Config::database()->database;
            self::$database = new \PDO($db_host, Config::database()->user, Config::database()->password);
        }
        return self::$database;
    }
}
