<?php

namespace TaraCatalog\Model;

use TaraCatalog\Config\Config;
use TaraCatalog\Service\DatabaseService;
use TaraCatalog\Service\APIService;

class Session
{
    const EXPIRES_DAYS = 1;

    public $id;
    public $user_id;
    public $expires;
    public $token;

    public $created;
    public $updated;
    public $active;

    /* Relationships */
    public $user;

    function __construct($data, $include_relationships = true)
    {
        $this->id       = isset($data['id']) ? intval($data['id']) : null;
        $this->user_id  = isset($data['user_id']) ? intval($data['user_id']) : null;
        $this->expires  = isset($data['expires']) ? new \DateTime($data['expires']) : (new \DateTime('now'))->modify("+" . self::EXPIRES_DAYS . " day");
        $this->token    = isset($data['token']) ? $data['token'] : bin2hex(openssl_random_pseudo_bytes(40));

        $this->created  = isset($data['created']) ? new \DateTime($data['created']) : new \DateTime('now');
        $this->updated  = isset($data['updated']) ? new \DateTime($data['updated']) : new \DateTime('now');
        $this->active   = isset($data['active']) ? (boolean) $data['active'] : true;

        /* Relationship */
        if($include_relationships) {
            $this->user = self::get_user_for_session($this);
        } else {
            unset($this->user);
        }
    }

    /* =====================================================
    * Database Functions
    * ===================================================== */

    /* ========================================================== *
    * POST
    * ========================================================== */

    /* Create a session */
    public static function create_from_data($data)
    {
        $session = new Session($data);

        $data = array(
            "user_id"       => $session->user_id,
            "token"         => $session->token,
            "expires"       => $session->expires->format('Y-m-d H:i:s'),
            "created"       => $session->created,
            "updated"       => $session->updated,
            "active"        => $session->active
        );

        $id = DatabaseService::create(Config::DBTables()->session, $data);
        if($id === false || $id === null) {
            APIService::response_fail("There was a problem logging in.", 500);
        }
        $session->id = $id;
        return $session;
    }

    /* ========================================================== *
    * GET
    * ========================================================== */

    /* Get a single session */
    public static function get_from_id($id)
    {
        $where = array("id" => $id);
        $result = DatabaseService::get(Config::DBTables()->session, $where);
        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("Authentication failed.", 500);
        }
        return new Session($result[0]);
    }

    /* Get a user for the session */
    private static function get_user_for_session($session)
    {
        $where = array("id" => $session->user_id);
        $result = DatabaseService::get(Config::DBTables()->user, $where);
        if($result === false || $result === null || count($result) === 0) {
            APIService::response_fail("There was a problem getting the user.", 500);
        }
        return new User($result[0], false);
    }

    /* ========================================================== *
    * DELETE
    * ========================================================== */

    public static function set_active($id)
    {
        return self::delete_for_id($id);
    }

    public static function delete_for_id($id)
    {
        $where = array("id" => $id);
        return self::delete($where);
    }

    public static function delete_for_user_id($user_id)
    {
        $where = array("user_id" => $user_id);
        return self::delete($where);
    }

    public static function delete($where)
    {
        $result = DatabaseService::delete(Config::DBTables()->session, $where);
        return $result;
    }
}
