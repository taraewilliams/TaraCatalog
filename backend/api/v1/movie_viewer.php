<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Movie;
use TaraCatalog\Model\Viewer;


$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/movie_viewers";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get all movies */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $viewer_id = $session->user->id;
            $creator_id = intval($args['id']);
            $status = "approved";

            /* Check that the viewer has permission to view the creator's movies */
            $viewer = Viewer::get_for_creator_and_viewer_id($creator_id, $viewer_id, $status);
            if($viewer === false || $viewer === null) {
                APIService::response_fail("There was a problem getting the movies.", 500);
            }

            $movies = Movie::get_all($creator_id);
            if($movies === false) {
                APIService::response_fail("There was a problem getting the movies.", 500);
            }
            APIService::response_success($movies);
        });

    });
});
