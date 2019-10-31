<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Service\AuthService;

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/auth";

        /* ========================================================== *
        * POST
        * ========================================================== */
        $app->post($resource . "/login", function () use ($app)
        {
            $params = APIService::build_params($_POST, array(
                "username",
                "password"
            ));

            $session = AuthService::login($params['username'], $params['password']);
            APIService::response_success($session);
        });

        $app->post($resource . "/logout", function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);

            $params = APIService::build_params($_REQUEST, array(
                "session_id"
            ));

            $result = AuthService::logout($params['session_id']);
            APIService::response_success($result);
        });

    });
});
