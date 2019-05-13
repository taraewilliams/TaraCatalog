<?php

namespace TaraCatalog\Config;

use TaraCatalog\Config\Secret;

class Config
{
    public static function database()
    {
        return Secret::database();
    }

    public static function DBTables()
    {
        return (object) array(
          "book"    => "book",
          "movie"   => "movie",
          "game"    => "game",
          "user"    => "user",
          "session" => "session",
          "viewer"  => "viewer"
        );
    }

}
