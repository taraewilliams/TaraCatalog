<?php

namespace TaraCatalog\Model;

use TaraCatalog\Model\Media;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;
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
        $this->todo_list      = isset($data['todo_list']) ? (boolean) $data['todo_list'] : false;
        $this->image           = isset($data['image']) ? $data['image'] : null;
        $this->mpaa_rating     = isset($data['mpaa_rating']) ? $data['mpaa_rating'] : null;
        $this->notes           = isset($data['notes']) ? $data['notes'] : null;

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
            "todo_list"     => $movie->todo_list,
            "image"          => $movie->image,
            "mpaa_rating"    => $movie->mpaa_rating,
            "notes"          => $movie->notes,
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

    /* ========================================================== *
    * GET MOVIES
    * ========================================================== */

    /* Get a single movie */
    public static function get_from_id($user_id, $id)
    {
        $result = Media::get_from_id($user_id, $id, Config::DBTables()->movie);
        return new Movie($result);
    }

    /* Get all movies */
    public static function get_all($user_id)
    {
        $order_by = "ORDER BY title,season,mpaa_rating";
        return Media::get_all($user_id, CONFIG::DBTables()->movie, $order_by);
    }

    /* Get all movies on the to do list */
    public static function get_all_on_todo_list($user_id, $todo)
    {
        $order_by = "ORDER BY title,season,mpaa_rating";
        return Media::get_all_on_todo_list($user_id, $todo, CONFIG::DBTables()->movie, $order_by);
    }

    /* Get a set number of movies */
    public static function get_all_with_limit($user_id, $offset = 0, $limit = 50)
    {
        $order_by = "ORDER BY title,season,mpaa_rating";
        return Media::get_all_with_limit($user_id, CONFIG::DBTables()->movie, $order_by, $offset, $limit);
    }

    /* Get all movies ordered by a specific field */
    public static function get_all_with_order($user_id, $order)
    {
        return Media::get_all_with_order($user_id, CONFIG::DBTables()->movie, $order);
    }

    /* Get movies for search parameters */
    /* AND for filter by specific column */
    /* OR for search all columns */
    public static function get_for_search($user_id, $data, $conj="AND", $order=null)
    {
        $order_by = is_null($order) ? "ORDER BY title,season,mpaa_rating" : "ORDER BY " . $order;
        $enum_keys = array("format", "content_type", "location", "mpaa_rating");
        return Media::get_for_search($user_id, CONFIG::DBTables()->movie, $data, $order_by, $enum_keys, $conj);
    }

    /* ========================================================== *
    * GET MOVIE COUNTS
    * ========================================================== */

    /* Count all movies */
    public static function count_movies($user_id)
    {
        return Media::count_media($user_id, CONFIG::DBTables()->movie);
    }

    /* Count movies with different content types */
    public static function get_all_content_type_counts($user_id)
    {
        $column_name = "content_type";
        $header = "movie_content_type";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->movie, $user_id, $column_name, $header);
    }

    /* Count movies with different formats */
    public static function get_all_format_counts($user_id)
    {
        $column_name = "format";
        $header = "movie_format_type";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->movie, $user_id, $column_name, $header);
    }

    /* Count all movies, grouped by mpaa rating */
    public static function get_all_mpaa_rating_counts($user_id)
    {
        $column_name = "mpaa_rating";
        $header = "movie_mpaa_rating_type";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->movie, $user_id, $column_name, $header);
    }

    /* Count movies with different mpaa ratings, grouped by under PG and above PG */
    public static function get_all_mpaa_rating_counts_grouped($user_id)
    {
        $column_name = "mpaa_rating";
        $header = "movie_mpaa_grouped_rating_type";
        $counts = DatabaseService::get_counts_for_column(CONFIG::DBTables()->movie, $user_id, $column_name, $header);

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

    /* Count movies with different locations */
    public static function get_all_location_counts($user_id)
    {
        $column_name = "location";
        $header = "movie_location";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->movie, $user_id, $column_name, $header);
    }

    /* ========================================================== *
    * GET A SINGLE VALUE FOR A COLUMN
    * ========================================================== */

    /* Get a movie's title for its ID */
    public static function get_title_for_id($user_id, $id){
        $column_name = "title";
        return Media::get_column_value_for_id($user_id, $id, CONFIG::DBTables()->movie, $column_name);
    }

    /* ========================================================== *
    * UPDATE
    * ========================================================== */

    /* Update a movie */
    public static function update($user_id, $id, $data)
    {
        $result = DatabaseService::update(Config::DBTables()->movie, $id, $data);
        if ($result === false || $result === null){
            APIService::response_fail("Update failed.", 500);
        }
        return $result ? self::get_from_id($user_id, $id) : false;
    }

    /* ========================================================== *
    * DELETE
    * ========================================================== */

    /* Delete a movie */
    public static function set_active($id, $active)
    {
        $result = DatabaseService::set_active(Config::DBTables()->movie, $id, $active);
        if( $result === false || $result === null) {
            APIService::response_fail("There was an error deleting the movie.", 500);
        }
        return $result;
    }

    /* ===================================================== *
    * Public Functions
    * ===================================================== */

}
