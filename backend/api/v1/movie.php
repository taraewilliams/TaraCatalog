<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Movie;


$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/movies";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* ========================================================== *
        * GET MOVIES
        * ========================================================== */

        /* Get a single movie */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $id = intval($args['id']);
            $movie = Movie::get_from_id($user_id, $id);
            APIService::response_success($movie);
        });

        /* Get all movies */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $movies = Movie::get_all($user_id);
            APIService::response_success($movies);
        });

        /* Get a set number of movies */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $offset = intval($args['offset']);
            $limit = intval($args['limit']);
            $movies = Movie::get_all_with_limit($user_id, $offset, $limit);
            APIService::response_success($movies);
        });

        /* Get all movies on the watch list */
        $app->get($resource . '/watch/list/{watch}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $watch = intval($args['watch']);
            $movies = Movie::get_all_on_watch_list($user_id, $watch);
            APIService::response_success($movies);
        });

        /* Get all movies ordered by a specific field */
        $app->get($resource . '/order_by/{option}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $option = $args['option'];
            $movies = Movie::get_all_with_order($user_id, $option);
            APIService::response_success($movies);
        });

        /* Get movies for multiple filters */
        $app->post($resource. '/filter', function () use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "format",
                "edition",
                "content_type",
                "mpaa_rating",
                "location",
                "season"
            ));

            $movie = Movie::get_for_search($user_id, $params);
            APIService::response_success($movie);
        });

        /* Get movies for multiple filters with order */
        $app->post($resource. '/filter/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $conj = "AND";

            $order = $args['order'];
            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "format",
                "edition",
                "content_type",
                "mpaa_rating",
                "location",
                "season"
            ));

            $movie = Movie::get_for_search($user_id, $params, $conj, $order);
            APIService::response_success($movie);
        });

        /* ========================================================== *
        * GET MOVIE COUNTS
        * ========================================================== */

        /* Count all movies */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $movies = Movie::count_movies($user_id);
            APIService::response_success($movies);
        });

        /* Count movies with different content types */
        $app->get($resource . '/content_type/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $movies = Movie::get_all_content_type_counts($user_id);
            APIService::response_success($movies);
        });

        /* Count movies with different formats */
        $app->get($resource . '/format/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $movies = Movie::get_all_format_counts($user_id);
            APIService::response_success($movies);
        });

        /* Count movies with different mpaa ratings */
        $app->get($resource . '/mpaa_rating/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $movies = Movie::get_all_mpaa_rating_counts($user_id);
            APIService::response_success($movies);
        });

        /* Count movies with different mpaa ratings, grouped by under PG and above PG */
        $app->get($resource . '/mpaa_rating_grouped/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $movies = Movie::get_all_mpaa_rating_counts_grouped($user_id);
            APIService::response_success($movies);
        });

        /* ========================================================== *
        * POST
        * ========================================================== */

        /* Create a movie */
        $app->post($resource, function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, array(
                "title",
                "format"
            ), array(
                "edition",
                "content_type",
                "mpaa_rating",
                "location",
                "season",
                "watch_list",
                "notes"
            ));
            $params["user_id"] = $user_id;

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($files['image'])) {
                $params['image'] = Movie::set_image($files, $params["title"]);
            }

            $movie = Movie::create_from_data($params);
            APIService::response_success($movie);
        });

        /* ========================================================== *
        * PUT
        * ========================================================== */

        /* Update a movie */
        $app->post($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $id = intval($args["id"]);
            if (!Movie::get_from_id($user_id, $id)){
                APIService::response_fail("There was a problem updating the movie.", 500);
            }

            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "format",
                "edition",
                "content_type",
                "mpaa_rating",
                "location",
                "season",
                "watch_list",
                "notes"
            ));

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($params["title"])){
                $title = $params["title"];
            }else{
                $title = Movie::get_title_for_id($user_id, $id);
            }

            if(isset($files['image'])) {
                $params['image'] = Movie::set_image($files, $title);
            }

            $movie = Movie::update($user_id, $id, $params);
            APIService::response_success($movie);
        });


        /* ========================================================== *
        * DELETE
        * ========================================================== */

        /* Delete a movie */
        $app->delete($resource . '/{id}', function ($response, $request, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $id = intval($args['id']);

            if (!Movie::get_from_id($user_id, $id)){
                APIService::response_fail("There was a problem deleting the movie.", 500);
            }

            $result = Movie::set_active($id, 0);
            APIService::response_success(true);
        });
    });
});
