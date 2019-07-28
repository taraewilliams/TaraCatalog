<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;
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
        $this->location        = isset($data['location']) ? $data['location'] : "Home";
        $this->todo_list       = isset($data['todo_list']) ? (boolean) $data['todo_list'] : false;
        $this->image           = isset($data['image']) ? $data['image'] : null;
        $this->esrb_rating     = isset($data['esrb_rating']) ? $data['esrb_rating'] : null;
        $this->notes           = isset($data['notes']) ? $data['notes'] : null;

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
            "image"          => $game->image,
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

    /* ========================================================== *
    * GET
    * ========================================================== */


    /* ========================================================== *
    * GET GAMES
    * ========================================================== */

    /* Get a single game */
    public static function get_from_id($user_id, $id)
    {
        $result = Media::get_from_id($user_id, $id, Config::DBTables()->game);
        return new Game($result);
    }

    /* Get all games for a user */
    public static function get_all($user_id)
    {
        $order_by = "ORDER BY title,platform,esrb_rating";
        return Media::get_all($user_id, CONFIG::DBTables()->game, $order_by);
    }

    /* Get all games on the todo list or not on the todo list */
    public static function get_all_on_todo_list($user_id, $todo)
    {
        $order_by = "ORDER BY title,platform,esrb_rating";
        return Media::get_all_on_todo_list($user_id, $todo, CONFIG::DBTables()->game, $order_by);
    }

    /* Get a set number of games */
    public static function get_all_with_limit($user_id, $offset = 0, $limit = 50)
    {
        $order_by = "ORDER BY title,platform,esrb_rating";
        return Media::get_all_with_limit($user_id, CONFIG::DBTables()->game, $order_by, $offset, $limit);
    }

    /* Get all games ordered by a specific field */
    public static function get_all_with_order($user_id, $order)
    {
        return Media::get_all_with_order($user_id, CONFIG::DBTables()->game, $order);
    }

    /* Get games for search parameters */
    /* AND for filter by specific column */
    /* OR for search all columns */
    public static function get_for_search($user_id, $data, $conj="AND", $order=null)
    {
        $order_by = is_null($order) ? "ORDER BY title,platform,esrb_rating" : "ORDER BY " . $order;
        $enum_keys = array("location", "esrb_rating");
        return Media::get_for_search($user_id, CONFIG::DBTables()->game, $data, $order_by, $enum_keys, $conj);
    }

    /* ========================================================== *
    * GET GAME COUNTS
    * ========================================================== */

    /* Count all games */
    public static function count_games($user_id)
    {
        return Media::count_media($user_id, CONFIG::DBTables()->game);
    }

    /* Count all games, grouped by platform */
    public static function get_all_platform_counts($user_id)
    {
        $column_name = "platform";
        $header = "game_platform_type";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->game, $user_id, $column_name, $header);
    }

    /* Count all games, grouped by esrb rating */
    public static function get_all_esrb_rating_counts($user_id)
    {
        $column_name = "esrb_rating";
        $header = "game_esrb_rating_type";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->game, $user_id, $column_name, $header);
    }

    /* Count games with different locations */
    public static function get_all_location_counts($user_id)
    {
        $column_name = "location";
        $header = "game_location";
        return DatabaseService::get_counts_for_column(CONFIG::DBTables()->game, $user_id, $column_name, $header);
    }

    /* ========================================================== *
    * GET ALL DISTINCT VALUES FOR A COLUMN
    * ========================================================== */

    /* Get all game platforms */
    public static function get_platforms($user_id)
    {
        $column_name = "platform";
        return Media::get_distinct_for_column($user_id, CONFIG::DBTables()->game, $column_name);
    }

    /* ========================================================== *
    * GET A SINGLE VALUE FOR A COLUMN
    * ========================================================== */

    /* Get a game's title for its ID */
    public static function get_title_for_id($user_id, $id){
        $column_name = "title";
        return Media::get_column_value_for_id($user_id, $id, CONFIG::DBTables()->game, $column_name);
    }

    /* ========================================================== *
    * UPDATE
    * ========================================================== */

    /* Update a game */
    public static function update($user_id, $id, $data)
    {
        $result = DatabaseService::update(Config::DBTables()->game, $id, $data);
        if ($result === false || $result === null){
            APIService::response_fail("Update failed.", 500);
        }
        return $result ? self::get_from_id($user_id, $id) : false;
    }

    /* ========================================================== *
    * DELETE
    * ========================================================== */

    /* Delete a game */
    public static function set_active($id, $active)
    {
        $result = DatabaseService::set_active(Config::DBTables()->game, $id, $active);
        if( $result === false || $result === null) {
            APIService::response_fail("There was an error deleting the game.", 500);
        }
        return $result;
    }

    /* ===================================================== *
    * Public Functions
    * ===================================================== */

}
