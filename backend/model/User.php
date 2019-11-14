<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;
use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Media;

class User
{
    public $id;
    public $username;
    private $hashed_password;
    public $email;
    public $first_name;
    public $last_name;
    public $color_scheme;
    public $image;
    public $role;
    public $is_admin;

    public $created;
    public $updated;
    public $active;

    public function __construct($data)
    {

        $this->id              = Media::set_property($data, "id", Constants::property_types()->num);
        $this->username        = Media::set_property($data, "username");
        $this->email           = Media::set_property($data, "email");
        $this->first_name      = Media::set_property($data, "first_name");
        $this->last_name       = Media::set_property($data, "last_name");
        $this->image           = Media::set_property($data, "image");
        $this->is_admin        = Media::set_property($data, "is_admin", Constants::property_types()->bool, false);
        $this->hashed_password = isset($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : (isset($data['hashed_password']) ? $data['hashed_password'] : null);

        /* Set Enums */
        $this->color_scheme    = Media::set_enum_property($data, 'color_scheme', Constants::user_color_scheme(), Constants::user_color_scheme()->red);
        $this->role            = Media::set_enum_property($data, 'role', Constants::user_role(), Constants::user_role()->viewer);

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

    /* Create a user */
    public static function create_from_data($data)
    {
        $user = new User($data);

        $data = array(
            "username"          => $user->username,
            "password"          => $user->hashed_password,
            "email"             => $user->email,
            "first_name"        => $user->first_name,
            "last_name"         => $user->last_name,
            "color_scheme"      => $user->color_scheme,
            "image"             => $user->image,
            "role"              => $user->role,
            "is_admin"          => $user->is_admin,
            "created"           => $user->created,
            "updated"           => $user->updated,
            "active"            => $user->active
        );

        $id = DatabaseService::create(Config::DBTables()->user, $data);
        if($id === false || $id === null) {
            APIService::response_fail("There was a problem creating the user.", 500);
        }
        $user->id = $id;
        return $user;
    }

    /* ========================================================== *
    * GET
    * ========================================================== */

    /* Admin only requests */
    /* Get all users */
    public static function get_all($active = 1)
    {
        $where = "WHERE active = " . $active;
        $result = DatabaseService::get_where_order_limit(Config::DBTables()->user, $where);
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the users.", 500);
        }else{
            $users = array();
            foreach( $result as $row ) {
                $users[] = new User($row);
            }
            return $users;
        }
    }

    /* User requests */
    /* Get user from username and password */
    public static function get_from_username_and_password($username, $password)
    {
        $where = "WHERE active = 1 AND username = '" . $username . "'";
        $result = DatabaseService::get_where_order_limit(CONFIG::DBTables()->user, $where);
        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("There was a problem getting the user.", 500);
        }

        $result[0]['hashed_password'] = $result[0]['password'];
        unset($result[0]['password']);
        $user = new User($result[0]);

        if( !password_verify($password, $user->hashed_password) ) {
            APIService::response_fail("Invalid username or password.", 500);
        }else{
            return $user;
        }
    }

    /* Get a single user */
    public static function get_from_id($id)
    {
        $where = array("id" => $id);
        $result = DatabaseService::get(Config::DBTables()->user, $where);
        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("There was a problem getting the user.", 500);
        }
        return new User($result[0]);
    }

    /* Get users that are not viewing a creator's catalog */
    public static function get_nonviewers($user_id){
        $database = Database::instance();
        $viewer_ids = "SELECT DISTINCT viewer_id FROM " . CONFIG::DBTables()->viewer . " WHERE creator_id = " . $user_id;
        $sql = "SELECT id, username, image FROM " . CONFIG::DBTables()->user . " WHERE active = 1 AND id != " . $user_id . " AND id NOT IN (" . $viewer_ids . ") ORDER BY username";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the users.", 500);
        }else{
            return $result;
        }
    }

    /* Get users whose catalogs a creator can't view */
    public static function get_nonviews($user_id){
        $database = Database::instance();
        $creator_ids = "SELECT DISTINCT creator_id FROM " . CONFIG::DBTables()->viewer . " WHERE viewer_id = " . $user_id;
        /* Only include users who are currently creators */
        $sql = "SELECT id, username, image FROM " . CONFIG::DBTables()->user . " WHERE active = 1 AND role = 'creator' AND id != " . $user_id . " AND id NOT IN (" . $creator_ids . ") ORDER BY username";
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the users.", 500);
        }else{
            return $result;
        }
    }

    /* Get a user's username for the ID */
    public static function get_username_for_id($id){
        $database = Database::instance();
        $sql = "SELECT username FROM " . CONFIG::DBTables()->user . " WHERE active = 1 AND id = " . $id;
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        if($result === false || $result === null) {
            APIService::response_fail("There was a problem getting the user.", 500);
        }else{
            return $result["username"];
        }
    }

    /* ========================================================== *
    * UPDATE
    * ========================================================== */

    /* Update a user */
    public static function update($id, $data)
    {
        /* If new image is set, delete the old image */
        if (isset($data["image"])){
            $database = Database::instance();
            $sql = "SELECT image FROM " . CONFIG::DBTables()->user . " WHERE active = 1 AND id = " . $id;
            $query = $database->prepare($sql);
            $query->execute();
            $result = $query->fetch(\PDO::FETCH_ASSOC);
            $query->closeCursor();
            if( $result !== false && $result !== null && $result !== "" ) {
                $old_image = $result["image"];
                FileService::delete_file($old_image);
            }
        }

        if(isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $result = DatabaseService::update(Config::DBTables()->user, $id, $data);
        if($result === false || $result === null) {
            APIService::response_fail("Update failed.", 500);
        }
        return $result ? self::get_from_id($id) : false;
    }

    /* ========================================================== *
    * DELETE
    * ========================================================== */

    /* Delete a user */
    public static function set_active($id, $active)
    {
        $result = DatabaseService::set_active(Config::DBTables()->user, $id, $active);
        if( $result === false || $result === null) {
            APIService::response_fail("There was an error deleting the user.", 500);
        }
        return $result;
    }

    public static function delete_dependencies($user_id, $table)
    {
        $where = array("user_id" => $user_id);
        $result = DatabaseService::delete($table, $where);
        return $result;
    }

    public static function delete_viewer_dependencies($user_id)
    {
        $table = Config::DBTables()->viewer;
        /* Delete where user is creator */
        $where = array("creator_id" => $user_id);
        $result = DatabaseService::delete($table, $where);
        /* Delete where user is viewer */
        $where = array("viewer_id" => $user_id);
        $result = DatabaseService::delete($table, $where);
        return $result;
    }

    public static function delete_for_id($id)
    {
        $where = array("id" => $id);
        $result = DatabaseService::delete(Config::DBTables()->user, $where);
        return $result;
    }

    /* ===================================================== *
    * Public Functions
    * ===================================================== */

    /* Check if user has unique username and email */
    public static function unique_username_and_email($id, $data, &$error = null)
    {
        $properties = ['username', 'email'];

        foreach( $properties as $property ){
            if(isset($data[$property]))
            {
                if(!self::is_unique_attribute($property, $id, $data)){
                    $error = "This " . $property . " is already in use.";
                    return false;
                }
            }
        }
        return true;
    }

    /* See if a user is a creator */
    public static function is_creator($user_id)
    {
        $sql = "SELECT username FROM user WHERE active = 1 AND id = " . $user_id . " AND role = 'creator'";
        $database = Database::instance();
        $query = $database->prepare($sql);
        $query->execute();
        $result = $query->fetch(\PDO::FETCH_ASSOC);
        $query->closeCursor();
        return ($result !== false && $result !== null && count($result) !== 0);
    }

    /* ===================================================== *
    * Private Functions
    * ===================================================== */

    /* Check if an attribute is unique */
    private static function is_unique_attribute($attribute, $id, $data){
        $where = array($attribute => $data[$attribute]);
        $result = DatabaseService::get(Config::DBTables()->user, $where);

        if($result !== false && count($result) !== 0){
            $temp_user = new User($result[0], false);
            return ($temp_user->id === $id);
        }
        return true;
    }
}
