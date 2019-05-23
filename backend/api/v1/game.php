<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Game;


$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/games";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get all games */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $games = Game::get_all($user_id);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get a set number of games */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $offset = intval($args['offset']);
            $limit = intval($args['limit']);

            $games = Game::get_all_with_limit($user_id, $offset, $limit);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get all games on the play list */
        $app->get($resource . '/play/list/{play}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $play = intval($args['play']);
            $games = Game::get_all_on_play_list($user_id, $play);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get all games ordered by a specific field */
        $app->get($resource . '/order_by/{option}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $option = $args['option'];
            $games = Game::get_all_with_order($user_id, $option);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get games for multiple filters */
        $app->post($resource. '/filter', function () use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "platform",
                "location",
                "esrb_rating"
            ));

            $game = Game::get_for_filter_params($user_id, $params);
            if($game === false || $game === null) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($game);
        });

        /* Get games for multiple filters with order */
        $app->post($resource. '/filter/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $order = $args['order'];
            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "platform",
                "location",
                "esrb_rating"
            ));

            $game = Game::get_for_filter_params($user_id, $params, $order);
            if($game === false || $game === null) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($game);
        });

        /* Get a single game */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $id = intval($args['id']);
            $game = Game::get_from_id($user_id, $id);

            if($game === false) {
                APIService::response_fail("There was a problem getting game.", 500);
            }
            if($game === null) {
                APIService::response_fail("The requested game does not exist.", 404);
            }
            APIService::response_success($game);
        });

        /* Count games with different platforms */
        $app->get($resource . '/platform/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $games = Game::get_all_platform_counts($user_id);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Count games with different esrb ratings */
        $app->get($resource . '/esrb_rating/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $games = Game::get_all_esrb_rating_counts($user_id);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Count all games */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $games = Game::count_games($user_id);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get all platforms */
        $app->get($resource . '/platforms/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $authors = Game::get_platforms($user_id);
            if($authors === false) {
                APIService::response_fail("There was a problem getting the platforms.", 500);
            }
            APIService::response_success($authors);
        });


        /* ========================================================== *
        * POST
        * ========================================================== */

        /* Create a game */
        $app->post($resource, function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, array(
                "title"
            ), array(
                "platform",
                "location",
                "play_list",
                "esrb_rating"
            ));
            $params["user_id"] = $user_id;


            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($files['image'])) {
                $params['image'] = Game::set_image($files, $params["title"]);
            }

            $game = Game::create_from_data($params);
            if($game === false || $game === null) {
                APIService::response_fail("There was a problem creating the game.", 500);
            }
            APIService::response_success($game);
        });

        /* ========================================================== *
        * PUT
        * ========================================================== */

        /* Update a game */
        $app->post($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $id = intval($args["id"]);
            if (!Game::get_from_id($user_id, $id)){
                APIService::response_fail("There was a problem updating the game.", 500);
            }

            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "platform",
                "location",
                "play_list",
                "esrb_rating"
            ));

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($params["title"])){
                $title = $params["title"];
            }else{
                $title = Game::get_title_for_id($user_id, $id);
            }

            if(isset($files['image'])) {
                $params['image'] = Game::set_image($files, $title);
            }

            $game = Game::update($user_id, $id, $params);
            if($game === false || $game === null) {
                APIService::response_fail("There was a problem updating the game.", 500);
            }
            APIService::response_success($game);
        });


        /* ========================================================== *
        * DELETE
        * ========================================================== */

        /* Delete a game */
        $app->delete($resource . '/{id}', function ($response, $request, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $id = intval($args['id']);

            if (!Game::get_from_id($user_id, $id)){
                APIService::response_fail("There was a problem deleting the game.", 500);
            }

            $result = Game::set_active($id, 0);

            if( $result === false ) {
                APIService::response_fail("There was an error setting the active state of that game.", 500);
            }
            if( $result === null ) {
                APIService::response_fail("The requested game does not exist.", 404);
            }
            APIService::response_success(true);
        });
    });
});
