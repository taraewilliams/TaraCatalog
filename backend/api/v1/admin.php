<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Service\FileService;
use TaraCatalog\Model\Media;
use TaraCatalog\Model\User;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;
use TaraCatalog\Config\HttpFailCodes;

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/admin";

        /* ========================================================== *
        * IMAGE REQUESTS
        * ========================================================== */

        /* Get unused media images */
        $app->get($resource . '/images', function ($request, $response, $args) use ($app)
        {
            /* Make an admin request */
            APIService::authenticate_request_admin($_GET);

            $images = Media::get_unused_images();
            APIService::response_success($images);
        });

        /* Delete unused media images */
        $app->delete($resource . '/images', function ($response, $request, $args) use ($app)
        {
            /* Make an admin request */
            APIService::authenticate_request_admin($_GET);

            $deleted_images = Media::delete_unused_images();
            APIService::response_success($deleted_images);
        });

        /* ========================================================== *
        * USER REQUESTS
        * ========================================================== */

        /* Get all active users */
        $app->get($resource . '/users', function ($request, $response, $args) use ($app)
        {
            APIService::authenticate_request_admin($_GET);

            $active = 1;
            $users = User::get_all($active);
            APIService::response_success($users);
        });

        /* Get all inactive users */
        $app->get($resource . '/users/inactive', function ($request, $response, $args) use ($app)
        {
            APIService::authenticate_request_admin($_GET);

            $active = 0;
            $users = User::get_all($active);
            APIService::response_success($users);
        });

        /* Create a user */
        $app->post($resource . '/users', function () use ($app)
        {
            APIService::authenticate_request_admin($_REQUEST);

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
                $http_response = HttpFailCodes::http_response_fail()->user_unique_prop;
                $http_response->message = $error;
                APIService::response_fail($http_response);
            }

            /* Check that enums are set to valid values */
            $enum_property_list = array(
                array("property" => "color_scheme", "enum" => Constants::user_color_scheme()),
                array("property" => "role", "enum" => Constants::user_role())
            );

            Media::are_valid_enums($enum_property_list, $params);

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

        /* Update user admin field */
        $app->post($resource . '/users/{id}', function ($request, $response, $args) use ($app)
        {
            APIService::authenticate_request_admin($_REQUEST);

            $id = intval($args['id']);
            $params = APIService::build_params($_REQUEST, null, array(
                "is_admin"
            ));

            /* Update user */
            $user = User::update($id, $params);
            APIService::response_success($user);
        });

        /* Delete a user */
        $app->delete($resource . '/users/{id}', function ($response, $request, $args) use ($app)
        {
            APIService::authenticate_request_admin($_GET);

            $id = intval($args['id']);
            $username = User::get_username_for_id($id);

            /* Delete user's images */
            $path = FileService::MAIN_DIR . '/' . $username;
            FileService::remove_folder($path);

            $profile_image = User::get_image_for_id($id);
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
