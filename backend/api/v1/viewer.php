<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Viewer;

/* Requests */


/* GET */

/*

1. viewers/list/{status}
    Gets all viewers for a creator for a specific status (approved, pending, rejected).
    Input: status
    Output: Viewer object array (with viewer and creator usernames and images)

2. viewers/view/list/{status}
    Gets all a creator can view for a specific status (approved, pending rejected).
    Input: status
    Output: Viewer object array (with viewer and creator usernames and images)

3. viewers/{creator_id}
    Gets a single viewer for creator ID and viewer ID.
    Input: creator_id
    Output: Viewer object
*/


/* POST */

/*

1. viewers
    Creates a new viewer and sets the status based on whether the user ID is a creator ID (approved) or viewer ID (pending).
    Input: creator_id, viewer_id
    Output: Viewer object

2. viewers/{id}
    Updates a viewer.
    Input: id (viewer object ID), status
    Output: true or false (success or failure)
*/


/* DELETE */

/*

1. viewers/{viewer_id}/{creator_id}
    Deletes a viewer for the viewer ID and creator ID. Either a viewer or creator can delete the relationship.
    Input: viewer_id, creator_id
    Output: true or false (success or failure)
*/


$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/viewers";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get all viewers for a creator for a specific status (approved, pending, rejected)
        and by who requested the relationship */
        $app->post($resource. '/list', function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, null, array(
                "status",
                "requested_by"
            ));

            $viewers = Viewer::get_all($user_id, $params);
            usort($viewers, array("TaraCatalog\Model\Viewer", "sort_viewers"));

            APIService::response_success($viewers);
        });

        /* Get all user catalogs a creator can view for a specific status
        and by who requested the relationship */
        $app->post($resource. '/view_list', function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, null, array(
                "status",
                "requested_by"
            ));

            $viewers = Viewer::get_all_user_views($user_id, $params);
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

            $params["status"] = "pending";

            if ($user_id == $params["creator_id"]){
                $params["requested_by"] = "creator";
            }else if ($user_id == $params["viewer_id"]){
                $params["requested_by"] = "viewer";
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

            /* Allow either viewer or creator to delete a viewer object */
            if ($user_id !== $creator_id && $user_id !== $viewer_id){
                APIService::response_fail("There was a problem deleting the viewer.", 500);
            }

            $result = Viewer::delete($creator_id, $viewer_id);
            APIService::response_success(true);
        });
    });
});
