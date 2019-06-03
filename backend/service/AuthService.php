<?php

namespace TaraCatalog\Service;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Database;
use TaraCatalog\Model\User;
use TaraCatalog\Model\Session;

class AuthService
{
    public static function login($username, $password){

        $user = User::get_from_username_and_password($username, $password);

        /* Delete old sessions for user */
        Session::delete_for_user_id($user->id);

        /* Create new session */
        $data = array(
            "user_id" => $user->id
        );
        $session = Session::create_from_data($data);
        return $session;
    }

    public static function logout($session_id)
    {
        return Session::delete_for_id($session_id);
    }

    public static function authenticate($session_id, $token, &$error = null)
    {
        $session = Session::get_from_id($session_id);

        /* Check if session token matches token from request */
        if($session->token !== $token) {
            $error = "Authentication failed.";
            return false;
        }
        /* Check that the session is not expired */
        if( $session->expires->getTimestamp() < (new \DateTime("now"))->getTimestamp() ) {
            $error = "Session expired.";
            return false;
        }
        return $session;
    }
}
