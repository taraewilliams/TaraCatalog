<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Media;

class Game
{
    public $id;
    public $user_id;
    public $title;
    public $platform;
    public $location;
    public $todo_list;
    public $image;
    public $esrb_rating;
    public $notes;
    public $genre;
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
        $this->platform        = Media::set_property($data, "platform");
        $this->todo_list       = Media::set_property($data, "todo_list", Constants::property_types()->bool, false);
        $this->image           = Media::set_property($data, "image");
        $this->notes           = Media::set_property($data, "notes");
        $this->genre           = Media::set_property($data, "genre");

        /* Set Enums */
        $this->esrb_rating     = Media::set_enum_property($data, 'esrb_rating', Constants::game_esrb_rating());
        $this->location        = Media::set_enum_property($data, 'location', Constants::media_location(), Constants::media_location()->home);
        $this->complete_series = Media::set_enum_property($data, 'complete_series', Constants::media_complete_series(), Constants::media_complete_series()->incomplete);

        $this->type            = "game";
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

    /* Create a game */
    public static function create_from_data($data)
    {
        $game = new Game($data);

        $data = array(
            "user_id"         => $game->user_id,
            "title"           => $game->title,
            "platform"        => $game->platform,
            "location"        => $game->location,
            "todo_list"       => $game->todo_list,
            "esrb_rating"     => $game->esrb_rating,
            "notes"           => $game->notes,
            "genre"           => $game->genre,
            "image"           => $game->image,
            "complete_series" => $game->complete_series,
            "created"         => $game->created,
            "updated"         => $game->updated,
            "active"          => $game->active
        );

        $id = DatabaseService::create(Config::DBTables()->game, $data);
        if($id === false || $id === null) {
            APIService::response_fail("There was a problem creating the game.", 500);
        }
        $game->id = $id;
        return $game;
    }

}
