<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Service\FileService;
use TaraCatalog\Model\User;
use TaraCatalog\Model\Media;
use TaraCatalog\Model\Viewer;
use TaraCatalog\Config\Constants;
use TaraCatalog\Config\Config;

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/users";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get a single user */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);

            $id = intval($args['id']);
            if ($session->user->id !== $id){
                APIService::response_fail("Invalid request.", 401);
            }

            $user = User::get_from_id($id);
            APIService::response_success($user);
        });

        /* Get a single user for username and password */
        $app->get($resource . '/{username}/{password}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);

            $username = $args['username'];
            $password = $args['password'];

            $user = User::get_from_username_and_password($username, $password);
            if ($session->user->id !== $user->id){
                APIService::response_fail("Invalid request.", 401);
            }
            APIService::response_success($user);
        });

        /* Get users that are not viewing a creator's catalog */
        $app->get($resource . '/non/viewers/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $users = User::get_nonviewers($user_id);
            APIService::response_success($users);
        });

        /* Get users (who are creators) whose catalogs a user can't view */
        $app->get($resource . '/non/views/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $users = User::get_nonviews($user_id);
            APIService::response_success($users);
        });

        /* ========================================================== *
        * POST
        * ========================================================== */

        /* Create a user */
        $app->post($resource, function () use ($app)
        {
            $params = APIService::build_params($_REQUEST, array(
                "username",
                "password",
                "email"
            ), array(
                "first_name",
                "last_name",
                "color_scheme",
                "role"
            ));

            if(!User::unique_username_and_email(null, $params, $error)) {
                APIService::response_fail($error, 500);
            }

            /* Check that enums are set to valid values */
            $enum_property_list = array(
                array("property" => "color_scheme", "enum" => Constants::user_color_scheme()),
                array("property" => "role", "enum" => Constants::user_role())
            );

            if(!Media::are_valid_enums($enum_property_list, $params)){
                APIService::response_fail("There was a problem setting the enums.", 500);
            }

            /* Set image */
            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $params["username"], '/users');
            }

            /* Create user */
            $user = User::create_from_data($params);
            APIService::response_success($user);
        });


        /* ========================================================== *
        * PUT
        * ========================================================== */

        /* Update a user */
        $app->post($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $id = intval($args['id']);
            if ($session->user->id !== $id){
                APIService::response_fail("There was a problem updating user.", 500);
            }

            $params = APIService::build_params($_REQUEST, null, array(
                "username",
                "password",
                "email",
                "first_name",
                "last_name",
                "color_scheme",
                "role"
            ));

            if(!User::unique_username_and_email($id, $params, $error)) {
                APIService::response_fail($error, 500);
            }

            /* Check that enums are set to valid values */
            $enum_property_list = array(
                array("property" => "color_scheme", "enum" => Constants::user_color_scheme()),
                array("property" => "role", "enum" => Constants::user_role())
            );

            if(!Media::are_valid_enums($enum_property_list, $params)){
                APIService::response_fail("There was a problem setting the enums.", 500);
            }

            /* Set image */
            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            $old_username = User::get_username_for_id($id);
            if(isset($params["username"])){
                $username = $params["username"];
            }else{
                $username = $old_username;
            }

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $username, '/users');
            }

            /* If role is changing to viewer, delete viewer objects for creator ID
            so other users can no longer view this catalog */
            if(isset($params['role'])){
                if($params['role'] == Constants::user_role()->viewer){
                    Viewer::delete_for_creator($id);
                }
            }

            /* If updating username, change folder images are listed under */
            /* Also, update image paths in database */
            if($old_username != $username){

                /* Change folder name */
                $old_dir = FileService::MAIN_DIR . '/' . $old_username;
                $new_dir = FileService::MAIN_DIR . '/' . $username;
                FileService::rename_directory($old_dir, $new_dir);

                /* Update image paths in database */
                $book_result = Media::update_image_paths_for_table($id, CONFIG::DBTables()->book, $old_username, $username);
                $game_result = Media::update_image_paths_for_table($id, CONFIG::DBTables()->game, $old_username, $username);
                $movie_result = Media::update_image_paths_for_table($id, CONFIG::DBTables()->movie, $old_username, $username);
            }

            /* Update user */
            $user = User::update($id, $params);
            APIService::response_success($user);
        });


        /* ========================================================== *
        * DELETE
        * ========================================================== */

        /* Delete a user */
        $app->delete($resource . '/{id}', function ($response, $request, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $id = intval($args['id']);
            if ($session->user->id !== $id){
                APIService::response_fail("Invalid request.", 401);
            }
            $username = $session->user->username;

            /* Delete user's images */
            $path = FileService::MAIN_DIR . '/' . $username;
            FileService::remove_folder($path);

            $profile_image = $session->user->image;
            FileService::delete_file($profile_image);

            /* Delete books, movies, games */
            User::delete_dependencies($id, Config::DBTables()->book);
            User::delete_dependencies($id, Config::DBTables()->movie);
            User::delete_dependencies($id, Config::DBTables()->game);

            /* Delete viewer relationships */
            User::delete_viewer_dependencies($id);

            /* Delete sessions */
            User::delete_dependencies($id, Config::DBTables()->session);

            $result = User::delete_for_id($id);
            APIService::response_success(true);
        });

    });
});
