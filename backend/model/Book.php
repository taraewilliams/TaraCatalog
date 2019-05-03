<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;

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
    public $read_list;
    public $image;
    public $row_number;

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
        $this->read_list       = isset($data['read_list']) ? (boolean) $data['read_list'] : false;
        $this->image           = isset($data['image']) ? $data['image'] : null;
        $this->row_number      = isset($data['row_number']) ? intval($data['row_number']) : null;

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

    /* Create a book */
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
            "read_list"      => $book->read_list,
            "image"          => $book->image,
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

    /* Get all books */
    public static function get_all()
    {
        $where = "WHERE active = 1";
        $order_by = "ORDER BY title,author,volume";
        $result = Book::get($where, $order_by);
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

    /* Get all books on the read list */
    public static function get_all_on_read_list($read)
    {
        $where = "WHERE active = 1 AND read_list = " . $read;
        $order_by = "ORDER BY title,author,volume";
        $result = Book::get($where, $order_by);
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

    /* Get a set number of books */
    public static function get_all_with_limit($offset = 0, $limit = 50)
    {
        $where = "WHERE active = 1";
        $order_by = "ORDER BY title,author,volume";
        $limit_sql = "LIMIT " . $offset . ", " . $limit;
        $result = Book::get($where, $order_by, $limit_sql);
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

    /* Get all books ordered by a specific field */
    public static function get_all_with_order($order)
    {
        $where = "WHERE active = 1";
        $order_by = "ORDER BY ". $order;
        $result = Book::get($where, $order_by);
        if ($result === false){
            return false;
        } else{
            $books = array();
            foreach( $result as $row ) {
                $books[] = new Book($row);
            }
            return $books;
        }
    }

    /* Get books for multiple filters */
    public static function get_for_filter_params($data, $order=null){

        $where = "WHERE active = 1";
        foreach ($data as $key => $value) {
            $where = $where . (isset($data[$key]) ? " AND " . $key . " LIKE '%" . $data[$key] . "%'" : "");
        }
        $order_by = is_null($order) ? "ORDER BY title,author,volume" : "ORDER BY " . $order;
        $result = Book::get($where, $order_by);
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

    /* Get a single book */
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
        return new Book($result[0]);
    }

    /* Generic get books function */
    private static function get($where = null, $order_by = null, $limit = null){
        $database = Database::instance();
        $where_sql = is_null($where) ? "" : " " . $where;
        $order_by_sql = is_null($order_by) ? "" : " " . $order_by;
        $limit_sql = is_null($limit) ? "" : " " . $limit;
        $sql = "SELECT *, @curRow := @curRow + 1 AS row_number FROM " . CONFIG::DBTables()->book . " JOIN(SELECT @curRow := 0) r". $where_sql . $order_by_sql . $limit_sql;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $result;
    }

    /* Count all books */
    public static function count_books()
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num FROM " . CONFIG::DBTables()->book . " WHERE active = 1";
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

    /* Count books with different content types */
    public static function get_all_content_type_counts()
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num, content_type as type FROM " . CONFIG::DBTables()->book . " WHERE active = 1 GROUP BY content_type";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false){
            return false;
        }else{
            return array('book_content_type' => $result);
        }
    }

    /* Count books with different cover types */
    public static function get_all_cover_type_counts()
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num, cover_type as type FROM " . CONFIG::DBTables()->book . " WHERE active = 1 GROUP BY cover_type";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false){
            return false;
        }else{
            return array('book_cover_type' => $result);
        }
    }

    /* Get all authors */
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

    /* Get all titles */
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

    /* Get a book's title for its ID */
    public static function get_title_for_id($id){
        $database = Database::instance();
        $sql = "SELECT title FROM " . CONFIG::DBTables()->book . " WHERE active = 1 AND id = " . $id;
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

    /* Update a book */
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

    /* Delete a book */
    public static function set_active($id, $active)
    {
        $result = DatabaseService::set_active(Config::DBTables()->book, $id, $active);
        return $result;
    }

    /* ===================================================== *
    * Public Functions
    * ===================================================== */

    /* Set book image */
    public static function set_image($files, $title)
    {
        $file_prefix = $title;
        $dir = FileService::MAIN_DIR . '/books';
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
