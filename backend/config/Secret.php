<?php

namespace TaraCatalog\Config;

class Secret
{
    public static function database()
    {
        return (object) array(
            "host"      => "localhost",
            "database"  => "tara_catalog",
            "user"      => "root",
            "password"  => "root"
        );
    }
}
