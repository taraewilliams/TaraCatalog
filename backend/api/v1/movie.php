<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Movie;
use TaraCatalog\Model\Media;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;

/* Requests */


/* GET */

/*

1. movies/{id}
    Gets a single movie for a user for its ID and user ID.
    Input: id (movie ID)
    Output: Movie object

2. movies
    Gets all movies for a user for the user ID.
    Input: none
    Output: Movie object array

3. movies/todo/list/{todo}
    Gets all movies on the todo list or not on the todo list for a user.
    Input: todo (0 or 1)
    Output: Movie object array

4. /limit/{offset}/{limit}
    Gets a set number of movies with a limit and an offset for a user.
    Input: offset, limit
    Output: Movie object array

5. movies/order_by/{order}
    Gets all movies ordered by a specific field for a user.
    Input: order (the field to order by)
    Output: Movie object array

6. movies/filter
    Gets movies that match the filter options for each field for a user.
    Input: (optional) title, format, edition, content_type, mpaa_rating, location, season
    Output: Movie object array

7. movies/filter/{order}
    Gets movies that match the filter options for each field ordered by a specific field for a user.
    Input: (required) order
        (optional) title, format, edition, content_type, mpaa_rating, location, season
    Output: Movie object array

8. movies/count/all
    Gets the count of all movies for a user.
    Input: none
    Output: Movie count

9. movies/column_count/{column}
    Gets the count of all movies grouped by distinct column for a user.
    Input: column name
    Output: Movie counts

10. movies/mpaa_rating_grouped/count
    Gets the count of all movies grouped by MPAA ratings under and over PG.
    Input: none
    Output: Movie counts

11. movies/column_values/{column}
    Gets all distinct column values from all movies for a user.
    Input: column name
    Output: Array of column values

*/


/* POST */

/*

1. movies
    Creates a new movie.
    Input: (required) title, format
        (optional) edition, content_type, mpaa_rating, location, season, todo_list, notes, image
    Output: Movie object

2. movies/{id}
    Updates a movie.
    Input: (required) id (movie ID)
        (optional) title, format, edition, content_type, mpaa_rating, location, season, todo_list, notes, image
    Output: true or false (success or failure)
*/


/* DELETE */

/*

1. movies/{id}
    Deletes a movie.
    Input: id (movie ID)
    Output: true or false (success or failure)
*/


$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/movies";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* ========================================================== *
        * GET MOVIES
        * ========================================================== */

        /* 1. Get a single movie */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $id = intval($args['id']);
            $movie = Media::get_from_id($user_id, $id, Config::DBTables()->movie);
            APIService::response_success($movie);
        });

        /* 2. Get all movies */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order_by = Constants::default_order()->movie;
            $movies = Media::get_all($user_id, CONFIG::DBTables()->movie, $order_by);
            APIService::response_success($movies);
        });

        /* 3. Get all movies on the todo list */
        $app->get($resource . '/todo/list/{todo}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $todo = intval($args['todo']);
            $order_by = Constants::default_order()->movie;
            $movies = Media::get_all_on_todo_list($user_id, $todo, CONFIG::DBTables()->movie, $order_by);
            APIService::response_success($movies);
        });

        /* 4. Get a set number of movies */
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

        /* 5. Get all movies ordered by a specific field */
        $app->get($resource . '/order_by/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order = $args['order'];
            $movies = Media::get_all_with_order($user_id, CONFIG::DBTables()->movie, $order);
            APIService::response_success($movies);
        });

        /* 6. Get movies for multiple filters */
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
                "genre"
            ));

            $order_by = Constants::default_order()->movie;
            $enum_keys = Constants::enum_columns()->movie;
            $movies = Media::get_for_search($user_id, CONFIG::DBTables()->movie, $params, $order_by, $enum_keys);
            APIService::response_success($movies);
        });

        /* 7. Get movies for multiple filters with order */
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
                "genre"
            ));

            $order_by = "ORDER BY " . $order;
            $enum_keys = Constants::enum_columns()->movie;
            $movies = Media::get_for_search($user_id, CONFIG::DBTables()->movie, $params, $order_by, $enum_keys);
            APIService::response_success($movies);
        });

        /* ========================================================== *
        * GET MOVIE COUNTS
        * ========================================================== */

        /* 8. Count all movies */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $movies = Media::count_media($user_id, CONFIG::DBTables()->movie);
            APIService::response_success($movies);
        });

        /* 9. Count movies with different column values */
        $app->get($resource . '/column_count/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $column_name = $args["column"];
            $header = "movie_" . $column_name;
            $movies = Media::get_counts_for_column(Config::DBTables()->movie, $user_id, $column_name, $header);
            APIService::response_success($movies);
        });

        /* 10. Count movies with different mpaa ratings, grouped by under PG and above PG */
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

        /* 11. Get all distinct values for a column */
        $app->get($resource . '/column_values/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $column_name = $args["column"];
            $column_values = Media::get_distinct_for_column($user_id, Config::DBTables()->movie, $column_name);
            APIService::response_success($column_values);
        });

        /* ========================================================== *
        * POST
        * ========================================================== */

        /* 1. Create a movie */
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
                "genre"
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

        /* 2. Update a movie */
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
                "genre"
            ));

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

        /* 1. Delete a movie */
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
