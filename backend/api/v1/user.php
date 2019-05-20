<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\User;

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
                APIService::response_fail("There was a problem getting user.", 500);
            }

            $user = User::get_from_id($id);
            if($user === false) {
                APIService::response_fail("There was a problem getting user.", 500);
            }
            if($user === null) {
                APIService::response_fail("The requested user does not exist.", 404);
            }
            APIService::response_success($user);
        });

        /* Get a single user for username and password */
        $app->get($resource . '/{username}/{password}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);

            $username = $args['username'];
            $password = $args['password'];

            $user = User::get_from_username_and_password($username, $password);

            if($user === false) {
                APIService::response_fail("There was a problem getting user.", 500);
            }
            if($user === null) {
                APIService::response_fail("The requested user does not exist.", 404);
            }
            if ($session->user->id !== $user->id){
                APIService::response_fail("There was a problem getting user.", 500);
            }
            APIService::response_success($user);
        });

        /* Get users that are not viewing a creator's catalog */
        $app->get($resource . '/non/viewers/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $users = User::get_nonviewers($user_id);
            usort($users, array("TaraCatalog\Model\User", "sort_viewers"));

            if($users === false) {
                APIService::response_fail("There was a problem getting user.", 500);
            }
            if($users === null) {
                APIService::response_fail("The requested user does not exist.", 404);
            }
            APIService::response_success($users);
        });

        /* Get users whose catalogs a creator can't view */
        $app->get($resource . '/non/views/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $users = User::get_nonviews($user_id);
            usort($users, array("TaraCatalog\Model\User", "sort_viewers"));

            if($users === false) {
                APIService::response_fail("There was a problem getting user.", 500);
            }
            if($users === null) {
                APIService::response_fail("The requested user does not exist.", 404);
            }
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

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($files['image'])) {
                $params['image'] = User::set_image($files, $params["username"]);
            }

            $user = User::create_from_data($params);
            if($user === false || $user === null) {
                APIService::response_fail("There was a problem creating user.", 500);
            }
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

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($params["username"])){
                $username = $params["username"];
            }else{
                $username = User::get_username_for_id($id);
            }

            if(isset($files['image'])) {
                $params['image'] = User::set_image($files, $username);
            }

            $user = User::update($id, $params);
            if($user === false) {
                APIService::response_fail("There was an error saving the user.", 500);
            }
            if($user === null) {
                APIService::response_fail("The requested user does not exist.", 404);
            }
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
                APIService::response_fail("There was a problem updating user.", 500);
            }
            $result = User::set_active($id, 0);

            if( $result === false ) {
                APIService::response_fail("There was an error setting the active state of that user.", 500);
            }
            if( $result === null ) {
                APIService::response_fail("The requested user does not exist.", 404);
            }
            APIService::response_success(true);
        });

    });
});
