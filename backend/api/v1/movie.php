<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Movie;
use TaraCatalog\Model\Media;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;

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
            $movie = Media::get_from_id($user_id, $id, Config::DBTables()->movie);
            APIService::response_success($movie);
        });

        /* Get all movies */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order_by = Constants::default_order()->movie;
            $movies = Media::get_all($user_id, CONFIG::DBTables()->movie, $order_by);
            APIService::response_success($movies);
        });

        /* Get all movies on the todo list */
        $app->get($resource . '/todo/list/{todo}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $todo = intval($args['todo']);
            $order_by = Constants::default_order()->movie;
            $movies = Media::get_all_on_todo_list($user_id, $todo, CONFIG::DBTables()->movie, $order_by);
            APIService::response_success($movies);
        });

        /* Get a set number of movies */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $offset = intval($args['offset']);
            $limit = intval($args['limit']);
            $order_by = Constants::default_order()->movie;
            $movies = Media::get_all_with_limit($user_id, CONFIG::DBTables()->movie, $order_by, $offset, $limit);
            APIService::response_success($movies);
        });

        /* Get all movies ordered by a specific field */
        $app->get($resource . '/order_by/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order = $args['order'];
            $movies = Media::get_all_with_order($user_id, CONFIG::DBTables()->movie, $order);
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
                "season",
                "genre",
                "complete_series",
                "running_time"
            ));

            $order_by = Constants::default_order()->movie;
            $enum_keys = Constants::enum_columns()->movie;
            $movies = Media::get_for_search($user_id, CONFIG::DBTables()->movie, $params, $order_by, $enum_keys);
            APIService::response_success($movies);
        });

        /* Get movies for multiple filters with order */
        $app->post($resource. '/filter/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $order = $args['order'];
            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "format",
                "edition",
                "content_type",
                "mpaa_rating",
                "location",
                "season",
                "genre",
                "complete_series",
                "running_time"
            ));

            $order_by = "ORDER BY " . $order;
            $enum_keys = Constants::enum_columns()->movie;
            $movies = Media::get_for_search($user_id, CONFIG::DBTables()->movie, $params, $order_by, $enum_keys);
            APIService::response_success($movies);
        });

        /* ========================================================== *
        * GET MOVIE COUNTS
        * ========================================================== */

        /* Count all movies */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $movies = Media::count_media($user_id, CONFIG::DBTables()->movie);
            APIService::response_success($movies);
        });

        /* Count movies with different column values */
        $app->get($resource . '/column_count/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $column_name = $args["column"];
            $header = "movie_" . $column_name;
            $movies = Media::get_counts_for_column(Config::DBTables()->movie, $user_id, $column_name, $header);
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
        * GET ALL DISTINCT VALUES FOR A COLUMN
        * ========================================================== */

        /* Get all distinct values for a column */
        $app->get($resource . '/column_values/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $column_name = $args["column"];
            $column_values = Media::get_distinct_for_column($user_id, Config::DBTables()->movie, $column_name);
            APIService::response_success($column_values);
        });

        /* Get total running time */
        $app->get($resource . '/running_time/total', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $running_time = Movie::sum_running_time($user_id, Config::DBTables()->movie);
            APIService::response_success($running_time);
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
                "todo_list",
                "notes",
                "genre",
                "complete_series",
                "running_time"
            ));
            $params["user_id"] = $user_id;

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $params["title"], '/movies');
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
            if (!Media::get_from_id($user_id, $id, Config::DBTables()->movie)){
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
                "todo_list",
                "notes",
                "genre",
                "complete_series",
                "running_time"
            ));

            /* Check that enums are valid */
            if(isset($params["format"])){
                if(!Media::is_valid_enum(Constants::movie_format(), $params["format"])){
                    APIService::response_fail("Must choose a valid value for format.", 400);
                }
            }
            if(isset($params["content_type"])){
                if(!Media::is_valid_enum(Constants::movie_content_type(), $params["content_type"])){
                    APIService::response_fail("Must choose a valid value for content type.", 400);
                }
            }
            if(isset($params["mpaa_rating"])){
                if(!Media::is_valid_enum(Constants::movie_mpaa_rating(), $params["mpaa_rating"])){
                    APIService::response_fail("Must choose a valid value for MPAA rating.", 400);
                }
            }
            if(isset($params["location"])){
                if(!Media::is_valid_enum(Constants::media_location(), $params["location"])){
                    APIService::response_fail("Must choose a valid value for location.", 400);
                }
            }
            if(isset($params["complete_series"])){
                if(!Media::is_valid_enum(Constants::media_complete_series(), $params["complete_series"])){
                    APIService::response_fail("Must choose a valid value for complete series.", 400);
                }
            }

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($params["title"])){
                $title = $params["title"];
            }else{
                $title = Media::get_column_value_for_id($user_id, $id, CONFIG::DBTables()->movie, "title");
            }

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $title, '/movies');
            }

            $movie = Media::update($user_id, $id, $params, Config::DBTables()->movie);
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

            if (!Media::get_from_id($user_id, $id, Config::DBTables()->movie)){
                APIService::response_fail("There was a problem deleting the movie.", 500);
            }

            $result = Media::set_active($id, 0, Config::DBTables()->movie);
            APIService::response_success(true);
        });
    });
});
