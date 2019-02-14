<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Movie;


$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/movies";

        /* ========================================================== *
         * GET
         * ========================================================== */

        /* Get all movies */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $movies = Movie::get_all();
            if($movies === false) {
                APIService::response_fail("There was a problem getting the movies.", 500);
            }
            APIService::response_success($movies);
        });

        /* Get a set number of movies */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $offset = intval($args['offset']);
            $limit = intval($args['limit']);

            $movies = Movie::get_all_with_limit($offset, $limit);
            if($movies === false) {
                APIService::response_fail("There was a problem getting the movies.", 500);
            }
            APIService::response_success($movies);
        });

        /* Get a single movie */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $id = intval($args['id']);
            $movie = Movie::get_from_id($id);

            if($movie === false) {
                APIService::response_fail("There was a problem getting movie.", 500);
            }
            if($movie === null) {
                APIService::response_fail("The requested movie does not exist.", 404);
            }
            APIService::response_success($movie);
        });

        /* Count movies with different content types */
        $app->get($resource . '/content_type/count', function ($request, $response, $args) use ($app)
        {
            $movies = Movie::get_all_content_type_counts();
            if($movies === false) {
                APIService::response_fail("There was a problem getting the movies.", 500);
            }
            APIService::response_success($movies);
        });

        /* Count movies with different formats */
        $app->get($resource . '/format/count', function ($request, $response, $args) use ($app)
        {
            $movies = Movie::get_all_format_counts();
            if($movies === false) {
                APIService::response_fail("There was a problem getting the movies.", 500);
            }
            APIService::response_success($movies);
        });

        /* Count all movies */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $movies = Movie::count_movies();
            if($movies === false) {
                APIService::response_fail("There was a problem getting the movies.", 500);
            }
            APIService::response_success($movies);
        });


        /* ========================================================== *
         * POST
         * ========================================================== */
        $app->post($resource, function () use ($app)
        {
            $params = APIService::build_params($_REQUEST, array(
                "title",
                "format"
            ), array(
                "edition",
                "content_type"
            ));

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($files['image'])) {
                $params['image'] = Movie::set_image($files, $params["title"]);
            }

            $movie = Movie::create_from_data($params);
            if($movie === false || $movie === null) {
                APIService::response_fail("There was a problem creating the movie.", 500);
            }
            APIService::response_success($movie);
        });

        /* ========================================================== *
         * PUT
         * ========================================================== */
        $app->post($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $params = APIService::build_params($_REQUEST, null, array(
              "title",
              "format",
              "edition",
              "content_type"
            ));

            $id = intval($args["id"]);

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($params["title"])){
              $title = $params["title"];
            }else{
              $title = Movie::get_title_for_id($id);
            }

            if(isset($files['image'])) {
                $params['image'] = Movie::set_image($files, $title);
            }

            $movie = Movie::update($id, $params);
            if($movie === false || $movie === null) {
                APIService::response_fail("There was a problem updating the movie.", 500);
            }
            APIService::response_success($movie);
        });


        /* ========================================================== *
         * DELETE
         * ========================================================== */
        $app->delete($resource . '/{id}', function ($response, $request, $args) use ($app)
        {
            $id = intval($args["id"]);
            $result = Movie::set_active($id, 0);

            if( $result === false ) {
                APIService::response_fail("There was an error setting the active state of that movie.", 500);
            }
            if( $result === null ) {
                APIService::response_fail("The requested movie does not exist.", 404);
            }
            APIService::response_success(true);
        });
    });
});
