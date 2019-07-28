<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\User;

/* Requests */


/* GET */

/*

1. users/{id}
    Gets a single user for its ID.
    Input: id (user ID)
    Output: User object

2. users/{username}/{password}
    Gets a single user for its username and password.
    Input: username, password
    Output: User object

3. users/non/viewers/all
    Gets users that are not viewing a creator's catalog.
    Input: none
    Output: User object array (with id, username, and image)

4. users/non/views/all
    Gets users whose catalogs a creator can't view.
    Input: none
    Output: User object array (with id, username, and image)
*/


/* POST */

/*

1. users
    Creates a new user.
    Input: (required) username, password, email
        (optional) first_name, last_name, color_scheme, role, image
    Output: User object

2. users/{id}
    Updates a user.
    Input: (required) id (user ID)
        (optional) username, password, email, first_name, last_name, color_scheme, role, image
    Output: true or false (success or failure)
*/


/* DELETE */

/*

1. users/{id}
    Deletes a user.
    Input: id (user ID)
    Output: true or false (success or failure)
*/


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
            APIService::response_success($users);
        });

        /* Get users whose catalogs a creator can't view */
        $app->get($resource . '/non/views/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $users = User::get_nonviews($user_id);
            usort($users, array("TaraCatalog\Model\User", "sort_viewers"));
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
                $params['image'] = Media::set_image($files, $params["username"], '/users');
            }

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

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($params["username"])){
                $username = $params["username"];
            }else{
                $username = User::get_username_for_id($id);
            }

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $username, '/users');
            }

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
                APIService::response_fail("There was a problem updating user.", 500);
            }
            $result = User::set_active($id, 0);
            APIService::response_success(true);
        });

    });
});
