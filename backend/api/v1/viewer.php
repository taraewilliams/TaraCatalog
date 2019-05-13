<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Viewer;


$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/viewers";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get all viewers */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $viewers = Viewer::get_all($user_id);
            if($viewers === false) {
                APIService::response_fail("There was a problem getting the viewers.", 500);
            }
            usort($viewers, array("TaraCatalog\Model\Viewer", "sort_viewers"));

            APIService::response_success($viewers);
        });

        /* Get a single viewer */
        $app->get($resource . '/{creator_id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);

            $creator_id = intval($args['creator_id']);
            $viewer_id = $session->user->id;

            $viewer = Viewer::get_for_creator_and_viewer_id($creator_id, $viewer_id);
            if($viewer === false) {
                APIService::response_fail("There was a problem getting viewer.", 500);
            }
            if($viewer === null) {
                APIService::response_fail("The requested viewer does not exist.", 404);
            }
            APIService::response_success($viewer);
        });

        /* Get all creator can view */
        $app->get($resource . "/view/list", function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $viewers = Viewer::get_all_user_views($user_id);
            if($viewers === false) {
                APIService::response_fail("There was a problem getting the viewers.", 500);
            }
            usort($viewers, array("TaraCatalog\Model\Viewer", "sort_creators"));

            APIService::response_success($viewers);
        });

        /* ========================================================== *
        * POST
        * ========================================================== */

        /* Create a viewer */
        $app->post($resource, function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, array(
                "viewer_id"
            ), array());
            $params["creator_id"] = $user_id;

            $viewer = Viewer::create_from_data($params);
            if($viewer === false || $viewer === null) {
                APIService::response_fail("There was a problem creating the viewer.", 500);
            }
            APIService::response_success($viewer);
        });

        /* ========================================================== *
        * DELETE
        * ========================================================== */

        /* Delete a viewer */
        $app->delete($resource . '/{viewer_id}', function ($response, $request, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $creator_id = $session->user->id;
            $viewer_id = intval($args['viewer_id']);

            $result = Viewer::set_active($creator_id, $viewer_id, 0);

            if( $result === false ) {
                APIService::response_fail("There was an error setting the active state of that viewer.", 500);
            }
            if( $result === null ) {
                APIService::response_fail("The requested viewer does not exist.", 404);
            }
            APIService::response_success(true);
        });
    });
});
