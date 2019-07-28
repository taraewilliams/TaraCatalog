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
    public $play_list;
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
        $this->play_list       = isset($data['play_list']) ? (boolean) $data['play_list'] : false;
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
            "play_list"      => $game->play_list,
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
        $where = array("id" => $id, "user_id" => $user_id);
        $result = DatabaseService::get(Config::DBTables()->game, $where);
        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("There was a problem getting the game.", 500);
        }
        return new Game($result[0]);
    }

    /* Get all games for a user */
    public static function get_all($user_id)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY title,platform,esrb_rating";
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->game, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the games.", 500);
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* Get all games on the play list or not on the play list */
    public static function get_all_on_play_list($user_id, $play)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id . " AND play_list = " . $play;
        $order_by = "ORDER BY title,platform,esrb_rating";
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->game, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the games.", 500);
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* Get a set number of games */
    public static function get_all_with_limit($user_id, $offset = 0, $limit = 50)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY title,platform,esrb_rating";
        $limit_sql = "LIMIT " . $offset . ", " . $limit;
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->game, $where, $order_by, $limit_sql);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the games.", 500);
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* Get all games ordered by a specific field */
    public static function get_all_with_order($user_id, $order)
    {
        $where = "WHERE active = 1 AND user_id = " . $user_id;
        $order_by = "ORDER BY ". $order;
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->game, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the games.", 500);
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* Get games for search parameters */
    /* AND for filter by specific column */
    /* OR for search all columns */
    public static function get_for_search($user_id, $data, $conj="AND", $order=null)
    {
        $enum_keys = array("location", "esrb_rating");
        $where = "WHERE (";
        $iter = 1;
        foreach ($data as $key => $value) {
            $conj_full = ($iter == 1) ? "" : " " . $conj . " ";
            $equality = in_array($key, $enum_keys) ? " = '" . $data[$key] . "'" : " LIKE '%" . $data[$key] . "%'";
            $where = $where . (isset($data[$key]) ? $conj_full . $key . $equality : "");
            $iter += 1;
        }

        $where = $where . ") AND active = 1 AND user_id = " . $user_id;
        $order_by = is_null($order) ? "ORDER BY title,platform,esrb_rating" : "ORDER BY " . $order;
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->game, $where, $order_by);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the games.", 500);
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* ========================================================== *
    * GET GAME COUNTS
    * ========================================================== */

    /* Count all games */
    public static function count_games($user_id)
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num FROM " . CONFIG::DBTables()->game . " WHERE active = 1 AND user_id = " . $user_id;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem counting the games.", 500);
        }else{
            return $result;
        }
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
        $database = Database::instance();
        $sql = "SELECT DISTINCT platform FROM " . CONFIG::DBTables()->game . " WHERE active = 1 AND user_id = " . $user_id . " ORDER BY platform";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the platforms.", 500);
        }else{
            return $result;
        }
    }

    /* ========================================================== *
    * GET A SINGLE VALUE FOR A COLUMN
    * ========================================================== */

    /* Get a game's title for its ID */
    public static function get_title_for_id($user_id, $id){
        $database = Database::instance();
        $sql = "SELECT title FROM " . CONFIG::DBTables()->game . " WHERE active = 1 AND user_id = " . $user_id . " AND id = " . $id;
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

    /* Set game image */
    public static function set_image($files, $title)
    {
        $file_prefix = $title;
        $dir = FileService::MAIN_DIR . '/games';
        $file_name = FileService::upload_file($files['image'], $dir, $file_prefix);
        if(!$file_name) {
            APIService::response_fail("There was an error saving the picture.");
        }
        return $file_name;
    }

}
