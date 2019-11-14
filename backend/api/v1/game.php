<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Game;
use TaraCatalog\Model\Media;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/games";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* ========================================================== *
        * GET GAMES
        * ========================================================== */

        /* Get a single game */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $id = intval($args['id']);
            $game = Media::get_from_id($user_id, $id, Config::DBTables()->game);
            APIService::response_success($game);
        });

        /* Get all games */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order_by = Constants::default_order()->game;
            $games = Media::get_all($user_id, CONFIG::DBTables()->game, $order_by);
            APIService::response_success($games);
        });

        /* Get all games on the todo list or not on the todo list */
        $app->get($resource . '/todo/list/{todo}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $todo = intval($args['todo']);
            $order_by = Constants::default_order()->game;
            $games = Media::get_all_on_todo_list($user_id, $todo, CONFIG::DBTables()->game, $order_by);
            APIService::response_success($games);
        });

        /* Get a set number of games */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $offset = intval($args['offset']);
            $limit = intval($args['limit']);
            $order_by = Constants::default_order()->game;
            $games = Media::get_all_with_limit($user_id, CONFIG::DBTables()->game, $order_by, $offset, $limit);
            APIService::response_success($games);
        });

        /* Get all games ordered by a specific field */
        $app->get($resource . '/order_by/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order = $args['order'];
            $games = Media::get_all_with_order($user_id, CONFIG::DBTables()->game, $order);
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
                "esrb_rating",
                "genre",
                "complete_series"
            ));

            $order_by = Constants::default_order()->game;
            $enum_keys = Constants::enum_columns()->game;
            $games = Media::get_for_search($user_id, CONFIG::DBTables()->game, $params, $order_by, $enum_keys);
            APIService::response_success($games);
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
                "esrb_rating",
                "genre",
                "complete_series"
            ));

            $order_by = "ORDER BY " . $order;
            $enum_keys = Constants::enum_columns()->game;
            $games = Media::get_for_search($user_id, CONFIG::DBTables()->game, $params, $order_by, $enum_keys);
            APIService::response_success($games);
        });

        /* ========================================================== *
        * GET GAME COUNTS
        * ========================================================== */

        /* Count all games */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $games = Media::count_media($user_id, CONFIG::DBTables()->game);
            APIService::response_success($games);
        });

        /* Count games with different column values */
        $app->get($resource . '/column_count/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $column_name = $args["column"];
            $header = "game_" . $column_name;
            $games = Media::get_counts_for_column(Config::DBTables()->game, $user_id, $column_name, $header);
            APIService::response_success($games);
        });

        /* ========================================================== *
        * GET ALL DISTINCT VALUES FOR A COLUMN
        * ========================================================== */

        /* Get all distinct values for a column */
        $app->get($resource . '/column_values/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $column_name = $args["column"];
            $column_values = Media::get_distinct_for_column($user_id, Config::DBTables()->game, $column_name);
            APIService::response_success($column_values);
        });

        /* ========================================================== *
        * POST
        * ========================================================== */

        /* Create a game */
        $app->post($resource, function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;
            $username = $session->user->username;

            $params = APIService::build_params($_REQUEST, array(
                "title"
            ), array(
                "platform",
                "location",
                "todo_list",
                "esrb_rating",
                "notes",
                "genre",
                "complete_series"
            ));
            $params["user_id"] = $user_id;

            /* Check that enums are set to valid values */
            $enum_property_list = array(
                array("property" => "esrb_rating", "enum" => Constants::game_esrb_rating()),
                array("property" => "location", "enum" => Constants::media_location()),
                array("property" => "complete_series", "enum" => Constants::media_complete_series())
            );

            if(!Media::are_valid_enums($enum_property_list, $params)){
                APIService::response_fail("There was a problem setting the enums.", 500);
            }

            /* Set image */
            $files = APIService::build_files($_FILES, null, array( "image" ));

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $params["title"], '/' . $username . '/games');
            }

            /* Create game */
            $game = Game::create_from_data($params);
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
            $username = $session->user->username;

            $id = intval($args["id"]);
            if (!Media::get_from_id($user_id, $id, Config::DBTables()->game)){
                APIService::response_fail("There was a problem updating the game.", 500);
            }

            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "platform",
                "location",
                "todo_list",
                "esrb_rating",
                "notes",
                "genre",
                "complete_series"
            ));

            /* Check that enums are set to valid values */
            $enum_property_list = array(
                array("property" => "esrb_rating", "enum" => Constants::game_esrb_rating()),
                array("property" => "location", "enum" => Constants::media_location()),
                array("property" => "complete_series", "enum" => Constants::media_complete_series())
            );

            if(!Media::are_valid_enums($enum_property_list, $params)){
                APIService::response_fail("There was a problem setting the enums.", 500);
            }

            /* Set image */
            $files = APIService::build_files($_FILES, null, array( "image" ));

            if(isset($params["title"])){
                $title = $params["title"];
            }else{
                $title = Media::get_column_value_for_id($user_id, $id, CONFIG::DBTables()->game, "title");
            }

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $title, '/' . $username . '/games');
            }

            $game = Media::update($user_id, $id, $params, Config::DBTables()->game);
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

            if (!Media::get_from_id($user_id, $id, Config::DBTables()->game)){
                APIService::response_fail("There was a problem deleting the game.", 500);
            }

            $result = Media::delete_for_id($id, $user_id, Config::DBTables()->game);
            APIService::response_success(true);
        });
    });
});
