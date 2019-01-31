<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;

class Book
{
    public $id;
    public $title;
    public $author;
    public $volume;
    public $isbn;
    public $cover_type;
    public $content_type;
    public $location;

    public $created;
    public $updated;
    public $active;

    public function __construct($data)
    {
        $this->id              = isset($data['id']) ? intval($data['id']) : null;
        $this->title           = isset($data['title']) ? $data['title'] : null;
        $this->author          = isset($data['author']) ? $data['author'] : null;
        $this->volume          = isset($data['volume']) ? intval($data['volume']) : null;
        $this->isbn            = isset($data['isbn']) ? $data['isbn'] : null;
        $this->cover_type      = isset($data['cover_type']) ? $data['cover_type'] : "Paperback";
        $this->content_type    = isset($data['content_type']) ? $data['content_type'] : "Novel";
        $this->location        = isset($data['location']) ? $data['location'] : "Home";

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
        $book = new Book($data);

        $data = array(
            "title"          => $book->title,
            "author"         => $book->author,
            "volume"         => $book->volume,
            "isbn"           => $book->isbn,
            "cover_type"     => $book->cover_type,
            "content_type"   => $book->content_type,
            "location"       => $book->location,
            "created"        => $book->created,
            "updated"        => $book->updated,
            "active"         => $book->active
        );

        $id = DatabaseService::create(Config::DBTables()->book, $data);
        if($id === false) {
            return false;
        }
        if($id === null) {
            return null;
        }
        $book->id = $id;
        return $book;
    }

    /* ========================================================== *
     * GET
     * ========================================================== */

     public static function get_all()
     {
         $result = DatabaseService::get(Config::DBTables()->book);
         if($result === false || $result === null) {
             return false;
         }
         $books = array();
         foreach( $result as $row ) {
             $books[] = new Book($row);
         }
         usort($books, array("TaraCatalog\Model\Book", "sort_books"));
         return $books;
     }

     public static function get_all_with_limit($offset = 0, $limit = 50)
     {
       $database = Database::instance();
       $sql = "SELECT * FROM " . CONFIG::DBTables()->book . " WHERE active = 1 ORDER BY title LIMIT " . $offset . ", " . $limit;
       $query = $database->prepare($sql);
       $query->execute();
       $result = $query->fetchAll(\PDO::FETCH_ASSOC);
       $query->closeCursor();
       if ($result === false){
          return false;
       }else{
           $books = array();
           foreach( $result as $row ) {
               $books[] = new Book($row);
           }
           return $books;
       }
     }

    public static function get_from_id($id)
    {
        $where = array("id" => $id);
        $result = DatabaseService::get(Config::DBTables()->book, $where);

        if($result === false || $result === null) {
            return false;
        }
        if(count($result) === 0) {
            return null;
        }
        return new Book($result[0], true);
    }

    public static function count_books()
    {
       $database = Database::instance();
       $sql = "SELECT COUNT(*) as num_books FROM " . CONFIG::DBTables()->book . " WHERE active = 1";
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
       $sql = "SELECT COUNT(*) as num_books, content_type FROM " . CONFIG::DBTables()->book . " WHERE active = 1 GROUP BY content_type";
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

    public static function get_authors()
    {
       $database = Database::instance();
       $sql = "SELECT DISTINCT author FROM " . CONFIG::DBTables()->book . " WHERE active = 1 ORDER BY author";
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

    public static function get_titles()
    {
       $database = Database::instance();
       $sql = "SELECT DISTINCT title FROM " . CONFIG::DBTables()->book . " WHERE active = 1 ORDER BY title";
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

    /* ========================================================== *
     * UPDATE
     * ========================================================== */

    public static function update($id, $data)
    {
        $result = DatabaseService::update(Config::DBTables()->book, $id, $data);

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
        $result = DatabaseService::set_active(Config::DBTables()->book, $id, $active);
        return $result;
    }

    /* ===================================================== *
     * Private Functions
     * ===================================================== */

    private static function sort_books($a, $b)
    {
        return strcmp($a->title, $b->title);
    }
}
