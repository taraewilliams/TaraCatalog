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
    public $todo_list;
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
        $this->todo_list       = isset($data['todo_list']) ? (boolean) $data['todo_list'] : false;
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
            "todo_list"      => $book->todo_list,
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
        $result = Media::get_from_id($user_id, $id, Config::DBTables()->book);
        return new Book($result);
    }

    /* Get all books for a user */
    public static function get_all($user_id)
    {
        $order_by = "ORDER BY title,author,volume";
        return Media::get_all($user_id, CONFIG::DBTables()->book, $order_by);
    }

    /* Get all books on the todo list or not on the todo list */
    public static function get_all_on_todo_list($user_id, $todo)
    {
        $order_by = "ORDER BY title,author,volume";
        return Media::get_all_on_todo_list($user_id, $todo, CONFIG::DBTables()->book, $order_by);
    }

    /* Get a set number of books */
    public static function get_all_with_limit($user_id, $offset = 0, $limit = 50)
    {
        $order_by = "ORDER BY title,author,volume";
        return Media::get_all_with_limit($user_id, CONFIG::DBTables()->book, $order_by, $offset, $limit);
    }

    /* Get all books ordered by a specific field */
    public static function get_all_with_order($user_id, $order)
    {
        return Media::get_all_with_order($user_id, CONFIG::DBTables()->book, $order);
    }

    /* Get books for search parameters */
    /* AND for filter by specific column */
    /* OR for search all columns */
    public static function get_for_search($user_id, $data, $conj="AND", $order=null)
    {
        $order_by = is_null($order) ? "ORDER BY title,author,volume" : "ORDER BY " . $order;
        $enum_keys = array("cover_type", "content_type", "location");
        return Media::get_for_search($user_id, CONFIG::DBTables()->book, $data, $order_by, $enum_keys, $conj);
    }

    /* ========================================================== *
    * GET BOOK COUNTS
    * ========================================================== */

    /* Count all books */
    public static function count_books($user_id)
    {
        return Media::count_media($user_id, CONFIG::DBTables()->book);
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
        $column_name = "author";
        return Media::get_distinct_for_column($user_id, CONFIG::DBTables()->book, $column_name);
    }

    /* Get all titles */
    public static function get_titles($user_id)
    {
        $column_name = "title";
        return Media::get_distinct_for_column($user_id, CONFIG::DBTables()->book, $column_name);
    }

    /* ========================================================== *
    * GET A SINGLE VALUE FOR A COLUMN
    * ========================================================== */

    /* Get a book's title for its ID */
    public static function get_title_for_id($user_id, $id){
        $column_name = "title";
        return Media::get_column_value_for_id($user_id, $id, CONFIG::DBTables()->book, $column_name);
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

}
