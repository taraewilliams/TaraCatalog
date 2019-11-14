<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;
use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Media;

class Viewer
{
    public $id;
    public $creator_id;
    public $viewer_id;
    public $requested_by;
    public $status;
    public $c_username;

    public $row_number;
    public $created;
    public $updated;
    public $active;

    public function __construct($data, $include_relationships = true)
    {
        $this->id              = Media::set_property($data, "id", Constants::property_types()->num);
        $this->creator_id      = Media::set_property($data, "creator_id", Constants::property_types()->num);
        $this->viewer_id       = Media::set_property($data, "viewer_id", Constants::property_types()->num);

        /* Set enums */
        $this->requested_by    = Media::set_enum_property($data, 'requested_by', Constants::viewer_requested_by(), Constants::viewer_requested_by()->viewer);
        $this->status          = Media::set_enum_property($data, 'status', Constants::viewer_status(), Constants::viewer_status()->pending);

        $this->c_username      = User::get_username_for_id($this->creator_id);

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

    /* Create a viewer */
    public static function create_from_data($data)
    {
        $viewer = new Viewer($data);

        $data = array(
            "creator_id"     => $viewer->creator_id,
            "viewer_id"      => $viewer->viewer_id,
            "requested_by"   => $viewer->requested_by,
            "status"         => $viewer->status,
            "created"        => $viewer->created,
            "updated"        => $viewer->updated,
            "active"         => $viewer->active
        );

        /* Check that a viewer can't already view the creator's catalog */
        if(Viewer::exists_for_creator_and_viewer_id($data["creator_id"], $data["viewer_id"])){
            APIService::response_fail("A viewer already exists for this creator.", 500);
        }

        /* Check that the creator ID belongs to a user that is  a creator */
        if(!User::is_creator($data["creator_id"])){
            APIService::response_fail("This user is not a creator.", 500);
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
    public static function get_all($user_id, $params)
    {
        $status_where = isset($params["status"]) ? " AND viewer.status = '" . $params["status"] . "'" : "";
        $request_where = isset($params["requested_by"]) ? " AND viewer.requested_by = '" . $params["requested_by"] . "'" : "";
        $where = "WHERE viewer.active = 1 AND viewer.creator_id = " . $user_id . $status_where . $request_where;
        $inner_sql = "(SELECT user.username, user.image, viewer.id, viewer.creator_id, viewer.viewer_id, viewer.requested_by, viewer.status FROM " . CONFIG::DBTables()->viewer . " JOIN " . CONFIG::DBTables()->user ." ON user.id=viewer.creator_id " . $where . ")";
        $sql = "SELECT views.username as c_username, views.image as c_image, user.username as v_username, user.image as v_image, views.id, views.creator_id, views.viewer_id, views.status, views.requested_by FROM " . $inner_sql . " AS views JOIN " . CONFIG::DBTables()->user . " ON user.id=views.viewer_id";
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
    public static function get_all_user_views($user_id, $params)
    {
        $status_where = isset($params["status"]) ? " AND viewer.status = '" . $params["status"] . "'" : "";
        $request_where = isset($params["requested_by"]) ? " AND viewer.requested_by = '" . $params["requested_by"] . "'" : "";
        $where = "WHERE viewer.active = 1 AND viewer.viewer_id = " . $user_id. $status_where . $request_where;
        $inner_sql = "(SELECT user.username, user.image, viewer.id, viewer.creator_id, viewer.viewer_id, viewer.requested_by, viewer.status FROM " . CONFIG::DBTables()->viewer . " JOIN " . CONFIG::DBTables()->user ." ON user.id=viewer.viewer_id " . $where . ")";
        $sql = "SELECT views.username as v_username, views.image as v_image, user.username as c_username, user.image as c_image, views.id, views.creator_id, views.viewer_id, views.status, views.requested_by FROM " . $inner_sql . " AS views JOIN " . CONFIG::DBTables()->user . " ON user.id=views.creator_id";
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
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->viewer, $where);
        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("There was a problem getting the viewer.", 500);
        }
        return new Viewer($result[0]);
    }

    /* See if a viewer exists for creator and viewer IDs */
    public static function exists_for_creator_and_viewer_id($creator_id, $viewer_id, $status=null)
    {
        $where = "WHERE active = 1 AND creator_id = " . $creator_id . " AND viewer_id = " . $viewer_id;
        $where_full = ($status !== null) ? $where . " AND status = '" . $status . "'" : $where;
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->viewer, $where_full);
        return ($result !== false && $result !== null && count($result) !== 0);
    }

    /* ========================================================== *
    * UPDATE
    * ========================================================== */

    /* Update a viewer */
    public static function update($user_id, $id, $data)
    {
        /* Check if a viewer exists with this id, creator id, and was requested by the viewer */
        /* Creator must be the one approving the viewer */
        $is_creator = true;
        $where = "WHERE active = 1 AND creator_id = " . $user_id . " AND id = " . $id . " AND requested_by = 'viewer'";
        $creator_result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->viewer, $where);
        if($creator_result === false || $creator_result === null || count($creator_result) === 0) {
            $is_creator = false;
        }

        /* Check if a viewer exists with this id, viewer id, and was requested by the creator */
        /* Viewer must be the one approving the creator */
        $is_viewer = true;
        $where = "WHERE active = 1 AND viewer_id = " . $user_id . " AND id = " . $id . " AND requested_by = 'creator'";
        $viewer_result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->viewer, $where);
        if($viewer_result === false || $viewer_result === null || count($viewer_result) === 0) {
            $is_viewer = false;
        }

        /* If viewer does not exist for creator id or viewer id, then it is invalid */
        if ($is_creator===false && $is_viewer===false){
            APIService::response_fail("Invalid request.", 500);
        }

        $result = DatabaseService::update(Config::DBTables()->viewer, $id, $data);
        if ($result === false || $result === null){
            APIService::response_fail("Update failed.", 500);
        }
        return $result;
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

    /* Delete a viewer for creator ID */
    public static function delete_for_creator($creator_id)
    {
        $where = array("creator_id" => $creator_id);
        $result = DatabaseService::delete(Config::DBTables()->viewer, $where);
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

}
