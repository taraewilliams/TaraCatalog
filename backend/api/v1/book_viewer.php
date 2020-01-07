<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Media;
use TaraCatalog\Model\Viewer;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;
use TaraCatalog\Config\HttpFailCodes;

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/book_viewers";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get all books */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $viewer_id = $session->user->id;
            $creator_id = intval($args['id']);
            $status = "approved";

            /* Check that the viewer has permission to view the creator's books */
            if(!Viewer::exists_for_creator_and_viewer_id($creator_id, $viewer_id, $status)){
                APIService::response_fail(HttpFailCodes::http_response_fail()->viewer_get_media);
            }

            $books = Media::get_all($creator_id, Config::DBTables()->book);
            APIService::response_success($books);
        });

    });
});
