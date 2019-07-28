<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\FileService;
use TaraCatalog\Service\APIService;

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

    public $created;
    public $updated;
    public $active;

    public function __construct($data)
    {
        $this->id               = isset($data['id']) ? intval($data['id']) : null;
        $this->username         = isset($data['username']) ? $data['username'] : null;
        $this->email            = isset($data['email']) ? $data['email'] : null;
        $this->first_name       = isset($data['first_name']) ? $data['first_name'] : null;
        $this->last_name        = isset($data['last_name']) ? $data['last_name'] : null;
        $this->image            = isset($data['image']) ? $data['image'] : null;
        $this->color_scheme     = isset($data['color_scheme']) ? $data['color_scheme'] : 'red';
        $this->role             = isset($data['role']) ? $data['role'] : 'viewer';

        if(isset($data['password'])) {
            $this->hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
        } else if(isset($data['hashed_password'])) {
            $this->hashed_password = $data['hashed_password'];
        } else {
            $this->hashed_password = null;
        }

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
            APIService::response_fail("There was a problem getting the user.", 500);
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
        $sql = "SELECT id, username, image FROM " . CONFIG::DBTables()->user . " WHERE active = 1 AND id != " . $user_id . " AND id NOT IN (" . $viewer_ids . ")";
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
        $sql = "SELECT id, username, image FROM " . CONFIG::DBTables()->user . " WHERE active = 1 AND id != " . $user_id . " AND id NOT IN (" . $creator_ids . ")";
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

    /* ===================================================== *
    * Public Functions
    * ===================================================== */

    /* Check if user has unique username and email */
    public static function unique_username_and_email($id, $data, &$error = null)
    {
        if(isset($data['username']))
        {
            if(!self::is_unique_attribute("username", $id, $data)){
                $error = "This username is already in use.";
                return false;
            }
        }

        if(isset($data['email']))
        {
            if(!self::is_unique_attribute("email", $id, $data)){
                $error = "This email is already in use.";
                return false;
            }
        }
        return true;
    }

    public static function sort_viewers($a, $b){
        return strtolower($a["username"]) > strtolower($b["username"]);
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
