<?php

namespace TaraCatalog\Service;

use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;
use TaraCatalog\Config\HttpFailCodes;
use TaraCatalog\Config\Database;

class APIService
{
    public static function response_success($data, $code = 200)
    {
        header('Content-Type: application/json');
        http_response_code($code);
        die(json_encode($data));
    }

    public static function response_fail($http_response) {

        header('Content-Type: application/json');
        http_response_code($http_response->code);

        $response = array(
            "code"      => $http_response->code,
            "message"   => $http_response->message,
            "type"      => $http_response->type
        );
        die(json_encode( (object) $response ));
    }

    public static function authenticate_request(&$request_params)
    {
        $params = APIService::build_params($request_params, array(
            "session_id",
            "session_token"
        ));

        $error = null;
        $result = AuthService::authenticate($params['session_id'], $params['session_token'], $error);
        if($result === false || $result === null) {
            $response = HttpFailCodes::http_response_fail()->session_fail;
            $response->message = $error;
            self::response_fail($response);
        }
        return $result;
    }

    public static function authenticate_request_admin(&$request_params)
    {
        $session = self::authenticate_request($request_params);
        if ($session->user->is_admin){
            return $session;
        } else{
            APIService::response_fail(HttpFailCodes::http_response_fail()->admin_request);
        }
    }

    public static function authenticate_request_creator(&$request_params)
    {
        $session = self::authenticate_request($request_params);
        if ($session->user->role === Constants::user_role()->creator){
            return $session;
        } else{
            APIService::response_fail(HttpFailCodes::http_response_fail()->creator_request);
        }
    }

    public static function build_params($request, $required_param_names = array(), $optional_param_names = array())
    {
        $request = (array) $request;

        $params = array();
        $required_params = self::get_params($request, $params, $required_param_names, true);
        $optional_params = self::get_params($request, $required_params["params"], $optional_param_names);

        if(count($required_params["missing"]) > 0) {
            $message = "Missing parameters: [" . implode(", ", $required_params["missing"]) . "].";
            return self::response_fail($message);
        }
        return $optional_params["params"];
    }

    public static function build_files($files, $required_file_names = array(), $optional_file_names = array())
    {
        $files = (array) $files;
        $params = array();

        $required_files = self::get_params($files, $params, $required_file_names, true);
        $optional_files = self::get_params($files, $required_files["params"], $optional_file_names);

        if(count($required_files["missing"]) > 0) {
            $message = "Missing files: [" . implode(", ", $required_files["missing"]) . "].";
            return self::response_fail($message);
        }
        return $optional_files["params"];
    }

    private static function get_params($request, $params, $request_names, $required = false){

        if (is_array($request_names)){
            $missing = array();

            foreach ($request_names as $param_name){
                if (isset($request[$param_name])){
                    $params[$param_name] = $request[$param_name];
                } else {
                    if ($required){
                        $missing[] = $param_name;
                    }
                }
            }
            return ["params" => $params, "missing" => $missing];
        }else{
            return ["params" => array(), "missing" => array()];
        }
    }

}
