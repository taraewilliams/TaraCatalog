<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Viewer;
use TaraCatalog\Config\Constants;
use TaraCatalog\Config\HttpFailCodes;
use TaraCatalog\Model\Media;

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
            $session = APIService::authenticate_request_creator($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, null, array(
                "status",
                "requested_by"
            ));

            /* Check that enums are set to valid values */
            $enum_property_list = array(
                array("property" => "status", "enum" => Constants::viewer_status()),
                array("property" => "requested_by", "enum" => Constants::viewer_requested_by()),
            );

            Media::are_valid_enums($enum_property_list, $params);

            /* Get viewers */
            $viewers = Viewer::get_all($user_id, $params);
            usort($viewers, array("TaraCatalog\Model\Viewer", "sort_viewers"));

            APIService::response_success($viewers);
        });

        /* Get all user catalogs a user can view for a specific status
        and by who requested the relationship */
        $app->post($resource. '/view_list', function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, null, array(
                "status",
                "requested_by"
            ));

            /* Check that enums are set to valid values */
            $enum_property_list = array(
                array("property" => "status", "enum" => Constants::viewer_status()),
                array("property" => "requested_by", "enum" => Constants::viewer_requested_by()),
            );

            Media::are_valid_enums($enum_property_list, $params);

            /* Get viewers */
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
            $status = Constants::viewer_status()->approved;

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

            if ($params["creator_id"] == $params["viewer_id"]){
                APIService::response_fail(HttpFailCodes::http_response_fail()->viewer_create_self);
            }

            $params["status"] = Constants::viewer_status()->pending;

            if ($user_id == $params["creator_id"]){
                $params["requested_by"] = Constants::viewer_requested_by()->creator;
            }else if ($user_id == $params["viewer_id"]){
                $params["requested_by"] = Constants::viewer_requested_by()->viewer;
            }else{
                APIService::response_fail(HttpFailCodes::http_response_fail()->create_viewer);
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

            /* Check that enums are set to valid values */
            $enum_property_list = array(
                array("property" => "status", "enum" => Constants::viewer_status())
            );

            Media::are_valid_enums($enum_property_list, $params);

            /* Update viewer */
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
                APIService::response_fail(HttpFailCodes::http_response_fail()->delete_viewer);
            }

            $result = Viewer::delete($creator_id, $viewer_id);
            APIService::response_success(true);
        });
    });
});
