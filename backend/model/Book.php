<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;
use TaraCatalog\Config\HttpFailCodes;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Media;

class Book
{
    public $id;
    public $user_id;
    public $title;
    public $series;
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
        $this->series          = Media::set_property($data, "series");
        $this->author          = Media::set_property($data, "author");
        $this->volume          = Media::set_property($data, "volume", Constants::property_types()->num);
        $this->isbn            = Media::set_property($data, "isbn");
        $this->notes           = Media::set_property($data, "notes");
        $this->todo_list       = Media::set_property($data, "todo_list", Constants::property_types()->bool, false);
        $this->genre           = Media::set_property($data, "genre");
        $this->image           = Media::set_property($data, "image");

        /* Set Enums */
        $this->cover_type      = Media::set_enum_property($data, 'cover_type', Constants::book_cover_type(), Constants::book_cover_type()->paperback);
        $this->content_type    = Media::set_enum_property($data, 'content_type', Constants::book_content_type(), Constants::book_content_type()->novel);
        $this->location        = Media::set_enum_property($data, 'location', Constants::media_location(), Constants::media_location()->home);
        $this->complete_series = Media::set_enum_property($data, 'complete_series', Constants::media_complete_series(), Constants::media_complete_series()->incomplete);

        $this->type            = "book";
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

    /* Create a book */
    public static function create_from_data($data)
    {
        $book = new Book($data);

        $data = array(
            "title"           => $book->title,
            "series"          => $book->series,
            "user_id"         => $book->user_id,
            "author"          => $book->author,
            "volume"          => $book->volume,
            "isbn"            => $book->isbn,
            "cover_type"      => $book->cover_type,
            "content_type"    => $book->content_type,
            "notes"           => $book->notes,
            "location"        => $book->location,
            "todo_list"       => $book->todo_list,
            "genre"           => $book->genre,
            "image"           => $book->image,
            "complete_series" => $book->complete_series,
            "created"         => $book->created,
            "updated"         => $book->updated,
            "active"          => $book->active
        );

        $id = DatabaseService::create(Config::DBTables()->book, $data);
        if($id === false || $id === null) {
            APIService::response_fail(HttpFailCodes::http_response_fail()->create_media);
        }
        $book->id = $id;
        return $book;
    }

}
