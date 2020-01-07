<?php

namespace TaraCatalog\Config;

class HttpFailCodes
{
    public static function http_response_fail(){
        return (object) array(

            /* Create errors */

            "create_media" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem creating the media.",
                "type"       => "danger"
            ),
            "create_user" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem creating the user.",
                "type"       => "danger"
            ),
            "create_viewer" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem creating the viewer.",
                "type"       => "danger"
            ),
            "viewer_create_self" => (object) array(
                "code"       => 400,
                "message"    => "Cannot create viewer relationship with yourself.",
                "type"       => "warning"
            ),

            /* Update errors */

            "update_media" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem updating the media.",
                "type"       => "danger"
            ),
            "update_user" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem updating the user.",
                "type"       => "danger"
            ),
            "update_viewer" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem updating the viewer.",
                "type"       => "danger"
            ),

            /* Delete errors */

            "delete_media" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem deleting the media.",
                "type"       => "danger"
            ),
            "delete_viewer" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem deleting the viewer.",
                "type"       => "danger"
            ),
            "delete_user" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem deleting the user.",
                "type"       => "danger"
            ),

            /* Authentication errors */

            "logging_in" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem logging in.",
                "type"       => "danger"
            ),
            "auth_fail" => (object) array(
                "code"       => 401,
                "message"    => "Authentication failed.",
                "type"       => "danger"
            ),
            "session_fail" => (object) array(
                "code"       => 401,
                "message"    => "Authentication failed.",
                "type"       => "danger"
            ),
            "admin_request" => (object) array(
                "code"       => 401,
                "message"    => "Admin only request.",
                "type"       => "warning"
            ),
            "creator_request" => (object) array(
                "code"       => 401,
                "message"    => "Creator only request.",
                "type"       => "warning"
            ),

            /* Get errors */

            /* Get media errors */
            "get_media_images" => (object) array(
                "code"       => 500,
                "message"    => "There was an error getting the images.",
                "type"       => "danger"
            ),
            "get_media" => (object) array(
                "code"       => 500,
                "message"    => "There was an error getting the media.",
                "type"       => "danger"
            ),
            "get_media_counts" => (object) array(
                "code"       => 500,
                "message"    => "There was an error getting the media counts.",
                "type"       => "danger"
            ),
            "get_column_values" => (object) array(
                "code"       => 500,
                "message"    => "There was an error getting the column values.",
                "type"       => "danger"
            ),
            "get_single_column_value" => (object) array(
                "code"       => 500,
                "message"    => "There was an error getting the column value.",
                "type"       => "danger"
            ),

            /* Get viewer errors */
            "viewer_get_media" => (object) array(
                "code"       => 401,
                "message"    => "You don't have permission to view this list.",
                "type"       => "warning"
            ),
            "get_viewers" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem getting the viewers.",
                "type"       => "danger"
            ),
            "get_single_viewer" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem getting the viewer.",
                "type"       => "danger"
            ),

            /* Get user errors */
            "get_single_user" => (object) array(
                "code"       => 500,
                "message"    => "There was an error getting the user.",
                "type"       => "danger"
            ),
            "get_users" => (object) array(
                "code"       => 500,
                "message"    => "There was an error getting the users.",
                "type"       => "danger"
            ),

            /* Other errors */

            "valid_enums" => (object) array(
                "code"       => 400,
                "message"    => "Must choose a valid value for the enum.",
                "type"       => "warning"
            ),
            "set_image" => (object) array(
                "code"       => 400,
                "message"    => "There was an error saving the picture.",
                "type"       => "warning"
            ),
            "invalid_viewer" => (object) array(
                "code"       => 401,
                "message"    => "Invalid request.",
                "type"       => "warning"
            ),
            "viewer_duplicate" => (object) array(
                "code"       => 400,
                "message"    => "A viewer already exists for this creator.",
                "type"       => "warning"
            ),
            "invalid_user" => (object) array(
                "code"       => 401,
                "message"    => "Invalid request.",
                "type"       => "warning"
            ),
            "invalid_user_cred" => (object) array(
                "code"       => 400,
                "message"    => "Invalid username or password.",
                "type"       => "warning"
            ),
            "user_not_creator" => (object) array(
                "code"       => 400,
                "message"    => "This user is not a creator.",
                "type"       => "warning"
            ),
            "user_unique_prop" => (object) array(
                "code"       => 400,
                "message"    => "User properties are not unique.",
                "type"       => "danger"
            ),
            "running_time" => (object) array(
                "code"       => 500,
                "message"    => "There was a problem getting the running time.",
                "type"       => "danger"
            )
        );
    }
}
