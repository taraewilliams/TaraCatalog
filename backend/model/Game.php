<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;

class Game
{
    public $id;
    public $title;
    public $platform;
    public $location;
    public $play_list;
    public $image;
    public $row_number;

    public $created;
    public $updated;
    public $active;

    public function __construct($data)
    {
        $this->id              = isset($data['id']) ? intval($data['id']) : null;
        $this->title           = isset($data['title']) ? $data['title'] : null;
        $this->platform        = isset($data['platform']) ? $data['platform'] : null;
        $this->location        = isset($data['location']) ? $data['location'] : "Home";
        $this->image           = isset($data['image']) ? $data['image'] : null;
        $this->play_list       = isset($data['play_list']) ? (boolean) $data['play_list'] : false;
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

    /* Create a game */
    public static function create_from_data($data)
    {
        $game = new Game($data);

        $data = array(
            "title"          => $game->title,
            "platform"       => $game->platform,
            "location"       => $game->location,
            "play_list"      => $game->play_list,
            "image"          => $game->image,
            "created"        => $game->created,
            "updated"        => $game->updated,
            "active"         => $game->active
        );

        $id = DatabaseService::create(Config::DBTables()->game, $data);
        if($id === false) {
            return false;
        }
        if($id === null) {
            return null;
        }
        $game->id = $id;
        return $game;
    }

    /* ========================================================== *
    * GET
    * ========================================================== */

    /* Get all games */
    public static function get_all()
    {
        $where = "WHERE active = 1";
        $order_by = "ORDER BY title,platform";
        $result = Game::get($where, $order_by);
        if ($result === false){
            return false;
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* Get all games on the play list */
    public static function get_all_on_play_list($play)
    {
        $where = "WHERE active = 1 AND play_list = " . $play;
        $order_by = "ORDER BY title,platform";
        $result = Game::get($where, $order_by);
        if ($result === false){
            return false;
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* Get a set number of games */
    public static function get_all_with_limit($offset = 0, $limit = 50)
    {
        $where = "WHERE active = 1";
        $order_by = "ORDER BY title,platform";
        $limit_sql = "LIMIT " . $offset . ", " . $limit;
        $result = Game::get($where, $order_by, $limit_sql);
        if ($result === false){
            return false;
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* Get all games ordered by a specific field */
    public static function get_all_with_order($order)
    {
        $where = "WHERE active = 1";
        $order_by = "ORDER BY ". $order;
        $result = Game::get($where, $order_by);
        if ($result === false){
            return false;
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* Get games for multiple filters */
    public static function get_for_filter_params($data, $order=null){

        $where = "WHERE active = 1";
        foreach ($data as $key => $value) {
            $where = $where . (isset($data[$key]) ? " AND " . $key . " LIKE '%" . $data[$key] . "%'" : "");
        }
        $order_by = is_null($order) ? "ORDER BY title,platform" : "ORDER BY " . $order;
        $result = Game::get($where, $order_by);
        if ($result === false){
            return false;
        }else{
            $games = array();
            foreach( $result as $row ) {
                $games[] = new Game($row);
            }
            return $games;
        }
    }

    /* Get a single game */
    public static function get_from_id($id)
    {
        $where = array("id" => $id);
        $result = DatabaseService::get(Config::DBTables()->game, $where);

        if($result === false || $result === null) {
            return false;
        }
        if(count($result) === 0) {
            return null;
        }
        return new Game($result[0]);
    }

    /* Generic get games function */
    private static function get($where = null, $order_by = null, $limit = null){
        $database = Database::instance();
        $where_sql = is_null($where) ? "" : " " . $where;
        $order_by_sql = is_null($order_by) ? "" : " " . $order_by;
        $limit_sql = is_null($limit) ? "" : " " . $limit;
        $sql = "SELECT *, @curRow := @curRow + 1 AS row_number FROM " . CONFIG::DBTables()->game . " JOIN(SELECT @curRow := 0) r". $where_sql . $order_by_sql . $limit_sql;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $result;
    }

    /* Count all games */
    public static function count_games()
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num FROM " . CONFIG::DBTables()->game . " WHERE active = 1";
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

    /* Count all games, grouped by platform */
    public static function get_all_platform_counts()
    {
        $database = Database::instance();
        $sql = "SELECT COUNT(*) as num, platform as type FROM " . CONFIG::DBTables()->game . " WHERE active = 1 GROUP BY platform";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false){
            return false;
        }else{
            return array('game_platform_type' => $result);
        }
    }

    /* Get all game platforms */
    public static function get_platforms()
    {
        $database = Database::instance();
        $sql = "SELECT DISTINCT platform FROM " . CONFIG::DBTables()->game . " WHERE active = 1 ORDER BY platform";
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

    /* Get a game's title for its ID */
    public static function get_title_for_id($id){
        $database = Database::instance();
        $sql = "SELECT title FROM " . CONFIG::DBTables()->game . " WHERE active = 1 AND id = " . $id;
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

    /* Update a game */
    public static function update($id, $data)
    {
        $result = DatabaseService::update(Config::DBTables()->game, $id, $data);

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

    /* Delete a game */
    public static function set_active($id, $active)
    {
        $result = DatabaseService::set_active(Config::DBTables()->game, $id, $active);
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
            APIService::response_error("There was an error saving the picture.");
        }
        return $file_name;
    }

    /* ===================================================== *
    * Private Functions
    * ===================================================== */


}
