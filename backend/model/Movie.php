<?php

namespace TaraCatalog\Model;

use TaraCatalog\Model\Media;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;
use TaraCatalog\Config\HttpFailCodes;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\APIService;

class Movie
{
    public $id;
    public $user_id;
    public $title;
    public $format;
    public $edition;
    public $content_type;
    public $location;
    public $season;
    public $todo_list;
    public $image;
    public $mpaa_rating;
    public $notes;
    public $genre;
    public $running_time;
    public $rt_hours;
    public $complete_series;

    public $type;
    public $row_number;
    public $created;
    public $updated;
    public $active;

    public function __construct($data)
    {
        $this->id              = Media::set_property($data, "id", Constants::property_types()->num);
        $this->user_id         = Media::set_property($data, "user_id", Constants::property_types()->num);
        $this->title           = Media::set_property($data, "title");
        $this->edition         = Media::set_property($data, "edition");
        $this->season          = Media::set_property($data, "season");
        $this->todo_list       = Media::set_property($data, "todo_list", Constants::property_types()->bool, false);
        $this->image           = Media::set_property($data, "image");
        $this->notes           = Media::set_property($data, "notes");
        $this->genre           = Media::set_property($data, "genre");
        $this->running_time    = Media::set_property($data, "running_time", Constants::property_types()->num, 0);
        $this->rt_hours        = Movie::get_running_time_in_hours($this->running_time);

        /* Set Enums */
        $this->format          = Media::set_enum_property($data, 'format', Constants::movie_format(), Constants::movie_format()->dvd);
        $this->content_type    = Media::set_enum_property($data, 'content_type', Constants::movie_content_type(), Constants::movie_content_type()->live_action);
        $this->mpaa_rating     = Media::set_enum_property($data, 'mpaa_rating', Constants::movie_mpaa_rating());
        $this->location        = Media::set_enum_property($data, 'location', Constants::media_location(), Constants::media_location()->home);
        $this->complete_series = Media::set_enum_property($data, 'complete_series', Constants::media_complete_series(), Constants::media_complete_series()->incomplete);

        $this->type            = "movie";
        $this->row_number      = Media::set_property($data, "row_number", Constants::property_types()->num);
        $this->created         = Media::set_property($data, "created", Constants::property_types()->date, new \DateTime('now'));
        $this->updated         = Media::set_property($data, "updated", Constants::property_types()->date, new \DateTime('now'));
        $this->active          = Media::set_property($data, "active", Constants::property_types()->bool, true);
    }

    /* =====================================================
    * Database Functions
    * ===================================================== */

    /* ========================================================== *
    * POST
    * ========================================================== */

    /* Create a movie */
    public static function create_from_data($data)
    {
        $movie = new Movie($data);

        $data = array(
            "user_id"         => $movie->user_id,
            "title"           => $movie->title,
            "format"          => $movie->format,
            "edition"         => $movie->edition,
            "content_type"    => $movie->content_type,
            "location"        => $movie->location,
            "season"          => $movie->season,
            "todo_list"       => $movie->todo_list,
            "image"           => $movie->image,
            "mpaa_rating"     => $movie->mpaa_rating,
            "notes"           => $movie->notes,
            "genre"           => $movie->genre,
            "running_time"    => $movie->running_time,
            "complete_series" => $movie->complete_series,
            "created"         => $movie->created,
            "updated"         => $movie->updated,
            "active"          => $movie->active
        );

        $id = DatabaseService::create(Config::DBTables()->movie, $data);
        if($id === false || $id === null) {
            APIService::response_fail(HttpFailCodes::http_response_fail()->create_media);
        }
        $movie->id = $id;
        return $movie;
    }

    /* ========================================================== *
    * GET
    * ========================================================== */

    /* Count movies with different mpaa ratings, grouped by under PG and above PG */
    public static function get_all_mpaa_rating_counts_grouped($user_id)
    {
        $column_name = "mpaa_rating";
        $header = "movie_mpaa_grouped_rating_type";
        $counts = Media::get_counts_for_column(Config::DBTables()->movie, $user_id, $column_name, $header);

        $under_pg = array('G', 'PG', 'TV-Y', 'TV-Y7', 'TV-G', 'TV-PG');
        $over_pg = array('PG-13', 'R', 'TV-14', 'TV-MA');
        $under_pg_counts = 0;
        $over_pg_counts = 0;
        $none_counts = 0;

        foreach ($counts[$header] as $count){
            $rating = $count["type"];
            $num = intval($count["num"]);

            if (in_array($rating, $under_pg)){
                $under_pg_counts = $under_pg_counts + $num;
            }else if (in_array($rating, $over_pg)){
                $over_pg_counts = $over_pg_counts + $num;
            }else{
                $none_counts = $none_counts + $num;
            }
        }

        $grouped_counts = array(
            array("num" => $under_pg_counts, "type" => "Up To PG"),
            array("num" => $over_pg_counts, "type" => "Above PG"),
            array("num" => $none_counts, "type" => "None")
        );

        return array($header => $grouped_counts);
    }

    /* Sum movie running times */
    public static function sum_running_time($user_id, $table)
    {
        $database = Database::instance();
        $sql = "SELECT SUM(running_time) as running_time FROM " . $table . " WHERE active = 1 AND user_id = " . $user_id;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail(HttpFailCodes::http_response_fail()->running_time);
        }else{
            $total_minutes = intval($result["running_time"]);
            $result["hours"] = Movie::get_running_time_in_hours($total_minutes);
            return $result;
        }
    }

    private static function get_running_time_in_hours($runtime){
        $hours = floor($runtime / 60);
        $minutes = $runtime % 60;
        $hour_text = ($hours == 1) ? "hour" : "hours";
        $minute_text = ($minutes == 1) ? "minute" : "minutes";
        return $hours . " " . $hour_text . " and " . $minutes . " " . $minute_text;
    }

}
