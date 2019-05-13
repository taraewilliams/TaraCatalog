<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;
use TaraCatalog\Service\APIService;

class Viewer
{
    public $id;
    public $creator_id;
    public $viewer_id;
    public $row_number;

    public $created;
    public $updated;
    public $active;

    /* Relationships */
    public $viewer;
    public $creator;

    public function __construct($data, $include_relationships = true)
    {
        $this->id              = isset($data['id']) ? intval($data['id']) : null;
        $this->creator_id      = isset($data['creator_id']) ? intval($data['creator_id']) : null;
        $this->viewer_id       = isset($data['viewer_id']) ? intval($data['viewer_id']) : null;
        $this->row_number      = isset($data['row_number']) ? intval($data['row_number']) : null;

        $this->created          = isset($data['created']) ? new \DateTime($data['created']) : new \DateTime('now');
        $this->updated          = isset($data['updated']) ? new \DateTime($data['updated']) : new \DateTime('now');
        $this->active           = isset($data['active']) ? (boolean) $data['active'] : true;

        /* Relationship */
        if($include_relationships) {
            $this->viewer = self::get_user_for_id($this->viewer_id);
            $this->creator = self::get_user_for_id($this->creator_id);
        } else {
            unset($this->viewer);
            unset($this->creator);
        }
    }

    /* =====================================================
    * Database Functions
    * ===================================================== */

    /* ========================================================== *
    * POST
    * ========================================================== */

    /* Create a viewer */
    public static function create_from_data($data)
    {
        $viewer = new Viewer($data);

        $data = array(
            "creator_id"     => $viewer->creator_id,
            "viewer_id"      => $viewer->viewer_id,
            "created"        => $viewer->created,
            "updated"        => $viewer->updated,
            "active"         => $viewer->active
        );

        $view = Viewer::get_for_creator_and_viewer_id($data["creator_id"], $data["viewer_id"]);
        if ($view !== false && $view !== null){
            APIService::response_fail("A viewer already exists for this creator.");
        }

        $id = DatabaseService::create(Config::DBTables()->viewer, $data);
        if($id === false) {
            return false;
        }
        if($id === null) {
            return null;
        }
        $viewer->id = $id;
        return $viewer;
    }

    /* ========================================================== *
    * GET
    * ========================================================== */

    /* Get all viewers */
    public static function get_all($user_id)
    {
        $where = "WHERE active = 1 AND creator_id = " . $user_id;
        $result = Viewer::get($where);
        if ($result === false){
            return false;
        }else{
            $viewers = array();
            foreach( $result as $row ) {
                $viewers[] = new Viewer($row);
            }
            return $viewers;
        }
    }

    /* Get all creator can view */
    public static function get_all_user_views($user_id)
    {
        $where = "WHERE active = 1 AND viewer_id = " . $user_id;
        $result = Viewer::get($where);
        if ($result === false){
            return false;
        }else{
            $viewers = array();
            foreach( $result as $row ) {
                $viewers[] = new Viewer($row);
            }
            return $viewers;
        }
    }

    /* Get single viewer for creator and viewer IDs */
    public static function get_for_creator_and_viewer_id($creator_id, $viewer_id)
    {
        $where = "WHERE active = 1 AND creator_id = " . $creator_id . " AND viewer_id = " . $viewer_id;
        $result = Viewer::get($where);
        if($result === false || $result === null) {
            return false;
        }
        if(count($result) === 0) {
            return null;
        }
        return new Viewer($result[0]);
    }

    /* ========================================================== *
    * DELETE
    * ========================================================== */

    /* Delete a viewer */
    public static function set_active($creator_id, $viewer_id, $active)
    {
        $id = Viewer::get_for_creator_and_viewer_id($creator_id, $viewer_id)->id;
        if ($id == null || $id == false){
            APIService::response_fail("There was a problem deleting the viewer.", 500);
        }
        $result = DatabaseService::set_active(Config::DBTables()->viewer, $id, $active);
        return $result;
    }

    /* ===================================================== *
    * Public Functions
    * ===================================================== */

    public static function sort_viewers($a, $b){
        return $a->viewer["username"] > $b->viewer["username"];
    }

    public static function sort_creators($a, $b){
        return $a->creator["username"] > $b->creator["username"];
    }

    /* ===================================================== *
    * Private Functions
    * ===================================================== */

    /* Generic get games function */
    private static function get($where = null, $order_by = null, $limit = null){
        $database = Database::instance();
        $where_sql = is_null($where) ? "" : " " . $where;
        $order_by_sql = is_null($order_by) ? "" : " " . $order_by;
        $limit_sql = is_null($limit) ? "" : " " . $limit;
        $sql = "SELECT *, @curRow := @curRow + 1 AS row_number FROM " . CONFIG::DBTables()->viewer . " JOIN(SELECT @curRow := 0) r". $where_sql . $order_by_sql . $limit_sql;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $result;
    }

    /* Get a user for an ID */
    private static function get_user_for_id($user_id)
    {
        $where = array("id" => $user_id);
        $result = DatabaseService::get(Config::DBTables()->user, $where);

        if($result === false || $result === null) {
            return false;
        }
        if(count($result) === 0) {
            return null;
        }
        $user = new User($result[0]);
        return array("id" => $user->id, "username" => $user->username, "image" => $user->image);
    }

}
