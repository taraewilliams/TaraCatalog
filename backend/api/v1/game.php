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
            $games = Game::get_all();
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get a set number of games */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $offset = intval($args['offset']);
            $limit = intval($args['limit']);

            $games = Game::get_all_with_limit($offset, $limit);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get all games on the play list */
        $app->get($resource . '/play/list/{play}', function ($request, $response, $args) use ($app)
        {
            $play = intval($args['play']);
            $games = Game::get_all_on_play_list($play);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get all games ordered by a specific field */
        $app->get($resource . '/order_by/{option}', function ($request, $response, $args) use ($app)
        {
            $option = $args['option'];
            $games = Game::get_all_with_order($option);
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get games for multiple filters */
        $app->post($resource. '/filter', function () use ($app)
        {
            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "platform",
                "location"
            ));

            $game = Game::get_for_filter_params($params);
            if($game === false || $game === null) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($game);
        });

        /* Get games for multiple filters with order */
        $app->post($resource. '/filter/{order}', function ($request, $response, $args) use ($app)
        {
            $order = $args['order'];
            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "platform",
                "location"
            ));

            $game = Game::get_for_filter_params($params, $order);
            if($game === false || $game === null) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($game);
        });

        /* Get a single game */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $id = intval($args['id']);
            $game = Game::get_from_id($id);

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
            $games = Game::get_all_platform_counts();
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Count all games */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $games = Game::count_games();
            if($games === false) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }
            APIService::response_success($games);
        });

        /* Get all platforms */
        $app->get($resource . '/platforms/all', function ($request, $response, $args) use ($app)
        {
            $authors = Game::get_platforms();
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
            $params = APIService::build_params($_REQUEST, array(
                "title"
            ), array(
                "platform",
                "location",
                "play_list"
            ));

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
            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "platform",
                "location",
                "play_list"
            ));

            $id = intval($args["id"]);

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($params["title"])){
                $title = $params["title"];
            }else{
                $title = Game::get_title_for_id($id);
            }

            if(isset($files['image'])) {
                $params['image'] = Game::set_image($files, $title);
            }

            $game = Game::update($id, $params);
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
            $id = intval($args["id"]);
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
