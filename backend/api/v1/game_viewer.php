<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Media;
use TaraCatalog\Model\Viewer;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;

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
                APIService::response_fail("You don't have permission to view this list.", 401);
            }

            $games = Media::get_all($creator_id, Config::DBTables()->game, Constants::default_order()->game);
            APIService::response_success($games);
        });

    });
});
