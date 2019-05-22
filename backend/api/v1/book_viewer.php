<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Book;
use TaraCatalog\Model\Viewer;


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
            $viewer = Viewer::get_for_creator_and_viewer_id($creator_id, $viewer_id, $status);
            if($viewer === false || $viewer === null || count($viewer) === 0) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }

            $books = Book::get_all($creator_id);
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

    });
});
