<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Media;
use TaraCatalog\Model\Viewer;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;

/* Requests */

/* GET */

/*

1. game_viewers/{id}
    Get all of a creator's games for a viewer, given the viewer's and creator's IDs.
    Input: id (creator ID)
    Output: Game object array
*/

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/game_viewers";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get all games */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $viewer_id = $session->user->id;
            $creator_id = intval($args['id']);
            $status = "approved";

            /* Check that the viewer has permission to view the creator's games */
            if(!Viewer::exists_for_creator_and_viewer_id($creator_id, $viewer_id, $status)){
                APIService::response_fail("There was a problem getting the games.", 500);
            }

            $games = Media::get_all($creator_id, Config::DBTables()->game, Constants::default_order()->game);
            APIService::response_success($games);
        });

    });
});
