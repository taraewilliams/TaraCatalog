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
    public $status;
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
        $this->status          = isset($data['status']) ? $data['status'] : "Pending";
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
            "status"         => $viewer->status,
            "created"        => $viewer->created,
            "updated"        => $viewer->updated,
            "active"         => $viewer->active
        );

        /* Check that a viewer can't already view the creator's catalog */
        $view = Viewer::get_for_creator_and_viewer_id($data["creator_id"], $data["viewer_id"]);
        if ($view !== false && $view !== null && count($view) !== 0){
            APIService::response_fail("A viewer already exists for this creator.");
        }

        $id = DatabaseService::create(Config::DBTables()->viewer, $data);
        if($id === false || $id === null) {
            APIService::response_fail("There was a problem creating the viewer.", 500);
        }
        $viewer->id = $id;
        return $viewer;
    }

    /* ========================================================== *
    * GET
    * ========================================================== */

    /* Get all viewers for a creator */
    public static function get_all($user_id, $status)
    {
        $where = "WHERE viewer.active = 1 AND viewer.creator_id = " . $user_id . " AND viewer.status = '" . $status . "'";
        $inner_sql = "(SELECT user.username, user.image, viewer.id, viewer.creator_id, viewer.viewer_id, viewer.status FROM " . CONFIG::DBTables()->viewer . " JOIN " . CONFIG::DBTables()->user ." ON user.id=viewer.creator_id " . $where . ")";
        $sql = "SELECT views.username as c_username, views.image as c_image, user.username as v_username, user.image as v_image, views.id, views.creator_id, views.viewer_id, views.status FROM " . $inner_sql . " AS views JOIN " . CONFIG::DBTables()->user . " ON user.id=views.viewer_id";
        $database = Database::instance();
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false || $result === null){
            APIService::response_fail("There was a problem getting the viewers.", 500);
        }else{
            return $result;
        }
    }

    /* Get all creator can view */
    public static function get_all_user_views($user_id, $status)
    {
        $where = "WHERE viewer.active = 1 AND viewer.viewer_id = " . $user_id. " AND viewer.status = '" . $status . "'";
        $inner_sql = "(SELECT user.username, user.image, viewer.id, viewer.creator_id, viewer.viewer_id, viewer.status FROM " . CONFIG::DBTables()->viewer . " JOIN " . CONFIG::DBTables()->user ." ON user.id=viewer.viewer_id " . $where . ")";
        $sql = "SELECT views.username as v_username, views.image as v_image, user.username as c_username, user.image as c_image, views.id, views.creator_id, views.viewer_id, views.status FROM " . $inner_sql . " AS views JOIN " . CONFIG::DBTables()->user . " ON user.id=views.creator_id";
        $database = Database::instance();
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if ($result === false || $result === null){
            APIService::response_fail("There was a problem getting the viewers.", 500);
        }else{
            return $result;
        }
    }

    /* Get single viewer for creator and viewer IDs */
    public static function get_for_creator_and_viewer_id($creator_id, $viewer_id, $status=null)
    {
        if ($status !== null){
            $where = "WHERE active = 1 AND creator_id = " . $creator_id . " AND viewer_id = " . $viewer_id . " AND status = '" . $status . "'";
        }else{
            $where = "WHERE active = 1 AND creator_id = " . $creator_id . " AND viewer_id = " . $viewer_id;
        }
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
    * UPDATE
    * ========================================================== */

    /* Update a viewer */
    public static function update($user_id, $id, $data)
    {
        /* Check if a viewer exists with this id and creator id */
        /* Creator must be the one approving the viewer */
        $where = "WHERE active = 1 AND creator_id = " . $user_id . " AND id = " . $id;
        $result = Viewer::get($where);
        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("Invalid viewer.", 500);
        }
        if($result[0]["status"] === $data["status"]){
            APIService::response_fail("Already " . $data["status"], 500);
        }

        $result = DatabaseService::update(Config::DBTables()->viewer, $id, $data);

        if ($result === false || $result === null){
            APIService::response_fail("Update failed.", 500);
        }
        return $result ? true : false;
    }

    /* ========================================================== *
    * DELETE
    * ========================================================== */

    /* Delete a viewer */
    public static function delete($creator_id, $viewer_id)
    {
        $id = Viewer::get_for_creator_and_viewer_id($creator_id, $viewer_id)->id;
        if ($id === null || $id === false){
            APIService::response_fail("There was a problem deleting the viewer.", 500);
        }
        $where = array("creator_id" => $creator_id, "viewer_id" => $viewer_id);
        $result = DatabaseService::delete(Config::DBTables()->viewer, $where);
        if( $result === false || $result === null) {
            APIService::response_fail("There was an error deleting that viewer.", 500);
        }
        return $result;
    }

    /* ===================================================== *
    * Public Functions
    * ===================================================== */

    public static function sort_viewers($a, $b){
        return strtolower($a["v_username"]) > strtolower($b["v_username"]);
    }

    public static function sort_creators($a, $b){
        return strtolower($a["c_username"]) > strtolower($b["c_username"]);
    }

    /* ===================================================== *
    * Private Functions
    * ===================================================== */

    /* Generic get viewer function */
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

        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("There was a problem getting the user.", 500);
        }
        $user = new User($result[0]);
        return array("id" => $user->id, "username" => $user->username, "image" => $user->image);
    }

}
