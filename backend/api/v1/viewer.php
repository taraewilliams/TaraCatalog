<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Viewer;


$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/viewers";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get all viewers for a creator for a specific status (approved, pending, rejected) */
        $app->get($resource . "/list/{status}", function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $status = $args['status'];

            $viewers = Viewer::get_all($user_id, $status);
            usort($viewers, array("TaraCatalog\Model\Viewer", "sort_viewers"));

            APIService::response_success($viewers);
        });

        /* Get all creator can view for a specific status */
        $app->get($resource . "/view/list/{status}", function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $status = $args['status'];

            $viewers = Viewer::get_all_user_views($user_id, $status);

            usort($viewers, array("TaraCatalog\Model\Viewer", "sort_creators"));

            APIService::response_success($viewers);
        });

        /* Get a single viewer */
        $app->get($resource . '/{creator_id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);

            $creator_id = intval($args['creator_id']);
            $viewer_id = $session->user->id;

            /* A viewer can only see creators that have approved them */
            $status = "approved";

            $viewer = Viewer::get_for_creator_and_viewer_id($creator_id, $viewer_id, $status);
            APIService::response_success($viewer);
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
                "creator_id",
                "viewer_id"
            ), array());

            if ($user_id == $params["creator_id"]){
                $params["status"] = "approved";
            }else if ($user_id == $params["viewer_id"]){
                $params["status"] = "pending";
            }else{
                APIService::response_fail("There was a problem creating the viewer.", 500);
            }

            $viewer = Viewer::create_from_data($params);
            APIService::response_success($viewer);
        });

        /* ========================================================== *
        * PUT
        * ========================================================== */

        /* Approve or reject a viewer */
        $app->post($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;
            $id = intval($args["id"]);

            $params = APIService::build_params($_REQUEST, null, array(
                "status"
            ));

            $status = $params["status"];
            if ($status !== "approved" && $status !== "rejected" && $status !== "pending"){
                APIService::response_fail("Invalid status.", 500);
            }

            $viewer = Viewer::update($user_id, $id, $params);
            APIService::response_success($viewer);
        });

        /* ========================================================== *
        * DELETE
        * ========================================================== */

        /* Delete a viewer */
        $app->delete($resource . '/{viewer_id}/{creator_id}', function ($response, $request, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $creator_id = intval($args["creator_id"]);
            $viewer_id = intval($args["viewer_id"]);

            if ($user_id !== $creator_id && $user_id !== $viewer_id){
                APIService::response_fail("There was a problem deleting the viewer.", 500);
            }

            $result = Viewer::delete($creator_id, $viewer_id);
            APIService::response_success(true);
        });
    });
});
