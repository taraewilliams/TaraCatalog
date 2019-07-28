<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;
use TaraCatalog\Service\APIService;

class Book
{
    public $id;
    public $user_id;
    public $title;
    public $author;
    public $volume;
    public $isbn;
    public $cover_type;
    public $content_type;
    public $notes;
    public $location;
    public $read_list;
    public $image;

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
        $this->author          = isset($data['author']) ? $data['author'] : null;
        $this->volume          = isset($data['volume']) ? intval($data['volume']) : null;
        $this->isbn            = isset($data['isbn']) ? $data['isbn'] : null;
        $this->cover_type      = isset($data['cover_type']) ? $data['cover_type'] : "Paperback";
        $this->content_type    = isset($data['content_type']) ? $data['content_type'] : "Novel";
        $this->notes           = isset($data['notes']) ? $data['notes'] : null;
        $this->location        = isset($data['location']) ? $data['location'] : "Home";
        $this->read_list       = isset($data['read_list']) ? (boolean) $data['read_list'] : false;
        $this->image           = isset($data['image']) ? $data['image'] : null;

        $this->type            = "book";
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

    /* Create a book */
    public static function create_from_data($data)
    {
        $book = new Book($data);

        $data = array(
            "title"          => $book->title,
            "user_id"        => $book->user_id,
            "author"         => $book->author,
            "volume"         => $book->volume,
            "isbn"           => $book->isbn,
            "cover_type"     => $book->cover_type,
            "content_type"   => $book->content_type,
            "notes"          => $book->notes,
            "location"       => $book->location,
            "read_list"      => $book->read_list,
            "image"          => $book->image,
            "created"        => $book->created,
            "updated"        => $book->updated,
            "active"         => $book->active
        );

        $id = DatabaseService::create(Config::DBTables()->book, $data);
        if($id === false || $id === null) {
            APIService::response_fail("There was a problem creating the book.", 500);
        }
        $book->id = $id;
        return $book;
    }

    /* ========================================================== *
    * GET
    * ========================================================== */


    /* ========================================================== *
    * GET BOOKS
    * ========================================================== */

    /* Get a single book */
    public static function get_from_id($user_id, $id)
    {
        $where = array("id" => $id, "user_id" => $user_id);
        $result = DatabaseService::get(Config::DBTables()->book, $where);
        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("There was a problem getting the book.", 500);
        }
        return new Book($result[0]);
    }

    /* Get all books for a user */
    public static function get_all($user_id)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY title,author,volume";
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->book, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the books.", 500);
        }else{
            $books = array();
            foreach( $result as $row ) {
                $books[] = new Book($row);
            }
            return $books;
        }
    }

    /* Get all books on the read list or not on the read list */
    public static function get_all_on_read_list($user_id, $read)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id . " AND read_list = " . $read;
        $order_by = "ORDER BY title,author,volume";
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->book, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the books.", 500);
        }else{
            $books = array();
            foreach( $result as $row ) {
                $books[] = new Book($row);
            }
            return $books;
        }
    }

    /* Get a set number of books */
    public static function get_all_with_limit($user_id, $offset = 0, $limit = 50)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY title,author,volume";
        $limit_sql = "LIMIT " . $offset . ", " . $limit;
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->book, $where, $order_by, $limit_sql);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the books.", 500);
        }else{
            $books = array();
            foreach( $result as $row ) {
                $books[] = new Book($row);
            }
            return $books;
        }
    }

    /* Get all books ordered by a specific field */
    public static function get_all_with_order($user_id, $order)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY ". $order;
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->book, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the books.", 500);
        }else{
            $books = array();
            foreach( $result as $row ) {
                $books[] = new Book($row);
            }
            return $books;
        }
    }

    /* Get books for search parameters */
    /* AND for filter by specific column */
    /* OR for search all columns */
    public static function get_for_search($user_id, $data, $conj="AND", $order=null)
    {
        $enum_keys = array("cover_type", "content_type", "location");
        $where = "WHERE (";
        $iter = 1;
        foreach ($data as $key => $value) {
            $conj_full = ($iter == 1) ? "" : " " . $conj . " ";
            $equality = in_array($key, $enum_keys) ? " = '" . $data[$key] . "'" : " LIKE '%" . $data[$key] . "%'";
            $where = $where . (isset($data[$key]) ? $conj_full . $key . $equality : "");
            $iter += 1;
        }
        $where = $where . ") AND active = 1 AND user_id = " . $user_id;
        $order_by = is_null($order) ? "ORDER BY title,author,volume" : "ORDER BY " . $order;
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->book, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the books.", 500);
        }else{
            $books = array();
            foreach( $result as $row ) {
                $books[] = new Book($row);
            }
            return $books;
        }
    }

    /* ========================================================== *
    * GET BOOK COUNTS
    * ========================================================== */

    /* Count all books */
    public static function count_books($user_id)
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num FROM " . CONFIG::DBTables()->book . " WHERE active = 1 AND user_id = " . $user_id;;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem counting the books.", 500);
        }else{
            return $result;
        }
    }

    /* Count books with different content types */
    public static function get_all_content_type_counts($user_id)
    {
        $column_name = "content_type";
        $header = "book_content_type";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->book, $user_id, $column_name, $header);
    }

    /* Count books with different cover types */
    public static function get_all_cover_type_counts($user_id)
    {
        $column_name = "cover_type";
        $header = "book_cover_type";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->book, $user_id, $column_name, $header);
    }

    /* Count books with different locations */
    public static function get_all_location_counts($user_id)
    {
        $column_name = "location";
        $header = "book_location";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->book, $user_id, $column_name, $header);
    }

    /* ========================================================== *
    * GET ALL DISTINCT VALUES FOR A COLUMN
    * ========================================================== */

    /* Get all authors */
    public static function get_authors($user_id)
    {
        $database = Database::instance();
        $sql = "SELECT DISTINCT author FROM " . CONFIG::DBTables()->book . " WHERE active = 1 AND user_id = " . $user_id . " ORDER BY author";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the authors.", 500);
        }else{
            return $result;
        }
    }

    /* Get all titles */
    public static function get_titles($user_id)
    {
        $database = Database::instance();
        $sql = "SELECT DISTINCT title FROM " . CONFIG::DBTables()->book . " WHERE active = 1 AND user_id = " . $user_id . " ORDER BY title";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the titles.", 500);
        }else{
            return $result;
        }
    }

    /* ========================================================== *
    * GET A SINGLE VALUE FOR A COLUMN
    * ========================================================== */

    /* Get a book's title for its ID */
    public static function get_title_for_id($user_id, $id){
        $database = Database::instance();
        $sql = "SELECT title FROM " . CONFIG::DBTables()->book . " WHERE active = 1 AND id = " . $id . " AND user_id = " . $user_id;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the title.", 500);
        }else{
            return $result["title"];
        }
    }

    /* ========================================================== *
    * UPDATE
    * ========================================================== */

    /* Update a book */
    public static function update($user_id, $id, $data)
    {
        $result = DatabaseService::update(Config::DBTables()->book, $id, $data);
        if ($result === false || $result === null){
            APIService::response_fail("Update failed.", 500);
        }
        return $result ? self::get_from_id($user_id, $id) : false;
    }

    /* ========================================================== *
    * DELETE
    * ========================================================== */

    /* Delete a book */
    public static function set_active($id, $active)
    {
        $result = DatabaseService::set_active(Config::DBTables()->book, $id, $active);
        if( $result === false || $result === null) {
            APIService::response_fail("There was an error deleting the book.", 500);
        }
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
            APIService::response_fail("There was an error saving the picture.");
        }
        return $file_name;
    }

    /* Sort all media by title */
    public static function sort_all($a, $b){
        return strtolower($a->title) > strtolower($b->title);
    }

    public static function get_all_media_location_counts($user_id){
        $column_name = "location";
        $header = "media_locations";

        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num, " . $column_name . " as type, 'book' as media FROM " . CONFIG::DBTables()->book . " WHERE active = 1 AND " . $column_name . " IS NOT NULL AND user_id = " . $user_id . " GROUP BY " . $column_name;
        $sql .= " UNION SELECT COUNT(*) as num, " . $column_name . " as type, 'movie' as media FROM " . CONFIG::DBTables()->movie . " WHERE active = 1 AND " . $column_name . " IS NOT NULL AND user_id = " . $user_id . " GROUP BY " . $column_name;
        $sql .= " UNION SELECT COUNT(*) as num, " . $column_name . " as type, 'game' as media FROM " . CONFIG::DBTables()->game . " WHERE active = 1 AND " . $column_name . " IS NOT NULL AND user_id = " . $user_id . " GROUP BY " . $column_name;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false || $result === null){
            APIService::response_fail("There was a problem getting the counts.", 500);
        }else{
            return array($header => $result);
        }
    }

}
