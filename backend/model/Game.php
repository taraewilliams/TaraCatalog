<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\APIService;

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
        $this->id              = isset($data['id']) ? intval($data['id']) : null;
        $this->user_id         = isset($data['user_id']) ? intval($data['user_id']) : null;
        $this->title           = isset($data['title']) ? $data['title'] : null;
        $this->platform        = isset($data['platform']) ? $data['platform'] : null;
        $this->todo_list       = isset($data['todo_list']) ? (boolean) $data['todo_list'] : false;
        $this->image           = isset($data['image']) ? $data['image'] : null;
        $this->notes           = isset($data['notes']) ? $data['notes'] : null;
        $this->genre           = isset($data['genre']) ? $data['genre'] : null;

        /* Set Enums */
        $this->esrb_rating     = (isset($data['esrb_rating'])
            && Media::is_valid_enum(Constants::game_esrb_rating(), $data["esrb_rating"]))
            ? $data['esrb_rating']
            : null;
        $this->location        = (isset($data['location'])
            && Media::is_valid_enum(Constants::media_location(), $data["location"]))
            ? $data['location']
            : Constants::media_location()->home;
        $this->complete_series = (isset($data['complete_series'])
            && Media::is_valid_enum(Constants::media_complete_series(), $data["complete_series"]))
            ? $data['complete_series']
            : Constants::media_complete_series()->incomplete;

        $this->type            = "game";
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

    /* Create a game */
    public static function create_from_data($data)
    {
        $game = new Game($data);

        $data = array(
            "user_id"        => $game->user_id,
            "title"          => $game->title,
            "platform"       => $game->platform,
            "location"       => $game->location,
            "todo_list"      => $game->todo_list,
            "esrb_rating"    => $game->esrb_rating,
            "notes"          => $game->notes,
            "genre"          => $game->genre,
            "image"          => $game->image,
            "complete_series" => $game->complete_series,
            "created"        => $game->created,
            "updated"        => $game->updated,
            "active"         => $game->active
        );

        $id = DatabaseService::create(Config::DBTables()->game, $data);
        if($id === false || $id === null) {
            APIService::response_fail("There was a problem creating the game.", 500);
        }
        $game->id = $id;
        return $game;
    }

}
