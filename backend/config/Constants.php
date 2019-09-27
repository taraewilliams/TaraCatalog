<?php

namespace TaraCatalog\Config;

class Constants
{
    public static function default_order()
    {
        return (object) array(
          "book"    => "ORDER BY COALESCE(NULLIF(series,''), title), volume, title, author",
          "movie"   => "ORDER BY title,season,mpaa_rating",
          "game"    => "ORDER BY title,platform,esrb_rating"
        );
    }

    public static function enum_columns()
    {
        return (object) array(
          "book"    => array("cover_type", "content_type", "location"),
          "movie"   => array("format", "content_type", "location", "mpaa_rating"),
          "game"    => array("location", "esrb_rating")
        );
    }

}
