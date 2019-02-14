<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;

class Movie
{
    public $id;
    public $title;
    public $format;
    public $edition;
    public $content_type;
    public $image;

    public $created;
    public $updated;
    public $active;

    public function __construct($data)
    {
        $this->id              = isset($data['id']) ? intval($data['id']) : null;
        $this->title           = isset($data['title']) ? $data['title'] : null;
        $this->format          = isset($data['format']) ? $data['format'] : "DVD";
        $this->edition         = isset($data['edition']) ? $data['edition'] : null;
        $this->content_type    = isset($data['content_type']) ? $data['content_type'] : "Live Action";
        $this->image           = isset($data['image']) ? $data['image'] : null;

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

    public static function create_from_data($data)
    {
        $movie = new Movie($data);

        $data = array(
            "title"          => $movie->title,
            "format"         => $movie->format,
            "edition"        => $movie->edition,
            "content_type"   => $movie->content_type,
            "image"          => $movie->image,
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

     public static function get_all()
     {
         $database = Database::instance();
         $sql = "SELECT * FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 ORDER BY title";
         $query = $database->prepare($sql);
         $query->execute();
         $result = $query->fetchAll(\PDO::FETCH_ASSOC);
         $query->closeCursor();
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

     public static function get_all_with_limit($offset = 0, $limit = 50)
     {
       $database = Database::instance();
       $sql = "SELECT * FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 ORDER BY title LIMIT " . $offset . ", " . $limit;
       $query = $database->prepare($sql);
       $query->execute();
       $result = $query->fetchAll(\PDO::FETCH_ASSOC);
       $query->closeCursor();
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

    public static function get_from_id($id)
    {
        $where = array("id" => $id);
        $result = DatabaseService::get(Config::DBTables()->movie, $where);

        if($result === false || $result === null) {
            return false;
        }
        if(count($result) === 0) {
            return null;
        }
        return new Movie($result[0]);
    }

    public static function count_movies()
    {
       $database = Database::instance();
       $sql = "SELECT COUNT(*) as num_movies FROM " . CONFIG::DBTables()->movie . " WHERE active = 1";
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

    public static function get_all_content_type_counts()
    {
       $database = Database::instance();
       $sql = "SELECT COUNT(*) as num, content_type as type FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 GROUP BY content_type";
       $query = $database->prepare($sql);
       $query->execute();
       $result = $query->fetchAll(\PDO::FETCH_ASSOC);
       $query->closeCursor();
       if ($result === false){
          return false;
       }else{
          return $result;
       }
    }

    public static function get_all_format_counts()
    {
       $database = Database::instance();
       $sql = "SELECT COUNT(*) as num, format as type FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 GROUP BY format";
       $query = $database->prepare($sql);
       $query->execute();
       $result = $query->fetchAll(\PDO::FETCH_ASSOC);
       $query->closeCursor();
       if ($result === false){
          return false;
       }else{
          return $result;
       }
    }

    public static function get_title_for_id($id){
        $database = Database::instance();
        $sql = "SELECT title FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 AND id = " . $id;
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

    public static function update($id, $data)
    {
        $result = DatabaseService::update(Config::DBTables()->movie, $id, $data);

        if($result === false) {
            return false;
        }
        if($result === null) {
            return null;
        }
        return $result ? self::get_from_id($id) : false;
    }

    /* ========================================================== *
     * DELETE
     * ========================================================== */

    public static function set_active($id, $active)
    {
        $result = DatabaseService::set_active(Config::DBTables()->movie, $id, $active);
        return $result;
    }

    /* ===================================================== *
     * Public Functions
     * ===================================================== */

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

}
