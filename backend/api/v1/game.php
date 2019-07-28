<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Game;

/* Requests */


/* GET */

/*

1. games/{id}
    Gets a single game for a user for its ID and user ID.
    Input: id (game ID)
    Output: Game object

2. games
    Gets all games for a user for the user ID.
    Input: none
    Output: Game object array

3. games/todo/list/{todo}
    Gets all games on the todo list or not on the todo list for a user.
    Input: todo (0 or 1)
    Output: Game object array

4. /limit/{offset}/{limit}
    Gets a set number of games with a limit and an offset for a user.
    Input: offset, limit
    Output: Game object array

5. games/order_by/{order}
    Gets all games ordered by a specific field for a user.
    Input: order (the field to order by)
    Output: Game object array

6. games/filter
    Gets games that match the filter options for each field for a user.
    Input: (optional) title, platform, location, esrb_rating
    Output: Game object array

7. games/filter/{order}
    Gets games that match the filter options for each field ordered by a specific field for a user.
    Input: (required) order
        (optional) title, platform, location, esrb_rating
    Output: Game object array

8. games/count/all
    Gets the count of all games for a user.
    Input: none
    Output: Game count

9. games/platform/count
    Gets the count of all games grouped by distinct platform for a user.
    Input: none
    Output: Game counts

10. games/esrb_rating/count
    Gets the count of all games grouped by distinct ESRB rating for a user.
    Input: none
    Output: Game counts

11. games/platforms/all
    Gets all distinct platforms from all games for a user.
    Input: none
    Output: Array of platforms
*/


/* POST */

/*

1. games
    Creates a new game.
    Input: (required) title
        (optional) platform, location, todo_list, esrb_rating, notes, image
    Output: Game object

2. games/{id}
    Updates a game.
    Input: (required) id (game ID)
        (optional) title, platform, location, todo_list, esrb_rating, notes, image
    Output: true or false (success or failure)
*/


/* DELETE */

/*

1. games/{id}
    Deletes a game.
    Input: id (game ID)
    Output: true or false (success or failure)
*/

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
            $game = Game::get_from_id($user_id, $id);
            APIService::response_success($game);
        });

        /* Get all games */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $games = Game::get_all($user_id);
            APIService::response_success($games);
        });

        /* Get all games on the todo list or not on the todo list */
        $app->get($resource . '/todo/list/{todo}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $todo = intval($args['todo']);
            $games = Game::get_all_on_todo_list($user_id, $todo);
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
            APIService::response_success($games);
        });

        /* Get all games ordered by a specific field */
        $app->get($resource . '/order_by/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order = $args['order'];
            $games = Game::get_all_with_order($user_id, $order);
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

            $game = Game::get_for_search($user_id, $params);
            APIService::response_success($game);
        });

        /* Get games for multiple filters with order */
        $app->post($resource. '/filter/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $conj = "AND";

            $order = $args['order'];
            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "platform",
                "location",
                "esrb_rating"
            ));

            $game = Game::get_for_search($user_id, $params, $conj, $order);
            APIService::response_success($game);
        });

        /* ========================================================== *
        * GET GAME COUNTS
        * ========================================================== */

        /* Count all games */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $games = Game::count_games($user_id);
            APIService::response_success($games);
        });

        /* Count games with different platforms */
        $app->get($resource . '/platform/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $games = Game::get_all_platform_counts($user_id);
            APIService::response_success($games);
        });

        /* Count games with different esrb ratings */
        $app->get($resource . '/esrb_rating/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $games = Game::get_all_esrb_rating_counts($user_id);
            APIService::response_success($games);
        });

        /* ========================================================== *
        * GET ALL DISTINCT VALUES FOR A COLUMN
        * ========================================================== */

        /* Get all platforms */
        $app->get($resource . '/platforms/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $authors = Game::get_platforms($user_id);
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
                "todo_list",
                "esrb_rating",
                "notes"
            ));
            $params["user_id"] = $user_id;


            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $params["title"], '/games');
            }

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

            $id = intval($args["id"]);
            if (!Game::get_from_id($user_id, $id)){
                APIService::response_fail("There was a problem updating the game.", 500);
            }

            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "platform",
                "location",
                "todo_list",
                "esrb_rating",
                "notes"
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
                $params['image'] = Media::set_image($files, $title, '/games');
            }

            $game = Game::update($user_id, $id, $params);
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
            APIService::response_success(true);
        });
    });
});
