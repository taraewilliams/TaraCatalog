<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;

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
    public $row_number;
    public $image;
    public $type;

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
        $this->watch_list      = isset($data['watch_list']) ? (boolean) $data['watch_list'] : false;
        $this->row_number      = isset($data['row_number']) ? intval($data['row_number']) : null;
        $this->image           = isset($data['image']) ? $data['image'] : null;
        $this->type            = "movie";

        $this->created          = isset($data['created']) ? new \DateTime($data['created']) : new \DateTime('now');
        $this->updated          = isset($data['updated']) ? new \DateTime($data['updated']) : new \DateTime('now');
        $this->active           = isset($data['active']) ? (boolean) $data['active'] : true;
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
            "image"          => $movie->image,
            "watch_list"     => $movie->watch_list,
            "created"        => $movie->created,
            "updated"        => $movie->updated,
            "active"         => $movie->active
        );

        $id = DatabaseService::create(Config::DBTables()->movie, $data);
        if($id === false) {
            return false;
        }
        if($id === null) {
            return null;
        }
        $movie->id = $id;
        return $movie;
    }

    /* ========================================================== *
    * GET
    * ========================================================== */

    /* Get all movies */
    public static function get_all($user_id)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY title";
        $result = Movie::get($where, $order_by);
        if ($result === false){
            return false;
        }else{
            $movies = array();
            foreach( $result as $row ) {
                $movies[] = new Movie($row);
            }
            return $movies;
        }
    }

    /* Get a set number of movies */
    public static function get_all_with_limit($user_id, $offset = 0, $limit = 50)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY title";
        $limit_sql = "LIMIT " . $offset . ", " . $limit;
        $result = Movie::get($where, $order_by, $limit_sql);
        if ($result === false){
            return false;
        }else{
            $movies = array();
            foreach( $result as $row ) {
                $movies[] = new Movie($row);
            }
            return $movies;
        }
    }

    /* Get all movies ordered by a specific field */
    public static function get_all_with_order($user_id, $order)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY ". $order;
        $result = Movie::get($where, $order_by);
        if ($result === false){
            return false;
        }else{
            $movies = array();
            foreach( $result as $row ) {
                $movies[] = new Movie($row);
            }
            return $movies;
        }
    }

    /* Get all movies on the watch list */
    public static function get_all_on_watch_list($user_id, $watch)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id . " AND watch_list = " . $watch;
        $order_by = "ORDER BY title";
        $result = Movie::get($where, $order_by);
        if ($result === false){
            return false;
        }else{
            $movies = array();
            foreach( $result as $row ) {
                $movies[] = new Movie($row);
            }
            return $movies;
        }
    }

    /* Get movies for multiple filters */
    public static function get_for_filter_params($user_id, $data, $order=null){

        $where = "WHERE active = 1 AND user_id = " . $user_id;
        foreach ($data as $key => $value) {
            $where = $where . (isset($data[$key]) ? " AND " . $key . " LIKE '%" . $data[$key] . "%'" : "");
        }
        $order_by = is_null($order) ? "ORDER BY title" : "ORDER BY " . $order;
        $result = Movie::get($where, $order_by);
        if ($result === false){
            return false;
        }else{
            $movies = array();
            foreach( $result as $row ) {
                $movies[] = new Movie($row);
            }
            return $movies;
        }
    }

    /* Get movies for search */
    public static function get_for_search($user_id, $data, $order=null){

        $where = "WHERE (";
        $iter = 1;
        foreach ($data as $key => $value) {
            if ($iter == 1){
                $where = $where . (isset($data[$key]) ? $key . " LIKE '%" . $data[$key] . "%'" : "");
            }else{
                $where = $where . (isset($data[$key]) ? " OR " . $key . " LIKE '%" . $data[$key] . "%'" : "");
            }
            $iter += 1;
        }
        $where = $where . ") AND active = 1 AND user_id = " . $user_id;
        $order_by = is_null($order) ? "ORDER BY title" : "ORDER BY " . $order;
        $result = Movie::get($where, $order_by);
        if ($result === false){
            return false;
        }else{
            $movies = array();
            foreach( $result as $row ) {
                $movies[] = new Movie($row);
            }
            return $movies;
        }
    }

    /* Get a single movie */
    public static function get_from_id($user_id, $id)
    {
        $where = array("id" => $id, "user_id" => $user_id);
        $result = DatabaseService::get(Config::DBTables()->movie, $where);

        if($result === false || $result === null) {
            return false;
        }
        if(count($result) === 0) {
            return null;
        }
        return new Movie($result[0]);
    }

    /* Count all movies */
    public static function count_movies($user_id)
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 AND user_id = " . $user_id;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false){
            return false;
        }else{
            return $result;
        }
    }

    /* Count movies with different content types */
    public static function get_all_content_type_counts($user_id)
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num, content_type as type FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 AND user_id = " . $user_id . " GROUP BY content_type";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false){
            return false;
        }else{
            return array('movie_content_type' => $result);
        }
    }

    /* Count movies with different formats */
    public static function get_all_format_counts($user_id)
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num, format as type FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 AND user_id = " . $user_id . " GROUP BY format";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false){
            return false;
        }else{
            return array('movie_format_type' => $result);
        }
    }

    /* Get a movie's title for its ID */
    public static function get_title_for_id($user_id, $id){
        $database = Database::instance();
        $sql = "SELECT title FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 AND user_id = " . $user_id . " AND id = " . $id;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false){
            return false;
        }else{
            return $result["title"];
        }
    }

    /* ========================================================== *
    * UPDATE
    * ========================================================== */

    /* Update a movie */
    public static function update($user_id, $id, $data)
    {
        $result = DatabaseService::update(Config::DBTables()->movie, $id, $data);

        if($result === false) {
            return false;
        }
        if($result === null) {
            return null;
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
        return $result;
    }

    /* ===================================================== *
    * Public Functions
    * ===================================================== */

    /* Set movie image */
    public static function set_image($files, $title)
    {
        $file_prefix = $title;
        $dir = FileService::MAIN_DIR . '/movies';
        $file_name = FileService::upload_file($files['image'], $dir, $file_prefix);
        if(!$file_name) {
            APIService::response_error("There was an error saving the picture.");
        }
        return $file_name;
    }

    /* ===================================================== *
    * Private Functions
    * ===================================================== */

    /* Generic get movies function */
    private static function get($where = null, $order_by = null, $limit = null){
        $database = Database::instance();
        $where_sql = is_null($where) ? "" : " " . $where;
        $order_by_sql = is_null($order_by) ? "" : " " . $order_by;
        $limit_sql = is_null($limit) ? "" : " " . $limit;
        $sql = "SELECT *, @curRow := @curRow + 1 AS row_number FROM " . CONFIG::DBTables()->movie . " JOIN(SELECT @curRow := 0) r". $where_sql . $order_by_sql . $limit_sql;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $result;
    }
}
