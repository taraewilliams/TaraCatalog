<?php

namespace TaraCatalog\Model;

use TaraCatalog\Model\Media;
use TaraCatalog\Config\Config;
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

    public $type;
    public $row_number;
    public $created;
    public $updated;
    public $active;

    public function __construct($data)
    {
        $this->id              = isset($data['id']) ? intval($data['id']) : null;
        $this->user_id         = isset($data['user_id']) ? intval($data['user_id']) : null;
        $this->title           = isset($data['title']) ? $data['title'] : null;
        $this->format          = isset($data['format']) ? $data['format'] : "DVD";
        $this->edition         = isset($data['edition']) ? $data['edition'] : null;
        $this->content_type    = isset($data['content_type']) ? $data['content_type'] : "Live Action";
        $this->location        = isset($data['location']) ? $data['location'] : "Home";
        $this->season          = isset($data['season']) ? $data['season'] : null;
        $this->todo_list       = isset($data['todo_list']) ? (boolean) $data['todo_list'] : false;
        $this->image           = isset($data['image']) ? $data['image'] : null;
        $this->mpaa_rating     = isset($data['mpaa_rating']) ? $data['mpaa_rating'] : null;
        $this->notes           = isset($data['notes']) ? $data['notes'] : null;
        $this->genre           = isset($data['genre']) ? $data['genre'] : null;

        $this->type            = "movie";
        $this->row_number      = isset($data['row_number']) ? intval($data['row_number']) : null;
        $this->created         = isset($data['created']) ? new \DateTime($data['created']) : new \DateTime('now');
        $this->updated         = isset($data['updated']) ? new \DateTime($data['updated']) : new \DateTime('now');
        $this->active          = isset($data['active']) ? (boolean) $data['active'] : true;
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
            "user_id"        => $movie->user_id,
            "title"          => $movie->title,
            "format"         => $movie->format,
            "edition"        => $movie->edition,
            "content_type"   => $movie->content_type,
            "location"       => $movie->location,
            "season"         => $movie->season,
            "todo_list"      => $movie->todo_list,
            "image"          => $movie->image,
            "mpaa_rating"    => $movie->mpaa_rating,
            "notes"          => $movie->notes,
            "genre"          => $movie->genre,
            "created"        => $movie->created,
            "updated"        => $movie->updated,
            "active"         => $movie->active
        );

        $id = DatabaseService::create(Config::DBTables()->movie, $data);
        if($id === false || $id === null) {
            APIService::response_fail("There was a problem creating the movie.", 500);
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

}
