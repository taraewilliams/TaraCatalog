<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Service\DatabaseService;
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
    public $genre;
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
        $this->genre           = isset($data['genre']) ? $data['genre'] : null;
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
            "genre"          => $book->genre,
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

}
