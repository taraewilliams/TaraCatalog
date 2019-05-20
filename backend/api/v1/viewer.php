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
            $status = "approved";

            $viewer = Viewer::get_for_creator_and_viewer_id($creator_id, $viewer_id, $status);
            if($viewer === false || $viewer === null) {
                APIService::response_fail("There was a problem getting viewer.", 500);
            }
            if($viewer === null) {
                APIService::response_fail("The requested viewer does not exist.", 404);
            }
            APIService::response_success($viewer);
        });

        /* Get all creator can view */
        $app->get($resource . "/view/list/{status}", function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $status = $args['status'];

            $viewers = Viewer::get_all_user_views($user_id, $status);
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
            if($viewer === false || $viewer === null) {
                APIService::response_fail("There was a problem creating the viewer.", 500);
            }
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
            if($viewer === false || $viewer === null) {
                APIService::response_fail("There was a problem updating the viewer.", 500);
            }
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

            if( $result === false ) {
                APIService::response_fail("There was an error deleting that viewer.", 500);
            }
            if( $result === null ) {
                APIService::response_fail("The requested viewer does not exist.", 404);
            }
            APIService::response_success(true);
        });
    });
});
