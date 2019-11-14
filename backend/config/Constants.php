<?php

namespace TaraCatalog\Config;

class Constants
{
    public static function property_types()
    {
        return (object) array(
          "string"    => "string",
          "date"      => "date",
          "bool"      => "bool",
          "num"       => "num"
        );
    }

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
          "book"    => array("cover_type", "content_type", "location", "complete_series"),
          "movie"   => array("format", "content_type", "location", "mpaa_rating", "complete_series"),
          "game"    => array("location", "esrb_rating", "complete_series")
        );
    }

    /* Book Enums */

    public static function book_cover_type(){
        return (object) array(
          "hardcover"    => "Hardcover",
          "paperback"    => "Paperback",
        );
    }

    public static function book_content_type(){
        return (object) array(
          "comic_book"  => "Comic Book",
          "manga"       => "Manga",
          "novel"       => "Novel"
        );
    }

    /* Movie Enums */

    public static function movie_format(){
        return (object) array(
            "blu_ray"      => "Blu-Ray",
            "dvd"          => "DVD",
            "combo"        => "DVD/Blu-Ray Combo"
        );
    }

    public static function movie_content_type(){
        return (object) array(
            "animated"      => "Animated",
            "anime"         => "Anime",
            "live_action"   => "Live Action"
        );
    }

    public static function movie_mpaa_rating(){
        return (object) array(
            "g"             => "G",
            "pg"            => "PG",
            "pg13"          => "PG-13",
            "r"             => "R",
            "nc17"          => "NC-17",
            "not_rated"     => "Not Rated",
            "unrated"       => "Unrated",
            "tvy"           => "TV-Y",
            "tvy7"          => "TV-Y7",
            "tvg"           => "TV-G",
            "tvpg"          => "TV-PG",
            "tv14"          => "TV-14",
            "tvma"          => "TV-MA",
            "none"          => "none"

        );
    }

    /* Game Enums */

    public static function game_esrb_rating(){
        return (object) array(
            "rp"        => "RP",
            "ec"        => "EC",
            "e"         => "E",
            "e10"       => "E10+",
            "t"         => "T",
            "m"         => "M",
            "ao"        => "AO",
            "none"      => "none",
            "ka"        => "KA"
        );
    }

    /* Media Enums */

    public static function media_location(){
        return (object) array(
          "apartment"  => "Apartment",
          "home"       => "Home"
      );
    }

    public static function media_complete_series(){
        return (object) array(
          "complete"    => "Complete",
          "incomplete"  => "Incomplete",
          "standalone"  => "Standalone"
      );
    }

    /* User Enums */

    public static function user_role(){
        return (object) array(
            "creator"      => "creator",
            "viewer"       => "viewer"
        );
    }

    public static function user_color_scheme(){
        return (object) array(
            "blue"      => "blue",
            "gray"      => "gray",
            "green"     => "green",
            "orange"    => "orange",
            "pink"      => "pink",
            "purple"    => "purple",
            "red"       => "red",
            "yellow"    => "yellow"
        );
    }

    /* Viewer Enums */

    public static function viewer_requested_by(){
        return (object) array(
            "creator"      => "creator",
            "viewer"       => "viewer"
        );
    }

    public static function viewer_status(){
        return (object) array(
            "approved"      => "approved",
            "pending"       => "pending",
            "rejected"      => "rejected"
        );
    }

}
